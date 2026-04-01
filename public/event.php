<?php

$title = "Event Solutions";

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
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Event Parking Services</h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-md mb-6">Looking for a hassle-free parking solution for your next event? At EveryonesParking, our event parking services offer a wide range of options catering to your needs. Whether you're attending a concert, festival, or sporting event, we've got a space waiting for you.</p>
                <p class="text-gray-700 text-md mb-6">Our event parking services include pre-booked parking in many locations across the UK. Secure your parking space in advance to avoid the stress of searching for parking on the day of the event. With our flexible booking options, you can choose from a variety of booking periods to suit your schedule.</p>
                <p class="text-gray-700 text-md mb-6">Our event parking spaces are conveniently located near popular venues across the UK, ensuring easy access to your destination.</p>
            </div>

            <!-- RIGHT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/desparking-event-image1.jpg" alt="man on phone" class="w-full h-72 object-cover rounded-lg shadow-lg">
            </div>
    </section>

    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16">

        <!-- Inner content -->
        <!-- Two-column grid -->
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-center justify-center">

            <!-- LEFT BOX -->
            <div class="flex flex-col justify-center h-full">
                <img src="/images/desparking-event-image2.jpg" alt="stadium" class="w-full h-72 object-cover rounded-lg shadow-lg">
            </div>

            <!-- RIGHT BOX -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Event <span class="text-[#6ae6fc]">Parking Solutions</span></h2>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
                <p class="text-gray-700 text-sm mb-6">If you’re looking to earn extra income, you can rent out your unused parking space on our site and earn money from attendees of popular events. Our event parking solutions platform makes it easy to list your space and connect with potential renters.</p>
                <p class="text-gray-700 text-sm mb-6">The benefits of renting out your parking space are that you can earn passive income by monetising your unused parking space with no hassle. Additionally, you can contribute to a sustainable community and help reduce traffic congestion and its environmental impact.</p>
                <p class="text-gray-700 text-sm mb-6">We have parking spaces available near popular event locations such as Wembley Stadium, O2 Arena, Manchester Arena, the NEC, and many more. Sign up to EveryonesParking and your perfect event parking solution today.</p>
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