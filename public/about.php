<?php
$title = "About Us";

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
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-start">

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
                    <p class="text-gray-700">EveryonesParking is a premier car parking provider that aims to redefine the parking experience for customers. With a commitment to simplicity and convenience, we strive to eliminate hassles and simplify the process. We offer cost-effective solutions tailored to meet diverse clientele needs, with locations throughout the UK.</p>
                    <p class="text-gray-700 mt-4">Our relentless commitment to customer satisfaction focuses on straightforward directions, intuitive technology, and secure facilities. We invest in cutting-edge technologies and industry best practices to enhance urban mobility.</p>
                </div>
            </div>
    </section>

    <!-- SECTION 2 -->
    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16">

        <!-- Inner content -->
        <!-- Centered Content -->
        <div class="max-w-4xl mx-auto px-6 text-center">

            <p class="text-3xl text-[#6ae6fc] font-bold">We are more than just a parking provider</p>
            <p class="text-gray-700 mt-4 text-3xl">We offer excellence, reliability, and customer-centric service, ensuring a seamless parking experience.</p>
        </div>

    </section>

    <!-- SECTION 3 -->
    <section id="section-3" class="relative bg-white overflow-hidden pt-16">

        <!-- Inner content -->
        <!-- Image Banner -->
        <div class="max-w-7xl mx-auto px-6 text-center">

            <img src="/images/about-us.jpg" alt="about us image" class="w-full h-72 object-cover rounded-lg shadow-lg">
        </div>
    </section>

    <!-- SECTION 4 -->
    <section id="section-4" class="relative bg-white py-20 overflow-hidden">

        <div class="max-w-7xl mx-auto px-6">

            <!-- Header -->
            <div class="mb-16 max-w-xl">
                <p class="text-3xl text-gray-800 font-medium">EveryonesParking</p>
                <p class="text-2xl text-gray-700 font-bold mb-4">Timeline</p>
                <div class="w-16 h-1 bg-[#6ae6fc]"></div>
            </div>

            <!-- Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-center">

                <!-- Timeline -->
                <div class="lg:col-span-2 relative">

                    <!-- Vertical line -->
                    <div class="hidden lg:block absolute left-3 top-0 bottom-0 w-1 bg-gray-200"></div>

                    <div class="space-y-16">

                        <!-- Item 1 -->
                        <div class="relative pl-10">
                            <!-- Dot -->
                            <div class="absolute left-0 top-2 w-6 h-6 bg-[#6ae6fc] rounded-full"></div>

                            <p class="inline-block text-white text-sm font-bold bg-[#6ae6fc] px-3 py-1 rounded mb-3">
                                2026
                            </p>

                            <p class="text-xl font-bold text-gray-800 mb-2">
                                EveryonesParking is launched
                            </p>

                            <p class="text-gray-600">
                                EveryonesParking was launched with the mission to simplify the parking experience, making it easier for users to find parking spaces and for space owners to earn additional income by renting out their available spaces.
                            </p>
                        </div>

                        <!-- Item 2 -->
                        <div class="relative pl-10">
                            <!-- Dot -->
                            <div class="absolute left-0 top-2 w-6 h-6 bg-[#6ae6fc] rounded-full"></div>

                            <p class="inline-block text-white text-sm font-bold bg-[#6ae6fc] px-3 py-1 rounded mb-3">
                                Next
                            </p>

                            <p class="text-xl font-bold text-gray-800 mb-2">
                                The future
                            </p>

                            <p class="text-gray-600">
                                We aim to revolutionise the parking market by introducing cutting-edge electric vehicle (EV) technology and expanding our platform nationwide. Our growth will include partnerships with hotels, supermarket chains, car park operators, landowners, local businesses, charities, and schools across the UK.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Image -->
                <div class="flex justify-center">
                    <img
                        src="/images/about-us-2.jpg"
                        alt="Timeline"
                        class="w-full h-80 object-cover rounded-xl shadow-lg">
                </div>

            </div>
        </div>

    </section>
    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>