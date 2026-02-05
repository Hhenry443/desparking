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
    <section id="section-1" class="relative bg-white overflow-hidden pt-48 pb-16">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-start">

            <!-- LEFT BOX -->
            <div>
                <img src="/images/how-we-work.jpg" alt="How we work" class="w-full h-auto rounded-lg shadow-lg">
            </div>

            <!-- RIGHT BOX -->
            <div class="flex flex-col justify-center h-full">
                <!-- Heading -->
                <h2 class="text-md font-bold text-gray-400 mb-6">DESPARKING</h2>
                <div class="w-full flex justify-between mb-6">
                    <h3 class="text-3xl font-extrabold text-gray-900 mb-6">Simple & Instant Booking</h3>
                    <div class="w-4 h-10 bg-[#6ae6fc]"></div>
                </div>
                <p class="text-gray-700 text-sm w-2/3">Whatever you need parking for, Des Parking makes finding a parking space simple and convenient.</p>
                <a href="/booking.php" class="w-1/2 mt-6 inline-block bg-[#6ae6fc] text-[#060745] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300">Book Your Space</a>
            </div>
    </section>

    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-32">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-start">

            <!-- LEFT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Search, Book & Park</h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <h3 class="text-xl font-bold text-gray-800 mb-4"><span class="text-[#6ae6fc]">Search</span> for a space</h3>
                <p class="text-gray-700 text-sm mb-6">Enter your destination in our easy-to-use search bar and browse various parking options filtered by price, distance and amenities. Whether you need a space for a few hours, days, or longer, we’ve got you covered.</p>
                <h3 class="text-xl font-bold text-gray-800 mb-4"><span class="text-[#6ae6fc]">Book</span> your parking</h3>
                <p class="text-gray-700 text-sm mb-6">Select your preferred parking space and complete the booking process. Our secure payment system protects your details, giving you peace of mind.</p>
                <h3 class="text-xl font-bold text-gray-800 mb-4"><span class="text-[#6ae6fc]">Park</span> stress free</h3>
                <p class="text-gray-700 text-sm mb-6">You’ll receive precise directions to your easy-to-find parking space, and then you can focus on your plans without worrying about parking restrictions or time limits.</p>
            </div>

            <!-- RIGHT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/how-we-work-image-2.png" alt="How we work" class="w-full h-auto rounded-lg shadow-lg">
            </div>
    </section>

    <section id="section-3" class="relative bg-white overflow-hidden pt-16 pb-32">

        <!-- Inner content -->
        <div class="max-w-7xl mx-auto px-10">
            <h1 class="text-md font-bold text-[#6ae6fc]">THE PROCESS</h1>
            <h2 class="text-3xl font-bold text-gray-800 mb-6">How to Book</h2>

            <div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="flex flex-col p-6 bg-gray-100 rounded-lg shadow-md">
                    <div class="w-12 h-12 bg-[#6ae6fc] text-white text-2xl font-black flex items-center justify-center mb-4">
                        1
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Enter your destination</h3>
                    <p class="text-gray-700 text-sm">Input where you would like to go and select from hourly or monthly parking, then pick your space to book</p>
                </div>

                <div class="flex flex-col p-6 bg-gray-100 rounded-lg shadow-md">
                    <div class="w-12 h-12 bg-[#6ae6fc] text-white text-2xl font-black flex items-center justify-center mb-4">
                        2
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Enter your times</h3>
                    <p class="text-gray-700 text-sm">Choose when you will need your parking space - or book monthly for a rolling subscription.</p>
                </div>

                <div class="flex flex-col p-6 bg-gray-100 rounded-lg shadow-md">
                    <div class="w-12 h-12 bg-[#6ae6fc] text-white text-2xl font-black flex items-center justify-center mb-4">
                        3
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Choose your space</h3>
                    <p class="text-gray-700 text-sm">Choose one of our spaces, and fill out your details.</p>
                </div>

                <div class="flex flex-col p-6 bg-gray-100 rounded-lg shadow-md">
                    <div class="w-12 h-12 bg-[#6ae6fc] text-white text-2xl font-black flex items-center justify-center mb-4">
                        4
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Pay</h3>
                    <p class="text-gray-700 text-sm">Confirm payment and complete your booking, upon which the full parking details will be provided to you instantly.</p>
                </div>

                <div class="flex flex-col p-6 bg-gray-100 rounded-lg shadow-md">
                    <div class="w-12 h-12 bg-[#6ae6fc] text-white text-2xl font-black flex items-center justify-center mb-4">
                        5
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Review</h3>
                    <p class="text-gray-700 text-sm">Tell us about your experience with DesParking and give the space a star rating.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>