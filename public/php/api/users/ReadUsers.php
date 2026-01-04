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

        header("Location: /account.php?user=" . $user['user_id']);
        exit;
    }

    private function redirectWithError(string $message): void
    {
        header("Location: /login.php?error=" . urlencode($message));
        exit;
    }
}
