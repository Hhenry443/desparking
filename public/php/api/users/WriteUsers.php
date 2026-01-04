<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Users.php';

class WriteUsers extends Users
{
    public function writeUser()
    {
        // Collect POST data safely
        $user_name = $_POST['user_name'] ?? null;
        $user_email = $_POST['user_email'] ?? null;
        $user_password = $_POST['user_password'] ?? null;
        $user_confirm_password = $_POST['user_confirm_password'] ?? null;

        if (!$user_name || !$user_email || !$user_password || !$user_confirm_password) {
            $errorMessage = "Please fill in all form fields.";
            $encodedError = urlencode($errorMessage);

            header("Location: /register.php?error=" . $encodedError);

            exit;
        }

        // Check that username not used already
        $inUse = $this->usernameInUse($user_email);

        if ($inUse) {
            $errorMessage = "Username already in use.";
            $encodedError = urlencode($errorMessage);

            header("Location: /register.php?error=" . $encodedError);

            exit;
        }

        // Check that email not used already
        $inUse = $this->emailInUse($user_email);

        if ($inUse) {
            $errorMessage = "Email already in use.";
            $encodedError = urlencode($errorMessage);

            header("Location: /register.php?error=" . $encodedError);

            exit;
        }

        // Check that password is longer than 6 chars
        if (strlen($user_password) < 6) {
            $errorMessage = "Password must be longer than 6 characters.";
            $encodedError = urlencode($errorMessage);

            header("Location: /register.php?error=" . $encodedError);

            exit;
        }

        // Check passwords match
        if ($user_confirm_password != $user_password) {
            $errorMessage = "Passwords must match.";
            $encodedError = urlencode($errorMessage);

            header("Location: /register.php?error=" . $encodedError);

            exit;
        }

        // Hash password
        $password_hash = password_hash($user_password, PASSWORD_DEFAULT);

        // Insert + get ID
        $userID = $this->insertUser(
            $user_name,
            $user_email,
            $password_hash
        );

        
        // Check if insert was successful 
        if ($userID) {
            $_SESSION['user_id'] = $userID;
            $_SESSION['is_admin'] = false;

            header("Location: /account.php?user=" . $userID);
            return $userID; // Return database error if one occurred
        }

        header("Location: /account.php?user=" . $userID);
        exit;
    }
}
