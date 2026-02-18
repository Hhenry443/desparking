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
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-start">

            <!-- LEFT BOX -->
            <div>
                <p class="text-4xl font-bold text-[#6ae6fc]">Redefining Parking</p>
                <p class="text-3xl text-gray-700 font-medium mt-2">with Simplicity, Convenience, and Excellence.</p>
            </div>

            <!-- RIGHT BOX -->
            <div class="flex justify-center h-full">
                <!-- Vertical line -->
                <div class="w-2 bg-[#6ae6fc] h-full mx-4"></div>
                <div class="flex flex-col justify-center h-full">
                    <p class="text-gray-700">DesParking is a premier car parking provider that aims to redefine the parking experience for customers. With a commitment to simplicity and convenience, we strive to eliminate hassles and simplify the process. We offer cost-effective solutions tailored to meet diverse clientele needs, with locations throughout the UK.</p>
                    <p class="text-gray-700 mt-4">Our relentless commitment to customer satisfaction focuses on straightforward directions, intuitive technology, and secure facilities. We invest in cutting-edge technologies and industry best practices to enhance urban mobility.</p>
                </div>
            </div>
    </section>

    <!-- SECTION 2 -->
    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16">

        <!-- Inner content -->
        <!-- Centered Content -->
        <div class="max-w-4xl mx-auto px-10 text-center">

            <p class="text-3xl text-[#6ae6fc] font-bold">We are more than just a parking provider</p>
            <p class="text-gray-700 mt-4 text-3xl">We offer excellence, reliability, and customer-centric service, ensuring a seamless parking experience.</p>
        </div>

    </section>

    <!-- SECTION 3 -->
    <section id="section-3" class="relative bg-white overflow-hidden pt-16">

        <!-- Inner content -->
        <!-- Image Banner -->
        <div class="max-w-7xl mx-auto px-10 text-center">

            <img src="/images/about-us.jpg" alt="about us image" class="w-full h-auto rounded-lg shadow-lg">
        </div>
    </section>

    <!-- SECTION 4 -->
    <section id="section-4" class="relative bg-white overflow-hidden pt-16 pb-16">
        <!-- Inner content -->
        <!-- 3 column grid Content -->
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 md:grid-cols-3 gap-12 text-left">

            <!-- Column 1 - Empty -->
            <div>
                
            </div> 

            <!-- Column 2 -->
            <div class="flex flex-col">
                <p class="text-3xl text-gray-800 font-medium mb-1">Desparking's</p>
                <p class="text-2xl text-gray-700 font-bold mb-6">Timeline</p>
                <div class="w-16 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="inline-block text-white text-2xl font-bold bg-[#6ae6fc] px-3 py-1 rounded w-fit mb-4">
                    2023
                </p>
                <p class="text-gray-800 text-xl font-bold mb-2">A decision is made</p>
                <p class="text-gray-700 text-md">Desmond relocated to Canary Wharf and quickly realised the high demand for parking as he struggled to find available spaces in his residential development. While conducting research, he discovered the potential to monetise underutilised parking spaces. This insight, combined with further market analysis, led him to the decision to create his own platform.</p>
            </div>

            <!-- Column 3 - image -->
            <div class="flex flex-col">
                <img src="/images/about-us-2.jpg" alt="Timeline 2023" class="w-full h-auto rounded-lg shadow-lg">
            </div>

            <!-- Column 3 -->
            <div class="flex flex-col">
                <p class="inline-block text-white text-2xl font-bold bg-[#6ae6fc] px-3 py-1 rounded w-fit mb-4">
                    2024
                </p>
                <p class="text-gray-800 text-xl font-bold mb-2">DesParking is launched</p>
                <p class="text-gray-700 text-md">DesParking was launched with the mission to simplify the parking experience, making it easier for users to find parking spaces and for space owners to earn additional income by renting out their available spaces.</p>
            </div>

            <!-- Column 4 -->
            <div class="flex flex-col">
                <p class="inline-block text-white text-2xl font-bold bg-[#6ae6fc] px-3 py-1 rounded w-fit mb-4">
                    2024
                </p>
                <p class="text-gray-800 text-xl font-bold mb-2">DesParking joins the BPA</p>
                <p class="text-gray-700 text-md">DesParking became a member of the British Parking Association (BPA). By engaging with industry leaders and experts, we are able to exchange valuable knowledge, explore innovative solutions, and stay ahead of key developments in the parking sector. This collaboration ensures we continually enhance our services and remain responsive to the evolving needs of our clients.</p>
            </div>

            <!-- Column 5 -->
            <div class="flex flex-col">
                <p class="inline-block text-white text-2xl font-bold bg-[#6ae6fc] px-3 py-1 rounded w-fit mb-4">
                    2025
                </p>
                <p class="text-gray-800 text-xl font-bold mb-2">The future</p>
                <p class="text-gray-700 text-md">We aim to revolutionise the parking market by introducing cutting-edge electric vehicle (EV) technology and expanding our platform nationwide. Our growth will include partnerships with hotels, supermarket chains, car park operators, landowners, local businesses, charities, and schools across the UK.</p>
            </div>
    </section>
    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>