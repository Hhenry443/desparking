<?php
session_start();

// If already logged in, bounce them out
if (isset($_SESSION['user_id'])) {
    header("Location: /account.php");
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind -->
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-green-50 to-gray-100 flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Create an account</h1>
        <p class="text-gray-500 mb-6">Join DesParking in under a minute</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 text-sm">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/php/api/index.php?id=insertUser" class="space-y-5">
            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Name
                </label>
                <input
                    type="text"
                    name="user_name"
                    required
                    class="w-full rounded-xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email
                </label>
                <input
                    type="email"
                    name="user_email"
                    required
                    class="w-full rounded-xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password
                </label>
                <input
                    type="password"
                    name="user_password"
                    required
                    minlength="8"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm Password
                </label>
                <input
                    type="password"
                    name="user_confirm_password"
                    required
                    minlength="8"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>

            <!-- Submit -->
            <button
                type="submit"
                class="w-full bg-green-600 text-white py-2 rounded-xl font-semibold hover:bg-green-700 transition"
            >
                Create account
            </button>
        </form>

        <p class="text-sm text-gray-500 mt-6 text-center">
            Already have an account?
            <a href="/login.php" class="text-green-600 hover:underline">
                Log in
            </a>
        </p>
    </div>

</body>
</html>
