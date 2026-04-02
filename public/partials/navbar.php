<?php
$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($page)
{
    global $currentPage;
    return $currentPage === $page;
}

function navLink($page)
{
    return isActive($page)
        ? 'text-[#6ae6fc] border-b-2 border-[#6ae6fc]'
        : 'hover:text-[#6ae6fc] transition';
}
?>


<nav class="w-full h-16 bg-[#060745] backdrop-blur-md shadow-md fixed top-0 left-0 z-50 flex items-center justify-between px-6 border-b-4 border-[#6ae6fc]">
    <!-- Logo -->
    <div class="flex items-center space-x-2">
        <a href="/" class="flex items-center gap-1 text-xl font-semibold text-gray-800 leading-none">
            <img src="/images/des-logo-new.png" alt="Logo" class="h-8 w-auto">
            <span class="text-[#6ae6fc] font-semibold tracking-tighter text-3xl leading-none">
                Everyones<span class="text-white">Parking</span>
            </span>
        </a>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] === true): ?>
            <span class="ml-3 px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full self-center">
                ADMIN
            </span>
        <?php endif; ?>
    </div>


    <!-- Nav Links -->
    <div class="hidden xl:flex items-center space-x-6 text-white font-bold">
        <!-- If user is admin, show admin link -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] === true): ?>
            <a href="/admin.php" class="<?= navLink('admin.php') ?>">Admin</a>
        <?php endif; ?>

        <!-- Home link -->
        <a href="/" class="<?= navLink('index.php') ?>">Home</a>

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
        <a href="/why-rent.php" class="hover:text-[#6ae6fc] transition">Rent My Space</a>

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

            <div class="absolute left-0 mt-4 w-72 rounded-xl bg-[#060745] p-3 shadow-xl
                    opacity-0 invisible group-hover:opacity-100 group-hover:visible
                    transition-all duration-200 z-50 space-y-2">

                <a href="/news.php"
                    class="flex items-start gap-3 p-3 bg-white rounded-lg hover:bg-gray-100 transition shadow-sm">
                    <i class="fa-solid fa-book text-[#060745] text-lg mt-1"></i>
                    <div>
                        <p class="font-bold text-[#060745] text-sm">Blogs</p>
                        <p class="text-xs text-gray-500">
                            Read the EveryonesParking blog and stay caught up on all of your parking-related information.
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
        <a href="/why-rent.php"
            class="inline-block px-4 py-2 bg-white text-[#060745] rounded-lg
                hover:bg-gray-500 hover:text-white transition shadow">
            <i class="fa-solid fa-square-parking"></i> Rent Your Space
        </a>

    </div>

    <!-- Mobile Menu Icon -->
    <button id="mobile-menu-btn" class="xl:hidden p-2 rounded text-white hover:text-[#6ae6fc] transition">
        <svg id="hamburger-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg id="close-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</nav>

<!-- Mobile Menu Drawer -->
<div id="mobile-menu" class="hidden xl:hidden fixed top-16 left-0 right-0 bg-[#060745] z-50 border-b-4 border-[#6ae6fc] overflow-y-auto max-h-[calc(100vh-4rem)]">
    <div class="px-6 py-4 space-y-1 text-white text-sm font-semibold">

        <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] === true): ?>
            <a href="/admin.php" class="block py-3 border-b border-white/10 hover:text-[#6ae6fc]">Admin</a>
        <?php endif; ?>

        <a href="/" class="block py-3 border-b border-white/10 hover:text-[#6ae6fc]">Home</a>

        <!-- About Us accordion -->
        <div>
            <button onclick="toggleMobileSection('mobile-about')" class="w-full flex justify-between items-center py-3 border-b border-white/10 hover:text-[#6ae6fc]">
                About Us
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div id="mobile-about" class="hidden pl-4 py-2 space-y-2">
                <a href="/about.php" class="block py-2 text-gray-300 hover:text-white">About Us</a>
                <a href="/how-we-work.php" class="block py-2 text-gray-300 hover:text-white">How We Work</a>
                <a href="/faq.php" class="block py-2 text-gray-300 hover:text-white">FAQ</a>
                <a href="/partners.php" class="block py-2 text-gray-300 hover:text-white">Partners</a>
            </div>
        </div>

        <!-- Booking accordion -->
        <div>
            <button onclick="toggleMobileSection('mobile-booking')" class="w-full flex justify-between items-center py-3 border-b border-white/10 hover:text-[#6ae6fc]">
                Booking
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div id="mobile-booking" class="hidden pl-4 py-2 space-y-2">
                <a href="/map.php" class="block py-2 text-gray-300 hover:text-white">Booking</a>
                <a href="/monthly.php" class="block py-2 text-gray-300 hover:text-white">Monthly Spaces</a>
            </div>
        </div>

        <!-- Parking Solutions accordion -->
        <div>
            <button onclick="toggleMobileSection('mobile-solutions')" class="w-full flex justify-between items-center py-3 border-b border-white/10 hover:text-[#6ae6fc]">
                Parking Solutions
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div id="mobile-solutions" class="hidden pl-4 py-2 space-y-2">
                <a href="/business.php" class="block py-2 text-gray-300 hover:text-white">Business Parking</a>
                <a href="/event.php" class="block py-2 text-gray-300 hover:text-white">Event Parking</a>
                <a href="/hospitality.php" class="block py-2 text-gray-300 hover:text-white">Hospitality Parking</a>
            </div>
        </div>

        <a href="/why-rent.php" class="block py-3 border-b border-white/10 hover:text-[#6ae6fc]">Rent My Space</a>

        <!-- News accordion -->
        <div>
            <button onclick="toggleMobileSection('mobile-news')" class="w-full flex justify-between items-center py-3 border-b border-white/10 hover:text-[#6ae6fc]">
                News
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div id="mobile-news" class="hidden pl-4 py-2 space-y-2">
                <a href="/blog.php" class="block py-2 text-gray-300 hover:text-white">Blog</a>
            </div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/account.php" class="block py-3 border-b border-white/10 hover:text-[#6ae6fc]">Account</a>
            <a href="/logout.php" class="block py-3 border-b border-white/10 text-red-400 hover:text-red-300">Logout</a>
        <?php else: ?>
            <a href="/login.php" class="block py-3 border-b border-white/10 hover:text-[#6ae6fc]">Login</a>
        <?php endif; ?>

        <div class="pt-4 pb-2 flex flex-col gap-3">
            <a href="/map.php" class="block text-center py-3 rounded-lg bg-[#6ae6fc] text-gray-900 font-bold hover:bg-cyan-400 transition">Book Now</a>
            <a href="/why-rent.php" class="block text-center py-3 rounded-lg bg-white text-[#060745] font-bold hover:bg-gray-100 transition">Rent Your Space</a>
        </div>

    </div>
</div>

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        const hamburger = document.getElementById('hamburger-icon');
        const close = document.getElementById('close-icon');
        menu.classList.toggle('hidden');
        hamburger.classList.toggle('hidden');
        close.classList.toggle('hidden');
    });

    function toggleMobileSection(id) {
        const el = document.getElementById(id);
        el.classList.toggle('hidden');
    }
</script>