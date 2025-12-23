<?php
session_start();

// If not logged in, kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$userId = $_SESSION['user_id'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Account · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100 pt-20">

    <nav class="w-full h-16 bg-white/80 backdrop-blur-md shadow-md fixed top-0 left-0 z-50 flex items-center justify-between px-6">
        <!-- Logo -->
        <div class="flex items-center space-x-2">
            <span class="text-xl font-semibold text-gray-800">DesParking</span>
        </div>

        <!-- Nav Links -->
        <div class="hidden md:flex space-x-6 text-gray-700 font-medium">
            <a href="#" class="hover:text-green-600 transition">Book</a>
            <a href="#" class="hover:text-green-600 transition">Carparks</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/logout.php" class="hover:text-red-600 transition">
                    Logout
                </a>
            <?php else: ?>
                <a href="/login.php" class="hover:text-green-600 transition">
                    Login
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Icon -->
        <button class="md:hidden p-2 rounded hover:bg-gray-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </nav>

<div class="max-w-xl mx-auto bg-white rounded-2xl shadow-lg p-8 mt-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">
        Account
    </h1>

    <p class="text-gray-600">
        You are logged in as user ID:
    </p>

    <div class="mt-4 text-xl font-mono bg-gray-100 rounded-lg px-4 py-2 inline-block">
        <?= htmlspecialchars($userId) ?>
    </div>
</div>

</body>
</html>
