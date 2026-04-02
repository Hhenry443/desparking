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

  <!-- HERO / SECTION -->
  <section id="section-1" class="bg-white pt-20 lg:pt-32 pb-16 lg:pb-24 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

      <!-- LEFT: IMAGE -->
      <div class="flex justify-center lg:justify-start">
        <img
          src="/images/rentyourspace-1.png"
          alt="man on phone"
          class="w-full max-w-md lg:max-w-lg object-contain">
      </div>

      <!-- RIGHT: TEXT -->
      <div class="max-w-xl">
        <h2 class="text-3xl lg:text-4xl font-bold text-[#060745] leading-tight mb-4">
          Begin making money as a <br>
          <span class="font-medium text-gray-700">DesParking host.</span>
        </h2>

        <div class="w-16 h-1 bg-[#6ae6fc] mb-6"></div>

        <p class="text-gray-600 mb-4">
          Are you tired of your driveway or garage sitting empty? Wondering how you can rent out your car parking space?
        </p>

        <p class="text-gray-600 mb-6">
          Turn your unused parking space into a profitable asset. Our platform connects space owners like you with drivers looking for convenient parking.
        </p>

        <!-- BULLETS -->
        <div class="space-y-3 mb-8 font-bold">
          <div class="flex items-center gap-3">
            <span class="text-[#060745]">›</span>
            <p class="text-gray-800">List Your Space</p>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-[#060745]">›</span>
            <p class="text-gray-800">Set Your Rates</p>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-[#060745]">›</span>
            <p class="text-gray-800">Manage Your Bookings</p>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-[#060745]">›</span>
            <p class="text-gray-800">Earn Passive Income</p>
          </div>
        </div>

        <!-- CTA -->
        <a href="/list-space"
          class="inline-block bg-[#6ae6fc] text-[#060745] font-semibold px-6 py-3 rounded-lg hover:opacity-90 transition">
          Rent Your Space
        </a>
      </div>

    </div>
  </section>

  <section id="section-2" class="relative bg-gray-50 pt-16 pb-16 overflow-hidden">

    <!-- Background shape (desktop only) -->
    <div class="hidden lg:block absolute left-0 bottom-0 w-[300px] h-[300px] bg-gray-200 rounded-full opacity-50 translate-x-[-30%] translate-y-[30%]"></div>

    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

      <!-- TEXT -->
      <div class="relative z-10">

        <p class="text-[#6ae6fc] font-semibold uppercase tracking-wide mb-2">
          How it works
        </p>

        <h2 class="text-2xl lg:text-4xl font-bold text-gray-900 mb-4">
          Start earning money
        </h2>

        <p class="text-gray-600 mb-8 max-w-md">
          There are several benefits of renting out your car parking space to others through DesParking:
        </p>

        <!-- STEPS -->
        <div class="space-y-6">

          <!-- STEP -->
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-[#060745] text-white flex items-center justify-center rounded-lg text-sm lg:text-lg font-bold shrink-0">
              1
            </div>
            <div>
              <p class="font-semibold text-gray-900 text-base lg:text-xl">List Your Space</p>
              <p class="text-gray-600 text-sm">
                Share details about your parking space and make it available for drivers to find and book.
              </p>
            </div>
          </div>

          <!-- STEP -->
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-[#060745] text-white flex items-center justify-center rounded-lg text-sm lg:text-lg font-bold shrink-0">
              2
            </div>
            <div>
              <p class="font-semibold text-gray-900 text-base lg:text-xl">Get Bookings</p>
              <p class="text-gray-600 text-sm">
                Receive reservations from drivers looking for parking in your area.
              </p>
            </div>
          </div>

          <!-- STEP -->
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-[#060745] text-white flex items-center justify-center rounded-lg text-sm lg:text-lg font-bold shrink-0">
              3
            </div>
            <div>
              <p class="font-semibold text-gray-900 text-base lg:text-xl">Get Paid</p>
              <p class="text-gray-600 text-sm">
                Earn money directly from each booking with secure and hassle-free payments.
              </p>
            </div>
          </div>

        </div>

        <!-- CTA -->
        <a href="/create.php"
          class="inline-block mt-8 bg-[#6ae6fc] text-[#060745] font-semibold px-6 py-3 rounded-lg hover:opacity-90 transition">
          Rent Your Space
        </a>

      </div>

      <!-- IMAGES -->
      <div class="relative flex justify-center mt-10 lg:mt-0">

        <!-- MOBILE: single image -->
        <img
          src="/images/partners-man.jpg"
          class="w-full max-w-sm rounded-xl shadow-lg lg:hidden">

        <!-- DESKTOP: stacked -->
        <div class="hidden lg:block relative">
          <img
            src="/images/whyrent-2.jpg"
            class="w-80 h-96 object-cover rounded-xl shadow-lg">

          <img
            src="/images/partners-man.jpg"
            class="absolute bottom-[-60px] right-[-60px] w-72 rounded-xl shadow-xl border-4 border-white">
        </div>

      </div>

    </div>
  </section>

  <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>