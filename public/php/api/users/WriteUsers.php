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

    public function updateProfile()
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit;
        }

        $userId       = (int) $_SESSION['user_id'];
        $user_name    = trim($_POST['user_name']    ?? '');
        $user_email   = trim($_POST['user_email']   ?? '');
        $current_pass = $_POST['current_password']  ?? '';
        $new_pass     = $_POST['new_password']       ?? '';
        $confirm_pass = $_POST['confirm_password']   ?? '';

        if (!$user_name || !$user_email || !$current_pass) {
            $this->redirectWithError("Please fill in all required fields.");
        }

        // Verify current password
        $user = $this->getUserById($userId);
        if (!$user || !password_verify($current_pass, $user['user_password_hash'])) {
            $this->redirectWithError("Current password is incorrect.");
        }

        // Check email uniqueness if changed
        if ($user_email !== $user['user_email']) {
            if ($this->emailInUse($user_email)) {
                $this->redirectWithError("That email address is already in use.");
            }
        }

        // Check username uniqueness if changed
        if ($user_name !== $user['user_name']) {
            if ($this->usernameInUse($user_name)) {
                $this->redirectWithError("That username is already in use.");
            }
        }

        // Update name + email
        $this->updateUserNameEmail($userId, $user_name, $user_email);

        // Update password if provided
        if ($new_pass !== '') {
            if (strlen($new_pass) < 6) {
                $this->redirectWithError("New password must be at least 6 characters.");
            }
            if ($new_pass !== $confirm_pass) {
                $this->redirectWithError("New passwords do not match.");
            }
            $this->updateUserPassword($userId, password_hash($new_pass, PASSWORD_DEFAULT));
        }

        // Keep session name in sync
        $_SESSION['user_name'] = $user_name;

        header("Location: /account.php?section=profile&success=" . urlencode("Profile updated successfully."));
        exit;
    }

    private function redirectWithError(string $message): void
    {
        header("Location: /account.php?section=profile&error=" . urlencode($message));
        exit;
    }
}
