// ─── Helpers ─────────────────────────────────────────────────────────────────

function badge(text, colourClass) {
  return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${colourClass}">${text}</span>`;
}

function carparkBadges(carpark, large = false) {
  const sz = large ? "text-xs" : "text-xs";
  const badges = [];

  if (carpark.space_size) {
    const label = { small: "Small space", medium: "Medium space", large: "Large space" }[carpark.space_size] || carpark.space_size;
    badges.push(badge(label, "bg-blue-50 text-blue-700 border border-blue-100"));
  }
  if (!parseInt(carpark.weekend_available)) {
    badges.push(badge("Weekdays only", "bg-amber-50 text-amber-700 border border-amber-100"));
  }
  if (parseInt(carpark.requires_key)) {
    badges.push(badge("Key required", "bg-purple-50 text-purple-700 border border-purple-100"));
  }
  if (parseInt(carpark.min_booking_minutes) > 0) {
    badges.push(badge(`Min. ${carpark.min_booking_minutes} min`, "bg-gray-100 text-gray-600 border border-gray-200"));
  }
  return badges.join(" ");
}

function featureTags(featuresStr) {
  if (!featuresStr) return "";
  return featuresStr.split(",")
    .map(f => f.trim()).filter(Boolean)
    .map(f => `<span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full border border-gray-200">${f}</span>`)
    .join("");
}

async function fetchRates(carparkId) {
  try {
    const res  = await fetch(`/php/api/index.php?id=getCarparkRates&carpark_id=${carparkId}`);
    const data = await res.json();
    return (data.success && Array.isArray(data.rates)) ? data.rates : [];
  } catch { return []; }
}

async function fetchPhotos(carparkId) {
  try {
    const res  = await fetch(`/php/api/index.php?id=getCarparkPhotos&carpark_id=${carparkId}`);
    const data = await res.json();
    return Array.isArray(data.data) ? data.data : [];
  } catch { return []; }
}

function panelShell(inner) {
  return `<div class="relative h-full w-full bg-white flex flex-col overflow-hidden shadow-xl">${inner}</div>`;
}

// ─── Results list ─────────────────────────────────────────────────────────────

function renderResultsList(carparks) {
  const panel = document.getElementById("carpark-information-container");

  // Drag handle for mobile
  const handle = `
    <div class="lg:hidden flex justify-center pt-3 pb-1 flex-shrink-0">
      <div class="w-10 h-1 rounded-full bg-gray-300"></div>
    </div>`;

  if (!carparks.length) {
    panel.innerHTML = panelShell(`
      ${handle}
      <div class="flex items-center justify-between px-4 py-3 border-b flex-shrink-0">
        <span class="font-bold text-gray-900 text-sm">No results</span>
        <button onclick="closeInfoPanel()" class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 text-lg transition">×</button>
      </div>
      <div class="flex-1 flex flex-col items-center justify-center gap-3 p-6 text-center">
        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-2xl">🔍</div>
        <p class="font-semibold text-gray-700">No car parks found</p>
        <p class="text-sm text-gray-500">Try adjusting your search times or expanding the radius.</p>
      </div>
    `);
    panel.classList.add("panel-open");
    return;
  }

  const cards = carparks.map((c) => {
    const dist      = c.distance ? `${parseFloat(c.distance).toFixed(1)} km` : "";
    const spaces    = parseInt(c.spaces_left) || 0;
    const spacesTag = spaces > 0
      ? badge(`${spaces} space${spaces !== 1 ? "s" : ""} left`, "bg-green-50 text-green-700 border border-green-100")
      : badge("Full", "bg-red-50 text-red-600 border border-red-100");

    return `
      <div class="flex gap-3 p-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-100 last:border-0"
           onclick="showCarparkDetail('${c.carpark_id}')">
        <img src="/images/default-carpark-image.png"
             class="w-16 h-16 object-cover rounded-xl flex-shrink-0 border border-gray-100">
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-1 mb-0.5">
            <p class="font-bold text-sm text-gray-900 truncate leading-tight">${c.carpark_name}</p>
            ${dist ? `<span class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">${dist}</span>` : ""}
          </div>
          <p class="text-xs text-gray-500 truncate mb-1.5">${c.carpark_address || ""}</p>
          <div class="flex flex-wrap gap-1">${spacesTag} ${carparkBadges(c)}</div>
        </div>
      </div>`;
  }).join("");

  panel.innerHTML = panelShell(`
    ${handle}
    <div class="flex items-center justify-between px-4 py-3 border-b flex-shrink-0">
      <span class="font-bold text-gray-900 text-sm">${carparks.length} car park${carparks.length !== 1 ? "s" : ""} found</span>
      <button onclick="closeInfoPanel()" class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 text-lg transition">×</button>
    </div>
    <div class="flex-1 overflow-y-auto">${cards}</div>
  `);

  panel.classList.add("panel-open");
}

// ─── Detail view ──────────────────────────────────────────────────────────────

async function showCarparkDetail(carparkId) {
  carparkId = String(carparkId);
  const carpark = currentCarparks.find((c) => String(c.carpark_id) === carparkId);
  if (!carpark) return;

  const panel = document.getElementById("carpark-information-container");

  // Show skeleton while loading
  const handle = `
    <div class="lg:hidden flex justify-center pt-3 pb-1 flex-shrink-0">
      <div class="w-10 h-1 rounded-full bg-gray-300"></div>
    </div>`;

  panel.innerHTML = panelShell(`
    ${handle}
    <div class="flex items-center justify-between px-4 py-3 border-b flex-shrink-0">
      <button onclick="renderResultsList(currentCarparks)"
        class="flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900 transition">
        ← Results
      </button>
      <button onclick="closeInfoPanel()" class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 text-lg transition">×</button>
    </div>
    <div class="flex-1 overflow-y-auto p-4">
      <div class="animate-pulse space-y-3">
        <div class="h-40 bg-gray-200 rounded-xl"></div>
        <div class="h-5 bg-gray-200 rounded w-3/4"></div>
        <div class="h-4 bg-gray-100 rounded w-1/2"></div>
        <div class="h-4 bg-gray-100 rounded w-2/3"></div>
      </div>
    </div>
  `);

  // Fetch in parallel
  const [rates, photos] = await Promise.all([
    fetchRates(carparkId),
    fetchPhotos(carparkId),
  ]);

  // ── Photo gallery ──
  let galleryHTML = "";
  if (photos.length) {
    const imgs = photos.map(p =>
      `<img src="${p.photo_path}" class="h-48 w-auto flex-shrink-0 object-cover rounded-xl border border-gray-100" alt="Car park photo">`
    ).join("");
    galleryHTML = `
      <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 snap-x snap-mandatory mb-4">
        ${imgs}
      </div>`;
  } else {
    galleryHTML = `
      <img src="/images/default-carpark-image.png"
           class="w-full h-44 object-cover rounded-xl mb-4 border border-gray-100">`;
  }

  // ── Rates ──
  let ratesHTML = "";
  if (carpark.is_monthly == 1) {
    const monthly = rates[0];
    ratesHTML = monthly
      ? `<div class="flex justify-between text-sm py-2">
           <span class="text-gray-600">Monthly subscription</span>
           <span class="font-bold text-gray-900">£${(monthly.price / 100).toFixed(2)}/mo</span>
         </div>`
      : `<p class="text-sm text-gray-400">No pricing set.</p>`;
  } else if (rates.length) {
    ratesHTML = rates.map(r =>
      `<div class="flex justify-between text-sm py-2 border-b border-gray-50 last:border-0">
         <span class="text-gray-600">${r.duration_minutes} minute${r.duration_minutes != 1 ? "s" : ""}</span>
         <span class="font-bold text-gray-900">£${(r.price / 100).toFixed(2)}</span>
       </div>`
    ).join("");
  } else {
    ratesHTML = `<p class="text-sm text-gray-400">No pricing information available.</p>`;
  }

  // ── Stats row ──
  const dist    = carpark.distance ? `${parseFloat(carpark.distance).toFixed(1)} km` : null;
  const spaces  = parseInt(carpark.spaces_left) || 0;
  const stats   = [
    dist    ? `<div class="text-center"><p class="text-lg font-bold text-gray-900">${dist}</p><p class="text-xs text-gray-500">Distance</p></div>` : "",
    `<div class="text-center"><p class="text-lg font-bold ${spaces > 0 ? "text-green-600" : "text-red-500"}">${spaces}</p><p class="text-xs text-gray-500">Spaces left</p></div>`,
    `<div class="text-center"><p class="text-lg font-bold text-gray-900">${carpark.carpark_capacity}</p><p class="text-xs text-gray-500">Capacity</p></div>`,
  ].filter(Boolean).join("");

  // ── Action button ──
  const actionBtn = carpark.carpark_type === "affiliate"
    ? `<a href="${carpark.carpark_affiliate_url}" target="_blank"
          class="block w-full text-center bg-[#060745] hover:bg-[#0a1a6e] text-white font-bold py-3 rounded-xl transition">
          Visit Partner Site
       </a>`
    : `<a href="/book.php?carpark_id=${carparkId}"
          class="block w-full text-center bg-[#6ae6fc] hover:bg-cyan-400 text-gray-900 font-bold py-3 rounded-xl transition shadow-sm">
          Book Now
       </a>`;

  const badges   = carparkBadges(carpark, true);
  const features = featureTags(carpark.carpark_features);

  panel.innerHTML = panelShell(`
    ${handle}
    <div class="flex items-center justify-between px-4 py-3 border-b flex-shrink-0">
      <button onclick="renderResultsList(currentCarparks)"
        class="flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900 transition">
        ← Results
      </button>
      <button onclick="closeInfoPanel()" class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 text-lg transition">×</button>
    </div>

    <div class="flex-1 overflow-y-auto">
      <div class="p-4 space-y-4">

        ${galleryHTML}

        <div>
          <h2 class="text-xl font-bold text-gray-900 leading-tight mb-1">${carpark.carpark_name}</h2>
          <p class="text-xs text-gray-500 mb-2">${carpark.carpark_address || ""}</p>
          ${carpark.carpark_description
            ? `<p class="text-sm text-gray-600">${carpark.carpark_description}</p>`
            : ""}
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-2 bg-gray-50 rounded-xl p-3">
          ${stats}
        </div>

        <!-- Restriction / attribute badges -->
        ${badges ? `<div class="flex flex-wrap gap-1.5">${badges}</div>` : ""}

        <!-- Feature tags -->
        ${features ? `<div class="flex flex-wrap gap-1.5">${features}</div>` : ""}

        <!-- Pricing -->
        <div>
          <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Pricing</p>
          <div class="bg-gray-50 rounded-xl px-3 divide-y divide-gray-100">
            ${ratesHTML}
          </div>
        </div>

        ${actionBtn}

      </div>
    </div>
  `);
}

// Keep backward-compat alias so any old calls still work
function toggleBookingForm(carparkId) {
  showCarparkDetail(carparkId);
}
