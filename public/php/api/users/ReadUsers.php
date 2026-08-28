<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Users.php';

class ReadUsers extends Users
{
    public function loginUser()
    {
        session_start();

        // Collect POST data
        $user_email = $_POST['email'] ?? null;
        $user_password = $_POST['password'] ?? null;

        if (!$user_email || !$user_password) {
            $this->redirectWithError("Please fill in all form fields.");
        }

        // Ask the model to authenticate
        $user = $this->login($user_email, $user_password);

        if (!$user) {
            $this->redirectWithError("Invalid email or password.");
        }

        // Login success 
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];

        if ($user['user_is_admin'] == 1) {
            $_SESSION['is_admin'] = true;
        } else {
            $_SESSION['is_admin'] = false;
        }

        // Came from a guest booking's emailed link — attach those bookings to
        // the account they just signed into.
        include_once $_SERVER['DOCUMENT_ROOT'] . '/php/helpers/GuestClaim.php';
        $claimMessage = GuestClaim::run($_POST['claim_token'] ?? '', (int) $user['user_id']);

        header("Location: /account.php" . ($claimMessage !== null ? "?success=" . urlencode($claimMessage) : ""));
        exit;
    }

    public function getUserById(int $id)
    {
        return parent::getUserById($id);
    }

    private function redirectWithError(string $message): void
    {
        header("Location: /login.php?error=" . urlencode($message));
        exit;
    }
}
