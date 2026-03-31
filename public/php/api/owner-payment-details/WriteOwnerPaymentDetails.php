<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/OwnerPaymentDetails.php';

class WriteOwnerPaymentDetails extends OwnerPaymentDetails
{
    public function handleSave(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit();
        }

        $userId = (int) $_SESSION['user_id'];
        $type   = $_POST['payment_type'] ?? '';

        if ($type === 'bank_transfer') {
            $accountName   = trim($_POST['account_name']   ?? '');
            $sortCode      = trim($_POST['sort_code']       ?? '');
            $accountNumber = trim($_POST['account_number']  ?? '');

            if (!$accountName || !$sortCode || !$accountNumber) {
                header('Location: /account.php?section=payment-details&error=' . urlencode('Please fill in all bank transfer fields.'));
                exit();
            }

            $result = $this->savePaymentDetails($userId, 'bank_transfer', [
                'account_name'   => $accountName,
                'sort_code'      => $sortCode,
                'account_number' => $accountNumber,
                'paypal_email'   => null,
            ]);
        } elseif ($type === 'paypal') {
            $paypalEmail = trim($_POST['paypal_email'] ?? '');

            if (!$paypalEmail || !filter_var($paypalEmail, FILTER_VALIDATE_EMAIL)) {
                header('Location: /account.php?section=payment-details&error=' . urlencode('Please enter a valid PayPal email address.'));
                exit();
            }

            $result = $this->savePaymentDetails($userId, 'paypal', [
                'account_name'   => null,
                'sort_code'      => null,
                'account_number' => null,
                'paypal_email'   => $paypalEmail,
            ]);
        } else {
            header('Location: /account.php?section=payment-details&error=' . urlencode('Invalid payment type.'));
            exit();
        }

        if (!$result['success']) {
            header('Location: /account.php?section=payment-details&error=' . urlencode('Could not save payment details.'));
            exit();
        }

        header('Location: /account.php?section=payment-details&success=' . urlencode('Payment details saved.'));
        exit();
    }

    public function handleDelete(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit();
        }

        $this->removePaymentDetails((int) $_SESSION['user_id']);
        header('Location: /account.php?section=payment-details&success=' . urlencode('Payment details removed.'));
        exit();
    }
}
