<?php

$title       = "Business Solutions";
$description = "Parking solutions tailored for businesses. Manage staff parking, reduce costs, and streamline your operations with EveryonesParking.";

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
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Business Parking Solutions</h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-md mb-6">Are you a business owner with excess parking space? Turn your unused parking into a profitable asset by listing it on EveryonesParking. We offer business parking solutions in various locations throughout the UK that are designed to meet the unique needs of modern businesses.</p>
                <p class="text-gray-700 text-md mb-6">Whether you're a bustling corporate office, a thriving small business, or a frequent traveller, our premium parking services provide the convenience and efficiency you require. Our platform connects businesses with available parking spaces, making it easy for you to generate additional revenue.</p>
            </div>

            <!-- RIGHT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/business-image.jpg" alt="man on phone" class="w-full h-72 object-cover rounded-lg shadow-lg">
            </div>
    </section>

    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-center justify-center">

            <!-- LEFT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/desparking-business-image2.jpg" alt="man on phone" class="w-full h-72 object-cover rounded-lg shadow-lg">
            </div>

            <!-- RIGHT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 ">Why choose EveryonesParking for</h2>
                <h2 class="text-3xl font-bold text-gray-800 mb-6"><span class="text-[#6ae6fc]">Business Parking?</span></h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-sm mb-6">Our extensive network of parking facilities is strategically located in convenient, high-traffic locations, ensuring easy access to your business. We also prioritise the safety of your vehicles with reliable state-of-the-art security measures often found at our parking spots, including CCTV surveillance and controlled access.</p>
                <p class="text-gray-700 text-sm mb-6">Choose from a variety of flexible booking options, including monthly spaces available, to suit your business's parking needs and enjoy competitive pricing without compromising on quality. Our transparent pricing structure ensures you get the best value for your money.</p>
                <p class="text-gray-700 text-sm mb-6">To learn more about our business parking solutions or to list your parking space, please fill out our contact form. Our team is ready to assist you in finding the perfect parking solution for your business.</p>
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

</body>

</html>