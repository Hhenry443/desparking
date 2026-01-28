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

        <!-- Right-side background image, anchored to bottom of section -->
        <div class="absolute bottom-0 left-3/4 -translate-x-1/2 w-[480px] opacity-5 z-0 pointer-events-none">
            <img
                src="/images/desparking-icon.png"
                class="w-full grayscale object-contain"
                alt="" />
        </div>

        <!-- Inner container (tightens everything like section 3) -->
        <div class="max-w-7xl mx-auto px-10 grid grid-cols-1 lg:grid-cols-2 gap-24 items-start">

            <!-- LEFT: Text + Form -->
            <div>
                <p class="text-3xl font-bold text-[#6ae6fc]">Park Easy.</p>
                <p class="text-3xl font-bold text-black mt-2">Anytime. Anywhere.</p>

                <div class="flex flex-wrap items-center mt-4 gap-4 text-sm text-gray-600 font-bold">
                    <p>Stress-Free Booking</p>
                    <p class="text-[#6ae6fc]">24 Hr Services</p>
                    <p>Best Parking Solution</p>
                </div>

                <!-- Search box -->
                <form
                    class="bg-white rounded-3xl p-6 shadow-[0_0_25px_rgba(0,0,0,0.15)] w-full mt-10 max-w-xl"
                    action="/map.php"
                    method="GET">

                    <!-- Toggle -->
                    <div class="flex justify-center mb-6">
                        <div class="flex w-full gap-3">
                            <a
                                href="/monthly.php"
                                class="w-1/2 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold text-center">
                                Monthly
                            </a>
                            <button
                                type="button"
                                class="w-1/2 py-2 rounded-lg bg-[#6ae6fc] text-gray-800 text-sm font-semibold">
                                Hourly / Daily
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="booking_type" value="hourly">

                    <p class="text-gray-600 text-xs text-center mb-4">
                        Book a parking space on a one-off basis. Starting from half an hour.
                    </p>

                    <!-- Location -->
                    <div class="mb-6 relative">
                        <input
                            name="location"
                            type="text"
                            placeholder="Where would you like to park?"
                            class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-700 text-sm border border-gray-300
                    focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]"
                            required />
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[#6ae6fc]">📍</span>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-gray-500 text-xs mb-1">Entry Date</label>
                            <input type="date" name="entry_date"
                                class="w-full py-3 px-4 rounded-lg bg-gray-100 border border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-gray-500 text-xs mb-1">Exit Date</label>
                            <input type="date" name="exit_date"
                                class="w-full py-3 px-4 rounded-lg bg-gray-100 border border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-gray-500 text-xs mb-1">Entry Time</label>
                            <input type="time" name="entry_time"
                                class="w-full py-3 px-4 rounded-lg bg-gray-100 border border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-gray-500 text-xs mb-1">Exit Time</label>
                            <input type="time" name="exit_time"
                                class="w-full py-3 px-4 rounded-lg bg-gray-100 border border-gray-300" />
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold shadow-md">
                        Show parking spaces
                    </button>

                </form>
            </div>

            <!-- RIGHT: Images -->
            <div class="relative">
                <div class="relative flex items-start pl-12 pb-24">
                    <!-- Back image (cars, smaller, raised) -->
                    <img
                        src="/images/homepage-image-2.jpg"
                        class="absolute top-0 right-0 w-[300px] h-[420px]
                        object-cover rounded-3xl
                        border-[6px] border-white
                        shadow-lg z-0"
                        alt="" />

                    <!-- Front image (girl, bigger, dominant) -->
                    <img
                        src="/images/homepage-image-1.jpg"
                        class="relative w-[300px] h-[420px]
                        object-cover rounded-3xl
                        border-[6px] border-white
                        shadow-2xl z-10
                        mt-40"
                        alt="" />
                </div>
            </div>


        </div>
    </section>



    <section id="section-2" class="bg-gray-100 py-10">
        <!-- Dog-leg strip -->
        <div class="absolute inset-x-0 pointer-events-none">
            <!-- Vertical down from left -->
            <div class="absolute left-[20%] top-0 w-10 h-50 bg-gray-300"></div>

            <!-- Horizontal to step 2 -->
            <div class="absolute left-[20%] top-40 w-[60%] h-10 bg-gray-300"></div>

            <!-- Vertical down -->
            <div class="absolute left-[80%] top-40 w-10 h-40 bg-gray-300"></div>
        </div>

        <div class="max-w-6xl mx-auto px-6 relative ">

            <h2 class="text-center text-4xl font-bold text-gray-900 mb-20">
                How to Park
            </h2>



            <!-- Steps -->
            <div class="relative grid grid-cols-1 md:grid-cols-3 gap-12 z-10">

                <!-- Step 1 -->
                <div class="text-center">
                    <div class="mx-auto w-24 h-24 rounded-xl bg-[#060745] flex items-center justify-center text-white text-3xl font-bold">
                        1
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-[#060745]">Search from anywhere</h3>
                    <p class="mt-2 text-gray-600">
                        Search and find parking using our booking system.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="mx-auto w-24 h-24 rounded-xl bg-[#060745] flex items-center justify-center text-white text-3xl font-bold">
                        2
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-[#060745]">Book in advance or on demand</h3>
                    <p class="mt-2 text-gray-600">
                        Pre-book your space or book it when you arrive.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="mx-auto w-24 h-24 rounded-xl bg-[#060745] flex items-center justify-center text-white text-3xl font-bold">
                        3
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-[#060745]">Park with confidence</h3>
                    <p class="mt-2 text-gray-600">
                        Manage your parking session from anywhere.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION 3 – Benefits -->
    <section id="section-3" class="bg-white pt-16">
        <div class="max-w-7xl mx-auto px-8 grid grid-cols-1 lg:grid-cols-2 gap-20 items-start">

            <!-- LEFT: Images -->
            <div class="relative flex justify-center items-start pb-32">

                <!-- Left-side background image, anchored to bottom of section -->
                <div class="absolute bottom-0 right-1/2 w-[480px] opacity-5 z-0 pointer-events-none">
                    <img
                        src="/images/desparking-icon.png"
                        class="w-full grayscale object-contain"
                        alt="" />
                </div>

                <!-- Back image (CAR) – aligned with "The Benefits" text -->
                <img
                    src="/images/homepage-image-4.jpg"
                    class="w-[300px] h-[420px] object-cover rounded-2xl shadow-lg absolute left-0 top-0 border-white border-[6px]"
                    alt="" />

                <!-- Front image (GIRL) – aligned with bottom of last benefit item -->
                <img
                    src="/images/homepage-image-3.jpg"
                    class="w-[300px] h-[420px] object-cover rounded-2xl shadow-xl relative z-10 ml-32 mt-40 border-white border-[6px]"
                    alt="" />

            </div>


            <!-- RIGHT: Text content -->
            <div>
                <p class="text-cyan-400 font-bold tracking-wide uppercase mb-2">
                    The Benefits
                </p>
                <h2 class="text-4xl font-bold text-gray-900 mb-6">
                    Parking Solutions
                </h2>
                <p class="text-gray-600 mb-12 max-w-lg">
                    There are several benefits of renting out your car parking space to others through DesParking:
                </p>

                <div class="space-y-8">

                    <!-- Item 1 -->
                    <div class="flex gap-6 items-start">
                        <div class="w-16 h-16 bg-[#060745] rounded-xl flex items-center justify-center text-white">
                            <i class="fa-solid fa-mobile-screen text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Easy Management</h3>
                            <p class="text-gray-600 max-w-md">
                                With DesParking, you have full control over how you rent out your space.
                                Tell us the availability, set your own rates and offer optional extras to renters.
                            </p>
                        </div>
                    </div>


                    <!-- Item 2 -->
                    <div class="flex gap-6 items-start">
                        <div class="w-16 h-16 bg-[#060745] rounded-xl flex items-center justify-center text-white">
                            <i class="fa-solid fa-money-bill-wave text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Generating Extra Income</h3>
                            <p class="text-gray-600 max-w-md">
                                By renting out your parking space through DesParking, you can turn an underutilised asset
                                into a steady stream of additional income.
                            </p>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="flex gap-6 items-start">
                        <div class="w-16 h-16 bg-[#060745] rounded-xl flex items-center justify-center text-white">
                            <i class="fa-solid fa-car text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Expanding your reach</h3>
                            <p class="text-gray-600 max-w-md">
                                By listing your space on DesParking, you gain access to our extensive network of customers
                                looking for convenient parking solutions.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- SECTION 4 – FAQ -->
    <section id="section-4" class="bg-gray-100 py-32">
        <div class="max-w-7xl mx-auto px-8 grid grid-cols-1 lg:grid-cols-2 gap-24 items-start">

            <!-- LEFT: FAQ -->
            <div>
                <p class="text-[#6ae6fc] font-bold tracking-wide uppercase mb-2">
                    Frequently Asked Questions
                </p>
                <h2 class="text-4xl font-bold text-gray-900 mb-12">
                    Have Questions?
                </h2>

                <div class="space-y-6" id="faq">

                    <!-- FAQ Item -->
                    <div class="faq-item border-b border-gray-200 pb-4">
                        <button
                            class="faq-toggle w-full flex justify-between items-center text-left text-lg font-semibold text-gray-900 py-4">
                            How can I contact DesParking customer support for assistance?
                            <svg
                                class="faq-icon w-5 h-5 transition-transform duration-300 origin-center"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 text-gray-600">
                            <p class="pb-2">
                                Our customer support team is available 24/7 to assist you with any questions or concerns. You can reach us by email at support@desparking.uk.
                            </p>
                            <p class="pb-4">
                                Alternatively, you can use the contact form on our website to send us a message, and we’ll get back to you promptly.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item border-b border-gray-200 pb-4">
                        <button
                            class="faq-toggle w-full flex justify-between items-center text-left text-lg font-semibold text-gray-900 py-4">
                            What amenities do DesParking facilities offer?
                            <svg
                                class="faq-icon w-5 h-5 transition-transform duration-300 origin-center"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 text-gray-600">
                            <p class="pb-2">
                                Our facilities are meticulously designed not only to ensure accessibility and security but also to enhance the overall parking experience for our customers.
                            </p>
                            <p class="pb-2">
                                Depending on the location, you can expect a range of amenities that add convenience and comfort to your visit. These amenities may include covered parking options to protect your vehicle from the elements, EV charging stations for electric vehicles, and convenient car wash services to keep your vehicle looking its best.
                            </p>
                            <p class="pb-4">
                                We continuously strive to offer a comprehensive range of services that cater to your needs, making your parking experience with DesParking both comfortable and convenient.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item border-b border-gray-200 pb-4">
                        <button
                            class="faq-toggle w-full flex justify-between items-center text-left text-lg font-semibold text-gray-900 py-4">
                            Are DesParking facilities accessible for individuals with disabilities?
                            <svg
                                class="faq-icon w-5 h-5 transition-transform duration-300 origin-center"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>

                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 text-gray-600">
                            <p class="pb-2">
                                DesParking is dedicated to providing fully accessible parking facilities that comply with all relevant accessibility standards. Our spaces are designed to ensure equal access for individuals with disabilities, including conveniently located accessible parking spots and clear, obstacle-free pathways.
                            </p>
                            <p class="pb-4">
                                Our trained staff are also available to provide assistance as needed, ensuring a welcoming and inclusive experience for all customers.
                            </p>
                        </div>
                    </div>

                    <!-- View More Button -->
                    <a
                        class="mt-8 px-8 py-3 rounded-xl bg-[#6ae6fc] text-gray-900 font-semibold shadow-md hover:bg-cyan-400 transition"
                        href="/faq.php">
                        View More
                    </a>

                </div>
            </div>

            <!-- RIGHT: Image -->
            <div class="relative flex justify-center">
                <img
                    src="/images/faq-homepage.jpg"
                    class="w-[420px] h-[540px] object-cover rounded-3xl shadow-xl"
                    alt="FAQ image" />
            </div>

        </div>
    </section>

    <script>
        document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const item = button.parentElement;
                const content = item.querySelector('.faq-content');
                const icon = button.querySelector('.faq-icon');

                // Close others (accordion style)
                document.querySelectorAll('.faq-item').forEach(other => {
                    if (other !== item) {
                        other.querySelector('.faq-content').style.maxHeight = null;
                        other.querySelector('.faq-icon').classList.remove('rotate-180');
                    }
                });

                // Toggle current
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                    icon.classList.remove('rotate-180');
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                    icon.classList.add('rotate-180');
                }
            });
        });
    </script>


</body>

</html>