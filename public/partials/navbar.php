<?php

$currentPage = basename($_SERVER['PHP_SELF']);

?>

<nav class="w-full h-16 bg-[#060745] backdrop-blur-md shadow-md fixed top-0 left-0 z-50 flex items-center justify-between px-6 border-b-4 border-[#6ae6fc]">
    <!-- Logo -->
    <div class="flex items-center space-x-2">
        <a href="/" class="text-xl font-semibold text-gray-800">
            <img src="/images/des-logo-new.png" alt="Logo" class="h-8 w-auto">
        </a>
        <!-- If user is admin, show admin logo -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] === true): ?>
            <span class="ml-3 px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">ADMIN</span>
        <?php endif; ?>
    </div>


    <!-- Nav Links -->
    <div class="hidden md:flex items-center space-x-6 text-white font-bold">
        <!-- If user is admin, show admin link -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] === true): ?>
            <a href="/admin.php" class="hover:text-[#6ae6fc] transition">Admin</a>
        <?php endif; ?>

        <!-- Home link -->
        <a href="/" class="hover:text-[#6ae6fc] transition">Home</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/account.php" class="hover:text-[#6ae6fc] transition">Account</a>
        <?php else: ?>
            <a href="/login.php" class="hover:text-[#6ae6fc] transition">Login</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && $currentPage === 'account.php'): ?>
            <a href="/logout.php"
            class="inline-block py-2 px-4 rounded-lg bg-red-50 text-red-600 text-sm font-bold
                    hover:bg-red-100 transition shadow-sm">
                Logout
            </a>
        <?php endif; ?>

        <!-- Book a car park -->
        <a href="/map.php"
        class="inline-block py-2 px-5 rounded-lg bg-[#6ae6fc] text-gray-900
                hover:bg-cyan-400 transition shadow-md">
            Book Now
        </a>


        <!-- Create a new car park -->
        <a href="/create.php"
        class="inline-block px-4 py-2 bg-white text-[#060745] rounded-lg
                hover:bg-gray-500 hover:text-white transition shadow">
            <i class="fa-solid fa-square-parking"></i> Rent Your Space
        </a>

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