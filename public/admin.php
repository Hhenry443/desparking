<?php 

// Check if admin
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /login.php?error=" . urlencode("You must be an admin to access that page."));
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">