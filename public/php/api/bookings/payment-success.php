<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Get payment intent from URL
$paymentIntentId = $_GET['payment_intent'] ?? null;
$bookingID = $_GET['booking_id'] ?? null;
$type = $_GET['type'] ?? 'booking';

if (!$paymentIntentId || !$bookingID) {
    header("Location: /account.php?error=" . urlencode("Invalid payment confirmation"));
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
    header("Location: /account.php?error=" . urlencode("System configuration error"));
    exit;
}

try {
    require_once $autoloadPath;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
} catch (Exception $e) {
    error_log("Failed to load dependencies: " . $e->getMessage());
    header("Location: /account.php?error=" . urlencode("System error"));
    exit;
}

// Initialize Stripe
$stripe = new \Stripe\StripeClient(["api_key" => 'sk_test_NbE079Ks9Vg2NYlFuLBFFrRP']);

$conn = Dbh::getConnection();

try {
    // Retrieve the payment intent from Stripe
    $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
    
    if ($paymentIntent->status !== 'succeeded') {
        throw new Exception("Payment not completed");
    }
    
    $conn->beginTransaction();
    
    // Update payment status in database
    $stmt = $conn->prepare("
        UPDATE payments 
        SET status = 'succeeded' 
        WHERE stripe_payment_intent_id = :payment_intent_id 
          AND booking_id = :booking_id
    ");
    $stmt->bindParam(':payment_intent_id', $paymentIntentId);
    $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
    $stmt->execute();
    
    // If this is an extension payment, update booking times
    if ($type === 'extension' && isset($_SESSION['additional_payment'])) {
        $paymentData = $_SESSION['additional_payment'];
        
        if ($paymentData['booking_id'] == $bookingID) {
            $stmt = $conn->prepare("
                UPDATE bookings 
                SET booking_start = :new_start, 
                    booking_end = :new_end, 
                    updated_at = NOW() 
                WHERE booking_id = :booking_id
            ");
            $stmt->bindParam(':new_start', $paymentData['new_start']);
            $stmt->bindParam(':new_end', $paymentData['new_end']);
            $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
            $stmt->execute();
            
            // Clear session data
            unset($_SESSION['additional_payment']);
        }
    }
    
    $conn->commit();
    
    header("Location: /account.php?success=" . urlencode("Payment successful! Your booking has been updated."));
    exit;
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Stripe verification error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("Location: /account.php?error=" . urlencode("Payment verification failed"));
    exit;
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Payment success handler error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("Location: /account.php?error=" . urlencode("Error updating booking"));
    exit;
}