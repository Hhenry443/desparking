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

        <!-- About Us Dropdown -->
        <div class="relative group">
            <button class="hover:text-[#6ae6fc] transition flex items-center gap-1">
                About Us
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown box -->
            <div class="absolute left-0 mt-4 w-72 rounded-xl bg-[#060745] p-3 shadow-xl
                opacity-0 invisible group-hover:opacity-100 group-hover:visible
                transition-all duration-200 z-50 space-y-2">

                <!-- Item -->
                <a href="/about.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-users text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">About Us</p>
                        <p class="text-xs text-gray-500">
                            With a commitment to simplicity and convenience, we strive to eliminate hassles and simplify the process.
                        </p>
                    </div>
                </a>

                <!-- Item -->
                <a href="/how-we-work.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-car text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">How We Work</p>
                        <p class="text-xs text-gray-500">
                            Our process is designed to be as simple and easy to use as possible, taking the stress out of parking.
                        </p>
                    </div>
                </a>

                <!-- Item -->
                <a href="/faq.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-question text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">FAQ</p>
                        <p class="text-xs text-gray-500">
                            Got a question? Find the answers to the most common queries here, or get in contact.
                        </p>
                    </div>
                </a>

                <!-- Item -->
                <a href="/partners.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-handshake text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">Partners</p>
                        <p class="text-xs text-gray-500">
                            We're proud to have established partnerships with brands across every sector, from transportation and technology to hospitality and retail.
                        </p>
                    </div>
                </a>

            </div>
        </div>

        <!-- Booking Dropdown -->
        <div class="relative group">
            <button class="hover:text-[#6ae6fc] transition flex items-center gap-1">
                Booking
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown box -->
            <div class="absolute left-0 mt-4 w-72 rounded-xl bg-[#060745] p-3 shadow-xl
                opacity-0 invisible group-hover:opacity-100 group-hover:visible
                transition-all duration-200 z-50 space-y-2">

                <!-- Item -->
                <a href="/map.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-car text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">Booking</p>
                        <p class="text-xs text-gray-500">
                            Plan your parking today with our quick and easy booking system. Your parking, guaranteed.
                        </p>
                    </div>
                </a>

                <!-- Item -->
                <a href="/monthly.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-clock text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">Monthly Spaces</p>
                        <p class="text-xs text-gray-500">
                            Want a more long-term option? Book your parking with a subscription model to ensure the spot remains yours.
                        </p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Parking Solutions Dropdown -->
        <div class="relative group">
            <button class="hover:text-[#6ae6fc] transition flex items-center gap-1">
                Parking Solutions
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown box -->
            <div class="absolute left-0 mt-4 w-72 rounded-xl bg-[#060745] p-3 shadow-xl
                opacity-0 invisible group-hover:opacity-100 group-hover:visible
                transition-all duration-200 z-50 space-y-2">

                <!-- Item -->
                <a href="/business.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-briefcase text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">Business Parking Solutions</p>
                        <p class="text-xs text-gray-500">
                            Are you a business owner with excess parking spaces? Turn your unused parking into a profitable asset!
                        </p>
                    </div>
                </a>

                <!-- Item -->
                <a href="/event.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-calendar text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">Event Parking Services</p>
                        <p class="text-xs text-gray-500">
                            Looking for a hassle-free parking solution for your next event? Book today!
                        </p>
                    </div>
                </a>

                <!-- Item -->
                <a href="/hospitality.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-building text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">Hospitality Parking</p>
                        <p class="text-xs text-gray-500">
                            Our hospitality parking services are designed to cater to the unique needs of individuals across the UK.
                        </p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Rent My Space link -->
        <a href="/create.php" class="hover:text-[#6ae6fc] transition">Rent My Space</a>

        <!-- News Dropdown -->
        <div class="relative group">
            <button class="hover:text-[#6ae6fc] transition flex items-center gap-1">
                News
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown box -->
            <div class="absolute left-0 mt-4 w-72 rounded-xl bg-[#060745] p-3 shadow-xl
                opacity-0 invisible group-hover:opacity-100 group-hover:visible
                transition-all duration-200 z-50 space-y-2">

                <!-- Item -->
                <a href="/blog.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-book text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">Blogs</p>
                        <p class="text-xs text-gray-500">
                            Read the DesParking blog and stay caught up on all of your parking-related information.
                        </p>
                    </div>
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/account.php" class="hover:text-[#6ae6fc] transition text-lg"><i class="fa-solid fa-user"></i> Account</a>
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