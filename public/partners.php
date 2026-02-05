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
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 items-start gap-24 px-10">

            <!-- LEFT BOX -->
            <div class="bg-white w-full h-full px-10 py-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-6">Partners of Desparking</h1>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-sm mb-6">At DesParking, we’re proud to have established partnerships with brands across every sector, from transportation and technology to hospitality and retail. Our diverse network of partners enables us to deliver innovative solutions and enhance the overall parking experience for our customers.</p>
                <p class="text-gray-700 text-sm mb-6">Whether it’s integrating with leading navigation apps, collaborating with local businesses to offer exclusive discounts, or partnering with car manufacturers to implement smart parking solutions, we’re committed to driving value and innovation through strategic partnerships.</p>
            </div>

            <!-- RIGHT BOX -->
            <div class="w-full h-full">
                <img src="/images/partners-header.jpg" alt="Our Partners" class="w-full h-auto rounded-lg shadow-lg">
            </div>
        </div>
    </section>

    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16 w-full flex items-center justify-center">
        <div class="image-marquee">
            <div class="marquee-track">
                <!-- Set 1 -->
                <img src="/images/parkso.png" alt="parkso logo">
                <img src="/images/keynest.jpeg" alt="keynest logo">
                <img src="/images/parkso.png" alt="parkso logo">
                <img src="/images/keynest.jpeg" alt="keynest logo">

                <!-- Set 2 (duplicate) -->
                <img src="/images/parkso.png" alt="parkso logo">
                <img src="/images/keynest.jpeg" alt="keynest logo">
                <img src="/images/parkso.png" alt="parkso logo">
                <img src="/images/keynest.jpeg" alt="keynest logo">
            </div>
        </div>

        <style>
            .image-marquee {
            width: 100%;
            overflow: hidden;

            /* Fade edges */
            -webkit-mask-image: linear-gradient(
                to right,
                transparent 0%,
                black 10%,
                black 90%,
                transparent 100%
            );
            mask-image: linear-gradient(
                to right,
                transparent 0%,
                black 10%,
                black 90%,
                transparent 100%
            );
            }

            .marquee-track {
            display: flex;
            width: max-content;
            align-items: center;
            animation: scroll 20s linear infinite;
            }

            .marquee-track img {
            height: 75px;
            width: auto;
            margin-right: 10rem;
            flex-shrink: 0;
            object-fit: contain;
            }

            @keyframes scroll {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(-50%);
            }
            }

        </style>
    </section>

    <section id="section-3" class="relative bg-white overflow-hidden pt-48 pb-32">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-center justify-center">

            <!-- LEFT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Collaborate with us </h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-sm mb-6">Join DesParking‘s growing network of partners and affiliates and unlock exciting collaboration opportunities! Whether you’re an influencer with a dedicated audience or a company looking to expand your reach, we invite you to collaborate with us and be part of the future of parking.</p>
                <p class="text-gray-700 text-sm mb-6">By partnering with DesParking, you’ll gain access to a wide range of benefits, including competitive commissions, exclusive promotions, and access to our cutting-edge technology platform. Together, we can revolutionize the parking experience and create value for our customers.</p>
                <div class="w-full flex gap-6">
                    <a href="/contact.php" class="w-1/4 mt-6 inline-block bg-[#6ae6fc] text-[#060745] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300">Contact Us</a>
                    <a href="/map.php" class="w-1/4 mt-6 inline-block bg-[#060745] text-[#ffffff] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300">Book Now</a>
                </div>
            </div>

            <!-- RIGHT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/partners-man.jpg" alt="man on phone" class="w-auto h-auto rounded-lg shadow-lg">
            </div>
    </section>
    
    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>