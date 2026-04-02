<footer class="bg-[#060745] text-white pt-20">

    <!-- Top: Links -->
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-24">

        <!-- Useful Links -->
        <div>
            <h4 class="text-[#6ae6fc] font-semibold mb-4">Useful Links</h4>
            <ul class="space-y-2 text-gray-300">
                <li><a href="/map.php" class="hover:text-white">Find a parking space.</a></li>
                <li><a href="/why-rent.php" class="hover:text-white">Rent your parking space.</a></li>
                <li><a href="/map.php" class="hover:text-white">Monthly spaces.</a></li>
                <li><button onclick="document.getElementById('contact-modal').classList.remove('hidden')" class="hover:text-white cursor-pointer text-gray-300">Contact us.</button></li>
            </ul>
        </div>

        <!-- Parking Solutions -->
        <div>
            <h4 class="text-[#6ae6fc] font-semibold mb-4">Parking Solutions</h4>
            <ul class="space-y-2 text-gray-300">
                <li><a href="/how-we-work.php" class="hover:text-white">How We Work.</a></li>
                <li><a href="/business.php" class="hover:text-white">Business Parking.</a></li>
                <li><a href="/event.php" class="hover:text-white">Event Parking.</a></li>
                <li><a href="/hospitality.php" class="hover:text-white">Hospitality Parking.</a></li>
            </ul>
        </div>

        <!-- About Us -->
        <div>
            <h4 class="text-[#6ae6fc] font-semibold mb-4">About Us</h4>
            <ul class="space-y-2 text-gray-300">
                <li><a href="/faq.php" class="hover:text-white">FAQs.</a></li>
                <li><a href="/about.php" class="hover:text-white">About Us.</a></li>
                <li><a href="/news.php" class="hover:text-white">Blog.</a></li>
                <li><a href="/partners.php" class="hover:text-white">Partners.</a></li>
            </ul>
        </div>

        <!-- Brand (desktop only position) -->
        <div class="hidden md:block">
            <div class="flex items-center gap-2 mb-4">
                <img src="/images/des-logo-new.png" alt="Logo" class="h-8 w-auto">
            </div>

            <div class="flex gap-4 text-[#6ae6fc] text-xl mt-4">
                <a href="https://www.instagram.com/everyonesparking"><i class="fa-brands fa-instagram hover:text-white transition"></i></a>
                <a href="https://www.facebook.com/share/1GtGLVNJ3y/?mibextid=wwXIfr"><i class="fa-brands fa-facebook hover:text-white transition"></i></a>
                <a href="https://www.linkedin.com/company/everyonesparking/"><i class="fa-brands fa-linkedin hover:text-white transition"></i></a>
                <a href="https://x.com/evonesparking?s=21"><i class="fa-brands fa-x-twitter hover:text-white transition"></i></a>
            </div>
        </div>

    </div>

    <!-- Divider (mobile only) -->
    <div class="mt-10 border-t border-white/10 md:hidden"></div>

    <!-- Brand (mobile only, bottom row) -->
    <div class="max-w-7xl mx-auto px-6 mt-6 md:hidden">
        <div class="flex flex-col items-center text-center">
            <img src="/images/des-logo-new.png" alt="Logo" class="h-8 w-auto mb-4">

            <div class="flex gap-4 text-[#6ae6fc] text-xl">
                <a href="#"><i class="fa-brands fa-instagram hover:text-white transition"></i></a>
                <a href="#"><i class="fa-brands fa-facebook hover:text-white transition"></i></a>
                <a href="#"><i class="fa-brands fa-linkedin hover:text-white transition"></i></a>
                <a href="#"><i class="fa-brands fa-x-twitter hover:text-white transition"></i></a>
            </div>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="mt-10 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
            <span>©2026 EveryonesParking</span>
            <div class="flex gap-4 mt-2 md:mt-0">
                <a href="/privacy.php" class="hover:text-white">Privacy Policy</a>
                <span>|</span>
                <a href="/terms-and-conditions.php" class="hover:text-white">Terms & Conditions</a>
                <span>|</span>
                <a href="/parking-contract.php" class="hover:text-white">Parking Contract</a>
            </div>
        </div>
    </div>

</footer>

<?php include_once __DIR__ . '/contact-modal.php'; ?>

<!-- Cookie consent banner -->
<div id="cookie-banner"
     class="fixed bottom-0 inset-x-0 z-50 bg-[#060745] text-white px-6 py-4 shadow-2xl hidden">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-300 text-center sm:text-left">
            We use cookies to keep you logged in and to make our site work. By continuing to use EveryonesParking, you agree to our
            <a href="/privacy.php" class="underline text-[#6ae6fc] hover:text-white transition">Privacy Policy</a>.
        </p>
        <div class="flex gap-3 flex-shrink-0">
            <button onclick="acceptCookies()"
                    class="px-5 py-2 rounded-xl bg-[#6ae6fc] text-gray-900 text-sm font-semibold hover:bg-cyan-400 transition">
                Accept
            </button>
            <button onclick="declineCookies()"
                    class="px-5 py-2 rounded-xl border border-white/20 text-sm font-semibold hover:bg-white/10 transition">
                Decline
            </button>
            <a href="/privacy.php"
               class="px-5 py-2 rounded-xl border border-white/20 text-sm font-semibold hover:bg-white/10 transition hidden sm:inline-flex">
                Learn more
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        if (!localStorage.getItem('ep_cookie_consent')) {
            document.getElementById('cookie-banner').classList.remove('hidden');
        }
    })();

    function acceptCookies() {
        localStorage.setItem('ep_cookie_consent', '1');
        document.getElementById('cookie-banner').classList.add('hidden');
    }

    function declineCookies() {
        // Just dismiss for this session — banner will return on next visit
        document.getElementById('cookie-banner').classList.add('hidden');
    }
</script>