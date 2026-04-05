<?php

$title       = "Find Long-Term Parking";
$description = "Looking for monthly or long-term parking? Browse available spaces near you and save with EveryonesParking.";

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

          <div class="bg-white rounded-2xl shadow-sm overflow-hidden cursor-pointer hover:shadow-md transition group"
               onclick="openMonthlyDetail(<?php echo $carpark['carpark_id']; ?>, this)"
               data-carpark="<?php echo htmlspecialchars(json_encode($carpark), ENT_QUOTES); ?>">

            <!-- Image -->
            <div class="h-48 w-full overflow-hidden">
              <img
                class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300"
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

              <!-- Monthly price -->
              <?php if (!empty($carpark['monthly_price'])): ?>
              <div class="flex items-center gap-2 text-sm font-bold text-[#060745] mb-6">
                <i class="fa-solid fa-tag text-[#00c2d1]"></i>
                <span>£<?php echo number_format($carpark['monthly_price'] / 100, 2); ?>/mo</span>
              </div>
              <?php endif; ?>

              <!-- Button -->
              <div class="block text-center bg-[#18c3cf] hover:bg-[#12aab5] font-bold text-[#060745] py-2 rounded-xl transition">
                View space
              </div>

            </div>

          </div>

        <?php endforeach; ?>

      </div>
    </div>
  </section>

  <?php include_once __DIR__ . '/partials/footer.php'; ?>

  <!-- Carpark detail modal -->
  <div id="monthly-modal-overlay"
       class="fixed inset-0 z-50 bg-black/50 flex items-end sm:items-center justify-center hidden"
       onclick="if(event.target===this)closeMonthlyModal()">
    <div id="monthly-modal"
         class="bg-white w-full sm:max-w-lg sm:rounded-3xl rounded-t-3xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">

      <!-- Header -->
      <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-gray-100 flex-shrink-0">
        <p class="font-bold text-gray-900 text-base">Space details</p>
        <button onclick="closeMonthlyModal()"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition">
          <i class="fa-solid fa-xmark text-sm"></i>
        </button>
      </div>

      <!-- Scrollable body -->
      <div id="monthly-modal-body" class="flex-1 overflow-y-auto overscroll-contain p-5 space-y-4">
        <!-- Loading skeleton -->
        <div class="animate-pulse space-y-3">
          <div class="h-48 bg-gray-100 rounded-2xl"></div>
          <div class="h-5 bg-gray-200 rounded-lg w-3/4"></div>
          <div class="h-4 bg-gray-100 rounded-lg w-1/2"></div>
        </div>
      </div>

    </div>
  </div>

  <script src="./js/bookingForm.js"></script>
  <script>
    async function openMonthlyDetail(carparkId, cardEl) {
      const card    = cardEl || document.querySelector(`[data-carpark][onclick*="openMonthlyDetail(${carparkId})"]`);
      const carpark = card ? JSON.parse(card.dataset.carpark) : { carpark_id: carparkId, carpark_name: '' };

      const overlay = document.getElementById('monthly-modal-overlay');
      const body    = document.getElementById('monthly-modal-body');

      overlay.classList.remove('hidden');
      document.body.style.overflow = 'hidden';

      // Loading state
      body.innerHTML = `<div class="animate-pulse space-y-3">
        <div class="h-48 bg-gray-100 rounded-2xl"></div>
        <div class="h-5 bg-gray-200 rounded-lg w-3/4"></div>
        <div class="h-4 bg-gray-100 rounded-lg w-1/2"></div>
      </div>`;

      const [rates, photos] = await Promise.all([
        fetchRates(carparkId),
        fetchPhotos(carparkId),
      ]);

      // Gallery
      let galleryHTML = '';
      if (photos.length) {
        window._lbPhotos = photos.map(p => p.photo_path);
        const imgs = photos.map((p, i) =>
          `<img src="${p.photo_path}" class="h-48 w-auto flex-shrink-0 object-cover rounded-xl border border-gray-100 cursor-pointer hover:opacity-90 transition" onclick="openLightbox(window._lbPhotos,${i})">`
        ).join('');
        galleryHTML = `<div class="flex gap-2 overflow-x-auto pb-1 -mx-5 px-5 snap-x snap-mandatory">${imgs}</div>`;
      } else {
        galleryHTML = `<img src="/images/default-carpark-image.png" class="w-full h-48 object-cover rounded-2xl border border-gray-100">`;
      }

      // Rates
      let ratesHTML = '';
      const monthly = rates[0];
      ratesHTML = monthly
        ? `<div class="flex justify-between text-sm py-2">
             <span class="text-gray-600">Monthly subscription</span>
             <span class="font-bold text-gray-900">£${(monthly.price / 100).toFixed(2)}/mo</span>
           </div>`
        : `<p class="text-sm text-gray-400">No pricing set.</p>`;

      const badges   = typeof carparkBadges === 'function' ? carparkBadges(carpark, true) : '';
      const features = typeof featureTags   === 'function' ? featureTags(carpark.carpark_features) : '';

      body.innerHTML = `
        ${galleryHTML}
        <div>
          <h2 class="text-xl font-bold text-gray-900 leading-tight mb-1">${carpark.carpark_name || ''}</h2>
          <p class="text-xs text-gray-400 mb-2">${carpark.carpark_address || ''}</p>
          ${carpark.carpark_description ? `<p class="text-sm text-gray-600 leading-relaxed">${carpark.carpark_description}</p>` : ''}
        </div>
        ${badges   ? `<div class="flex flex-wrap gap-1.5">${badges}</div>`   : ''}
        ${features ? `<div class="flex flex-wrap gap-1.5">${features}</div>` : ''}
        ${carpark.time_restrictions ? `
        <div class="flex gap-2.5 bg-amber-50 border border-amber-100 rounded-2xl px-3.5 py-3">
          <i class="fa-solid fa-clock text-amber-500 mt-0.5 flex-shrink-0"></i>
          <div>
            <p class="text-xs font-bold text-amber-700 mb-0.5">Time restrictions</p>
            <p class="text-xs text-amber-600 leading-relaxed">${carpark.time_restrictions}</p>
          </div>
        </div>` : ''}
        <div>
          <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pricing</p>
          <div class="bg-gray-50 rounded-2xl px-3 divide-y divide-gray-100">${ratesHTML}</div>
        </div>
        <a href="/book.php?carpark_id=${carparkId}"
           class="block w-full text-center bg-[#6ae6fc] hover:bg-cyan-400 active:scale-[0.98] text-gray-900 font-bold py-3.5 rounded-2xl transition-all shadow-sm">
          Book Now
        </a>
      `;
    }

    function closeMonthlyModal() {
      document.getElementById('monthly-modal-overlay').classList.add('hidden');
      document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeMonthlyModal();
    });
  </script>

</body>

</html>