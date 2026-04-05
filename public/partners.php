<?php
$title       = "Partners of EveryonesParking";
$description = "Meet the partners and organisations working with EveryonesParking to make parking smarter and more accessible across Australia.";

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
    <section id="section-1" class="relative bg-white overflow-hidden pt-28 lg:pt-48 pb-12 lg:pb-16">

        <!-- Inner content -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 items-start gap-8 lg:gap-24 px-6">

            <!-- LEFT BOX -->
            <div class="bg-white w-full h-full py-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-6">Partners of EveryonesParking</h1>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-sm mb-6">At EveryonesParking, we’re proud to have established partnerships with brands across every sector, from transportation and technology to hospitality and retail. Our diverse network of partners enables us to deliver innovative solutions and enhance the overall parking experience for our customers.</p>
                <p class="text-gray-700 text-sm mb-6">Whether it’s integrating with leading navigation apps, collaborating with local businesses to offer exclusive discounts, or partnering with car manufacturers to implement smart parking solutions, we’re committed to driving value and innovation through strategic partnerships.</p>
            </div>

            <!-- RIGHT BOX -->
            <div class="w-full h-full">
                <img src="/images/partners-header.jpg" alt="Our Partners" class="w-full h-72 object-cover rounded-lg shadow-lg">
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
                -webkit-mask-image: linear-gradient(to right,
                        transparent 0%,
                        black 10%,
                        black 90%,
                        transparent 100%);
                mask-image: linear-gradient(to right,
                        transparent 0%,
                        black 10%,
                        black 90%,
                        transparent 100%);
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

    <section id="section-3" class="relative bg-white overflow-hidden pt-16 lg:pt-48 pb-12 lg:pb-32">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-center justify-center">

            <!-- LEFT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Collaborate with us </h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-sm mb-6">Join EveryonesParking‘s growing network of partners and affiliates and unlock exciting collaboration opportunities! Whether you’re an influencer with a dedicated audience or a company looking to expand your reach, we invite you to collaborate with us and be part of the future of parking.</p>
                <p class="text-gray-700 text-sm mb-6">By partnering with EveryonesParking, you’ll gain access to a wide range of benefits, including competitive commissions, exclusive promotions, and access to our cutting-edge technology platform. Together, we can revolutionize the parking experience and create value for our customers.</p>
                <div class="flex flex-wrap gap-4 mt-6">
                    <button onclick="document.getElementById('contact-modal').classList.remove('hidden')"
                        class="inline-block bg-[#6ae6fc] text-[#060745] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300 cursor-pointer">
                        Contact Us
                    </button>
                    <a href="/map.php" class="inline-block bg-[#060745] text-[#ffffff] font-bold text-center px-6 py-3 rounded-lg shadow-md hover:bg-[#5ad0e0] transition duration-300">Book Now</a>
                </div>
            </div>

            <!-- RIGHT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/partners-man.jpg" alt="man on phone" class="w-full h-96 object-cover rounded-lg shadow-lg">
            </div>
    </section>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>