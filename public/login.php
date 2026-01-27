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
    <title>Login · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind -->
    <link href="/css/output.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-[#ebebeb] flex items-center justify-center">

<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="w-full max-w-md bg-white rounded-3xl shadow-[0px_0px_6px_10px_rgba(0,_0,_0,_0.1)] p-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Welcome back</h1>
    <p class="text-gray-500 mb-6 text-sm">Log in to your DesParking account</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 text-sm">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/php/api/index.php?id=login" class="space-y-5">

        <!-- Email -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">
                Email
            </label>
            <input
                type="email"
                name="email"
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
                name="password"
                required
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
            Log in
        </button>
    </form>

    <p class="text-sm text-gray-500 mt-6 text-center">
        Don’t have an account?
        <a href="/register.php" class="text-[#6ae6fc] font-semibold hover:underline">
            Sign up
        </a>
    </p>
</div>

</body>

</html>
