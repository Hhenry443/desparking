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



<body class="min-h-screen bg-white">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- HERO / SECTION 1 -->
    <section id="section-1" class="relative bg-white overflow-hidden">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-start">

            <!-- LEFT BOX -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1 pt-48">Sign Up</h1>
                <p class="text-gray-700 mb-6 text-sm">Create a DesParking account</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 text-sm">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/php/api/index.php?id=login" class="space-y-6">

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
                        class="w-full py-3 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-semibold
                            hover:bg-cyan-400 transition shadow-md"
                    >
                        Sign Up
                    </button>
                </form>
                
                <div class="w-full flex items-center justify-between my-6">
                    <p class="text-sm text-[#060745] text-center">
                        Have an account?
                    </p>

                    <a href="/login.php" class="text-[#060745] font-semibold w-1/2 text-center px-10 py-5 border border-[#060745] rounded-lg hover:bg-cyan-50 hover:underline">
                        Log in
                    </a>
                
                </div>
                
            </div>

            <!-- RIGHT BOX -->
            <div class="relative w-8/10 rounded-lg shadow-lg overflow-hidden pt-16">
                <!-- Gradient background (behind image) -->
                <div class="absolute top-0 left-0 w-full h-3/4
                            bg-gradient-to-b from-white to-[#0BE9FF]
                            z-0 rounded-b-lg">
                </div>

                <!-- Image (above gradient) -->
                <img src="/images/login.png"
                    alt="Login"
                    class="relative z-10 w-full h-auto block">
            </div>
    </section>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>
