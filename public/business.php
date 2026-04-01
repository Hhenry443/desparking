<?php

$title = "Business Solutions";

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
                <p class="text-gray-700 text-sm mb-6">To learn more about our business parking solutions or to list your parking space, please email us at support@desparking.uk, or fill out our contact form. Our team is ready to assist you in finding the perfect parking solution for your business.</p>
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

    <!-- CONTACT MODAL -->
    <div id="contact-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 relative">

            <button onclick="closeContactModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition cursor-pointer"
                aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <h2 class="text-xl font-bold text-[#060745] mb-1">Contact Us</h2>
            <p class="text-sm text-gray-500 mb-5">Send us a message and we'll get back to you.</p>

            <div id="contact-success" class="hidden mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
                Message sent! We'll be in touch soon.
            </div>
            <div id="contact-error" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm"></div>

            <form id="contact-form" class="space-y-4" novalidate>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" required
                        class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#6ae6fc] focus:outline-none"
                        placeholder="John Smith">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required
                        class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#6ae6fc] focus:outline-none"
                        placeholder="you@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea name="message" required rows="4"
                        class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#6ae6fc] focus:outline-none resize-none"
                        placeholder="How can we help?"></textarea>
                </div>
                <button type="submit" id="contact-submit"
                    class="w-full bg-[#6ae6fc] hover:bg-[#5ad0e0] text-[#060745] font-bold py-2.5 rounded-lg transition cursor-pointer">
                    Send Message
                </button>
            </form>
        </div>
    </div>

    <script>
        function closeContactModal() {
            document.getElementById('contact-modal').classList.add('hidden');
            document.getElementById('contact-form').reset();
            document.getElementById('contact-success').classList.add('hidden');
            document.getElementById('contact-error').classList.add('hidden');
        }

        // Close on backdrop click
        document.getElementById('contact-modal').addEventListener('click', function(e) {
            if (e.target === this) closeContactModal();
        });

        document.getElementById('contact-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('contact-submit');
            const errorBox = document.getElementById('contact-error');
            const successBox = document.getElementById('contact-success');

            errorBox.classList.add('hidden');
            successBox.classList.add('hidden');
            btn.disabled = true;
            btn.textContent = 'Sending…';

            const data = new FormData(this);

            try {
                const res = await fetch('/php/api/index.php?id=contactEnquiry', {
                    method: 'POST',
                    body: data,
                });
                const json = await res.json();

                if (res.ok) {
                    this.reset();
                    successBox.classList.remove('hidden');
                } else {
                    errorBox.textContent = json.feedback || 'Something went wrong. Please try again.';
                    errorBox.classList.remove('hidden');
                }
            } catch {
                errorBox.textContent = 'Network error. Please try again.';
                errorBox.classList.remove('hidden');
            }

            btn.disabled = false;
            btn.textContent = 'Send Message';
        });
    </script>

</body>

</html>