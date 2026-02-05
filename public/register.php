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

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>
<body class="min-h-screen bg-[#ebebeb] flex items-center justify-center">

<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="w-full max-w-md bg-white rounded-3xl shadow-[0px_0px_6px_10px_rgba(0,_0,_0,_0.1)] p-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Create an account</h1>
    <p class="text-gray-500 mb-6 text-sm">Join DesParking in under a minute</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 text-sm">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/php/api/index.php?id=insertUser" class="space-y-5">

        <!-- Name -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">
                Name
            </label>
            <input
                type="text"
                name="user_name"
                required
                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300
                       focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
            >
        </div>

        <!-- Email -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">
                Email
            </label>
            <input
                type="email"
                name="user_email"
                required
                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300
                       focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
            >
        </div>

        <!-- Password -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">
                Password
            </label>
            <input
                type="password"
                name="user_password"
                required
                minlength="8"
                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300
                       focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
            >
        </div>

        <!-- Confirm Password -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">
                Confirm Password
            </label>
            <input
                type="password"
                name="user_confirm_password"
                required
                minlength="8"
                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300
                       focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
            >
        </div>

        <!-- Submit -->
        <button
            type="submit"
            class="w-full py-3 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold
                   hover:bg-cyan-400 transition shadow-md"
        >
            Create account
        </button>
    </form>

    <p class="text-sm text-gray-500 mt-6 text-center">
        Already have an account?
        <a href="/login.php" class="text-[#6ae6fc] font-semibold hover:underline">
            Log in
        </a>
    </p>
</div>

</body>

</html>
