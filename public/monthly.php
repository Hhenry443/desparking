<?php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

$ReadCarparks = new ReadCarparks();
$carparks = $ReadCarparks->getMonthlyCarparks();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>DesParking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
  <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

  <link href="./css/output.css" rel="stylesheet">

  <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

<body class="min-h-screen bg-white">
  <?php include_once __DIR__ . '/partials/navbar.php'; ?>

  <!-- HERO / SECTION 1 -->
  <section id="section-1" class="relative bg-white overflow-hidden pt-28 lg:pt-48 pb-12 lg:pb-32">

    <!-- Inner content -->
    <!-- Two-column grid -->
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-start">

      <!-- LEFT BOX -->
      <div>
        <p class="text-4xl font-bold">Monthly Spaces</p>
        <div class="mt-4 w-20 bg-[#6ae6fc] h-1"></div>
      </div>

      <!-- RIGHT BOX -->
      <div class="relative w-full h-[250px] rounded-xl overflow-hidden">

        <!-- Image -->
        <img
          class="w-full h-full object-cover"
          src="/images/desparking-monthly.jpg">

        <!-- Gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-tr from-[#060745]/70 via-[#060745]/30 to-transparent"></div>

        <!-- Top-left white block -->
        <div class="absolute top-0 left-0 w-1/3 h-1/2 bg-white"></div>

      </div>
    </div>
  </section>

  <!-- SECTION 2 : MONTHLY CARPARKS -->
  <section class="bg-[#f5f6f8] py-20">

    <div class="max-w-7xl mx-auto px-6">

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

        <?php foreach ($carparks as $carpark): ?>

          <div class="bg-white rounded-2xl shadow-sm overflow-hidden">

            <!-- Image -->
            <div class="h-48 w-full overflow-hidden">
              <img
                class="w-full h-full object-cover"
                src="/images/default-carpark-image.png"
                alt="<?php echo htmlspecialchars($carpark['carpark_name']); ?>">
            </div>

            <!-- Content -->
            <div class="p-6">

              <!-- Title -->
              <h3 class="text-lg font-semibold text-[#111a44] mb-3">
                <?php echo htmlspecialchars($carpark['carpark_name']); ?>
              </h3>

              <!-- Address -->
              <div class="flex items-start gap-2 text-sm text-gray-600 mb-4">
                <i class="fa-solid fa-location-dot text-[#00c2d1] mt-[2px]"></i>
                <span><?php echo htmlspecialchars($carpark['carpark_address']); ?></span>
              </div>

              <!-- Hours -->
              <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <i class="fa-solid fa-clock text-[#00c2d1]"></i>
                <span>24 Hours</span>
              </div>

              <!-- Button -->
              <a
                href="/book.php?carpark_id=<?php echo $carpark['carpark_id']; ?>"
                class="block text-center bg-[#18c3cf] hover:bg-[#12aab5] font-bold text-[#060745] py-2 rounded-xl font-medium transition">
                View space
              </a>

            </div>

          </div>

        <?php endforeach; ?>

      </div>
    </div>
  </section>

  <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>