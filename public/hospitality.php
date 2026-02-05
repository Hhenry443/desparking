<?php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>DesParking</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

    <link href="./css/output.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

<body class="min-h-screen bg-white">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- HERO / SECTION 1 -->
    <section id="section-1" class="relative bg-white overflow-hidden pt-48 pb-32">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-center justify-center">

            <!-- LEFT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Hospitality Parking</h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-md mb-6">Our hospitality parking services are designed to cater to the unique needs of individuals seeking premium parking solutions for their hospitality events. Whether you're attending a wedding, a corporate gathering, or a special occasion, we offer a range of options to ensure your parking experience is seamless and stress-free.</p>
                <p class="text-gray-700 text-md mb-6">We provide tailored parking solutions for various hospitality events, ensuring ample space and convenient access for your guests.</p>
            </div>

            <!-- RIGHT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/desparking-hospitality-image1.jpg" alt="man on phone" class="w-auto h-auto rounded-lg shadow-lg">
            </div>
    </section>

    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-center justify-center">

            <!-- LEFT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/desparking-hospitality-image2.jpg" alt="man on phone" class="w-auto h-auto rounded-lg shadow-lg">
            </div>

            <!-- RIGHT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Hospitality Parking <span class="text-[#6ae6fc]">Spaces to Hire</span></h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-sm mb-6">Are you a venue owner, event planner, or simply someone with a vacant space that you want to make a passive income from? DesParking provides a platform for you to list your available parking spaces and connect with individuals seeking convenient and reliable parking solutions.</p>
                <p class="text-gray-700 text-sm mb-6">Listing your parking space on DesParking has many benefits, such as reaching a wider audience of potential customers seeking hospitality parking. Our booking process is extremely user-friendly, making it easy for guests to book their parking spaces. We also offer secure payment options to ensure hassle-free transactions.</p>
                <p class="text-gray-700 text-sm mb-6">Join DesParking today and experience the epitome of parking convenience.</p>
                <div class="w-full flex gap-6">
                    <a href="/contact.php" class="w-1/4 mt-6 inline-block bg-[#6ae6fc] text-[#060745] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300">Contact Us</a>
                    <a href="/map.php" class="w-1/4 mt-6 inline-block bg-[#060745] text-[#ffffff] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300">Book Now</a>
                </div>
            </div>
    </section>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>