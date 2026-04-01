<?php

$title = 'Hospitality Solutions';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
<!doctype html>
<html>

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="min-h-screen bg-white">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- HERO / SECTION 1 -->
    <section id="section-1" class="relative bg-white overflow-hidden pt-28 lg:pt-48 pb-12 lg:pb-32">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-center justify-center">

            <!-- LEFT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Hospitality Parking</h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-md mb-6">Our hospitality parking services are designed to cater to the unique needs of individuals seeking premium parking solutions for their hospitality events. Whether you're attending a wedding, a corporate gathering, or a special occasion, we offer a range of options to ensure your parking experience is seamless and stress-free.</p>
                <p class="text-gray-700 text-md mb-6">We provide tailored parking solutions for various hospitality events, ensuring ample space and convenient access for your guests.</p>
            </div>

            <!-- RIGHT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/desparking-hospitality-image1.jpg" alt="man on phone" class="w-full h-72 object-cover rounded-lg shadow-lg">
            </div>
    </section>

    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-center justify-center">

            <!-- LEFT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/desparking-hospitality-image2.jpg" alt="man on phone" class="w-full h-72 object-cover rounded-lg shadow-lg">
            </div>

            <!-- RIGHT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Hospitality Parking <span class="text-[#6ae6fc]">Spaces to Hire</span></h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-sm mb-6">Are you a venue owner, event planner, or simply someone with a vacant space that you want to make a passive income from? EveryonesParking provides a platform for you to list your available parking spaces and connect with individuals seeking convenient and reliable parking solutions.</p>
                <p class="text-gray-700 text-sm mb-6">Listing your parking space on EveryonesParking has many benefits, such as reaching a wider audience of potential customers seeking hospitality parking. Our booking process is extremely user-friendly, making it easy for guests to book their parking spaces. We also offer secure payment options to ensure hassle-free transactions.</p>
                <p class="text-gray-700 text-sm mb-6">Join EveryonesParking today and experience the epitome of parking convenience.</p>
                <div class="flex flex-wrap gap-4 mt-6">
                    <button onclick="document.getElementById('contact-modal').classList.remove('hidden')"
                        class="inline-block bg-[#6ae6fc] text-[#060745] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300 cursor-pointer">
                        Contact Us
                    </button>
                    <a href="/map.php" class="inline-block bg-[#060745] text-[#ffffff] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300">Book Now</a>
                </div>
            </div>
    </section>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>
    <?php include_once __DIR__ . '/partials/contact-modal.php'; ?>

</body>

</html>