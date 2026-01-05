<nav class="w-full h-16 bg-white/80 backdrop-blur-md shadow-md fixed top-0 left-0 z-50 flex items-center justify-between px-6">
    <!-- Logo -->
    <div class="flex items-center space-x-2">
        <a href="/" class="text-xl font-semibold text-gray-800">DesParking</a>
        <!-- If user is admin, show admin logo -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] === true): ?>
            <span class="ml-3 px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">ADMIN</span>
        <?php endif; ?>
    </div>


    <!-- Nav Links -->
    <div class="hidden md:flex space-x-6 text-gray-700 font-medium">
        <!-- Create a new car park -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/create.php" class="hover:text-green-600 transition">Create Car Park</a>
        <?php endif; ?>

        <!-- If user is admin, show admin link -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] === true): ?>
            <a href="/admin.php" class="hover:text-green-600 transition">Admin</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/account.php" class="hover:text-green-600 transition">Account</a>
        <?php else: ?>
            <a href="/login.php" class="hover:text-green-600 transition">Login</a>
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