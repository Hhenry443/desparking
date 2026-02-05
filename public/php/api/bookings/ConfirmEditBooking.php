<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Verify preview data exists
if (!isset($_SESSION['booking_edit_preview'])) {
    header("Location: /account.php?error=" . urlencode("Session expired. Please try again."));
    exit;
}

$preview = $_SESSION['booking_edit_preview'];
$bookingID = $preview['booking_id'];

// Check if preview is still valid (5 minutes as per your HTML)
if (time() - $preview['preview_created'] > 300) {
    unset($_SESSION['booking_edit_preview']);
    header("Location: /booking/edit.php?id=$bookingID&error=" . urlencode("Edit session expired"));
    exit;
}

// Find vendor autoload
$possiblePaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
];

$autoloadPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $autoloadPath = $path;
        break;
    }
}

if (!$autoloadPath) {
    error_log("Composer autoload not found. Tried paths: " . implode(', ', $possiblePaths));
    header("Location: /confirm-edit.php?id=$bookingID&error=" . urlencode("System configuration error"));
    exit;
}

try {
    require_once $autoloadPath;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
} catch (Exception $e) {
    error_log("Failed to load dependencies: " . $e->getMessage());
    header("Location: /confirm-edit.php?id=$bookingID&error=" . urlencode("System error"));
    exit;
}

// Initialize Stripe
$stripe = new \Stripe\StripeClient(["api_key" => 'sk_test_NbE079Ks9Vg2NYlFuLBFFrRP']);

// Database connection
$conn = Dbh::getConnection();

try {
    $conn->beginTransaction();
    
    $difference = $preview['difference'];
    $userId = $_SESSION['user_id'];
    
    // PATH 1: REFUND (new price is less than old price)
    if ($difference < 0) {
        $refundAmount = abs($difference);
        
        // Get the original payment record
        $stmt = $conn->prepare("
            SELECT id, stripe_payment_intent_id, stripe_customer_id, amount, currency 
            FROM payments 
            WHERE booking_id = :booking_id 
              AND type = TRIM('initial') 
              AND status = 'succeeded' 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
        $stmt->execute();
        $originalPayment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$originalPayment) {
            throw new Exception("Original payment not found");
        }
        
        // Create refund via Stripe
        $refund = $stripe->refunds->create([
            'payment_intent' => $originalPayment['stripe_payment_intent_id'],
            'amount' => $refundAmount, // Already in pence from your system
            'reason' => 'requested_by_customer',
            'metadata' => [
                'booking_id' => $bookingID,
                'user_id' => $userId,
                'reason' => 'Booking time reduced'
            ]
        ]);
        
        // Record the refund in payments table
        // Record the refund in payments table
        $stmt = $conn->prepare("
            INSERT INTO payments 
            (booking_id, user_id, stripe_payment_intent_id, stripe_customer_id, amount, currency, type, status, created_at) 
            VALUES (:booking_id, :user_id, :payment_intent_id, :customer_id, :amount, :currency, 'refund', 'succeeded', NOW())
        ");
        $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':payment_intent_id', $refund->payment_intent);
        $stmt->bindParam(':customer_id', $originalPayment['stripe_customer_id']);
        $stmt->bindParam(':amount', $refundAmount, PDO::PARAM_INT);
        $stmt->bindParam(':currency', $originalPayment['currency']);
        $stmt->execute();
        
        // Update booking times
        $stmt = $conn->prepare("
            UPDATE bookings 
            SET booking_start = :new_start, 
                booking_end = :new_end
            WHERE booking_id = :booking_id
        ");
        $stmt->bindParam(':new_start', $preview['new_start']);
        $stmt->bindParam(':new_end', $preview['new_end']);
        $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
        $stmt->execute();
        
        $conn->commit();
        
        // Clear preview
        unset($_SESSION['booking_edit_preview']);
        
        $refundPounds = number_format($refundAmount / 100, 2);
        header("Location: /account.php?success=" . urlencode("Booking updated successfully. Refund of £{$refundPounds} processed."));
        exit;
    }
    
    // PATH 2: ADDITIONAL PAYMENT (new price is more than old price)
    elseif ($difference > 0) {
        $additionalAmount = $difference;
        
        // Get currency from original payment
        $stmt = $conn->prepare("
            SELECT currency 
            FROM payments 
            WHERE booking_id = :booking_id 
              AND type = 'initial' 
              AND status = 'succeeded' 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
        $stmt->execute();
        $originalPayment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $currency = $originalPayment['currency'] ?? 'gbp';
        
        // Store pending extension payment in database (before payment)
        $stmt = $conn->prepare("
            INSERT INTO payments 
            (booking_id, user_id, stripe_payment_intent_id, amount, currency, type, status, created_at) 
            VALUES (:booking_id, :user_id, :payment_intent_id, :amount, :currency, 'initial', 'pending', NOW())
        ");
        $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $placeholderIntentId = 'pending_' . $bookingID . '_' . time();
        $stmt->bindParam(':payment_intent_id', $placeholderIntentId);
        $stmt->bindParam(':amount', $additionalAmount, PDO::PARAM_INT);
        $stmt->bindParam(':currency', $currency);
        $stmt->execute();
        
        $conn->commit();
        
        // Store extension details in session for payment page
        $_SESSION['pending_extension'] = [
            'booking_id' => $bookingID,
            'amount' => $additionalAmount,
            'new_start' => $preview['new_start'],
            'new_end' => $preview['new_end'],
            'currency' => $currency
        ];
        
        // Clear preview
        unset($_SESSION['booking_edit_preview']);
        
        // Redirect to payment page (which will create the checkout session)
        header("Location: /additional-payment.php?id=$bookingID");
        exit;
    }
    
    // PATH 3: NO PRICE CHANGE (just update times)
    else {
        // Update booking times only
        $stmt = $conn->prepare("
            UPDATE bookings 
            SET booking_start = :new_start, 
                booking_end = :new_end
            WHERE booking_id = :booking_id
        ");
        $stmt->bindParam(':new_start', $preview['new_start']);
        $stmt->bindParam(':new_end', $preview['new_end']);
        $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
        $stmt->execute();
        
        $conn->commit();
        
        // Clear preview
        unset($_SESSION['booking_edit_preview']);
        
        header("Location: /account.php?success=" . urlencode("Booking times updated successfully."));
        exit;
    }
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    $conn->rollBack();
    error_log("Stripe API error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("Location: /confirm-edit.php?id=$bookingID&error=" . urlencode("Payment processing error. Please try again."));
    exit;
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Booking update error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("Location: /confirm-edit.php?id=$bookingID&error=" . urlencode("Error processing booking update. Please try again."));
    exit;
}