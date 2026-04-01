// ─── Helpers ─────────────────────────────────────────────────────────────────

function badge(text, colourClass) {
  return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${colourClass}">${text}</span>`;
}

function carparkBadges(carpark, large = false) {
  const sz = large ? "text-xs" : "text-xs";
  const badges = [];

  if (carpark.space_size) {
    const label =
      { small: "Small space", medium: "Medium space", large: "Large space" }[
        carpark.space_size
      ] || carpark.space_size;
    badges.push(
      badge(label, "bg-blue-50 text-blue-700 border border-blue-100"),
    );
  }
  if (!parseInt(carpark.weekend_available)) {
    badges.push(
      badge(
        "Weekdays only",
        "bg-amber-50 text-amber-700 border border-amber-100",
      ),
    );
  }
  if (parseInt(carpark.requires_key)) {
    badges.push(
      badge(
        "Key required",
        "bg-purple-50 text-purple-700 border border-purple-100",
      ),
    );
  }
  if (parseInt(carpark.min_booking_minutes) > 0) {
    badges.push(
      badge(
        `Min. ${carpark.min_booking_minutes} min`,
        "bg-gray-100 text-gray-600 border border-gray-200",
      ),
    );
  }
  return badges.join(" ");
}

function featureTags(featuresStr) {
  if (!featuresStr) return "";
  return featuresStr
    .split(",")
    .map((f) => f.trim())
    .filter(Boolean)
    .map(
      (f) =>
        `<span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full border border-gray-200">${f}</span>`,
    )
    .join("");
}

async function fetchRates(carparkId) {
  try {
    const res = await fetch(
      `/php/api/index.php?id=getCarparkRates&carpark_id=${carparkId}`,
    );
    const data = await res.json();
    return data.success && Array.isArray(data.rates) ? data.rates : [];
  } catch {
    return [];
  }
}

async function fetchPhotos(carparkId) {
  try {
    const res = await fetch(
      `/php/api/index.php?id=getCarparkPhotos&carpark_id=${carparkId}`,
    );
    const data = await res.json();
    return Array.isArray(data.data) ? data.data : [];
  } catch {
    return [];
  }
}

function panelShell(inner) {
  return `<div class="relative h-full w-full bg-white flex flex-col overflow-hidden">${inner}</div>`;
}

// ─── Results list ─────────────────────────────────────────────────────────────

function renderResultsList(carparks) {
  const panel    = document.getElementById("carpark-information-container");
  const isMobile = window.innerWidth < 1024;

  if (!carparks.length) {
    if (!isMobile) {
      panel.innerHTML = panelShell(`
        <div class="flex items-center justify-between px-4 pt-4 pb-3 flex-shrink-0 border-b border-gray-100">
          <span class="font-bold text-gray-900">No results</span>
          <button onclick="closeInfoPanel()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition">
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>
        <div class="flex-1 flex flex-col items-center justify-center gap-3 p-6 text-center">
          <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
            <i class="fa-solid fa-magnifying-glass text-xl text-gray-400"></i>
          </div>
          <p class="font-semibold text-gray-700">No car parks found</p>
          <p class="text-sm text-gray-400">Try adjusting your times or expanding the radius.</p>
        </div>
      `);
      panel.classList.add("panel-open");
    }
    const listView = document.getElementById('list-view');
    if (listView) {
      listView.innerHTML = `
        <div class="pt-32 flex flex-col items-center justify-center gap-3 p-6 text-center min-h-full" style="min-height:100vh">
          <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
            <i class="fa-solid fa-magnifying-glass text-xl text-gray-400"></i>
          </div>
          <p class="font-semibold text-gray-700">No car parks found</p>
          <p class="text-sm text-gray-400">Try adjusting your times or expanding the radius.</p>
        </div>`;
    }
    return;
  }

  const cards = carparks
    .map((c) => {
      const dist = c.distance
        ? `${parseFloat(c.distance).toFixed(1)} km away`
        : "";
      const spaces = parseInt(c.spaces_left) || 0;
      const spacesTag =
        spaces > 0
          ? badge(
              `${spaces} left`,
              "bg-green-50 text-green-700 border border-green-100",
            )
          : badge("Full", "bg-red-50 text-red-600 border border-red-100");

      let priceHTML = "";
      if (parseInt(c.is_monthly) && c.monthly_price != null) {
        priceHTML = `<div class="text-right flex-shrink-0">
        <p class="text-sm font-bold text-[#060745]">£${(c.monthly_price / 100).toFixed(2)}</p>
        <p class="text-xs text-gray-400">/mo</p>
      </div>`;
      } else if (c.min_price != null) {
        priceHTML = `<div class="text-right flex-shrink-0">
        <p class="text-sm font-bold text-[#060745]">£${(c.min_price / 100).toFixed(2)}</p>
        <p class="text-xs text-gray-400">from</p>
      </div>`;
      }

      return `
      <div class="flex gap-3 px-4 py-3.5 hover:bg-gray-50/80 active:bg-gray-100 cursor-pointer
                  transition-colors border-b border-gray-100 last:border-0"
           onclick="showCarparkDetail('${c.carpark_id}')">
        <img src="/images/default-carpark-image.png"
             class="w-14 h-14 object-cover rounded-2xl flex-shrink-0 shadow-sm border border-gray-100">
        <div class="flex-1 min-w-0">
          <div class="flex items-start gap-2 mb-1">
            <div class="flex-1 min-w-0">
              <p class="font-bold text-sm text-gray-900 truncate leading-tight">${c.carpark_name}</p>
              <p class="text-xs text-gray-400 truncate mt-0.5">${c.carpark_address || ""}</p>
            </div>
            ${priceHTML}
          </div>
          <div class="flex flex-wrap gap-1">
            ${spacesTag}
            ${dist ? `<span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">${dist}</span>` : ""}
            ${carparkBadges(c)}
          </div>
        </div>
        <div class="flex items-center flex-shrink-0 self-center">
          <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
        </div>
      </div>`;
    })
    .join("");

  if (!isMobile) {
    panel.innerHTML = panelShell(`
      <div class="flex items-center justify-between px-4 pt-4 pb-3 flex-shrink-0 border-b border-gray-100">
        <div>
          <p class="font-bold text-gray-900 text-base">${carparks.length} <span class="text-[#060745]">found</span></p>
          <p class="text-xs text-gray-400 mt-0.5">Click a car park for details</p>
        </div>
        <button onclick="closeInfoPanel()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition">
          <i class="fa-solid fa-xmark text-sm"></i>
        </button>
      </div>
      <div class="flex-1 overflow-y-auto overscroll-contain">${cards}</div>
    `);
    panel.classList.add("panel-open");
  }

  const listView = document.getElementById('list-view');
  if (listView) {
    listView.innerHTML = `
      <div class="flex flex-col min-h-full pt-32 pb-24">
        <div class="bg-white flex-1">
          <div class="px-4 py-3 border-b border-gray-100">
            <p class="font-bold text-gray-900 text-base">${carparks.length} <span class="text-[#060745]">found</span></p>
            <p class="text-xs text-gray-400 mt-0.5">Tap a car park for details</p>
          </div>
          <div class="divide-y divide-gray-100">${cards}</div>
        </div>
      </div>`;
  }
}

// ─── Detail view ──────────────────────────────────────────────────────────────

async function showCarparkDetail(carparkId) {
  console.log("hyuihhujbhjuh");
  carparkId = String(carparkId);
  const carpark = currentCarparks.find(
    (c) => String(c.carpark_id) === carparkId,
  );
  if (!carpark) return;

  const panel = document.getElementById("carpark-information-container");

  panel.innerHTML = panelShell(`
    <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-gray-100 flex-shrink-0">
      <button onclick="backToResults()"
        class="flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
        <i class="fa-solid fa-chevron-left text-xs"></i> Results
      </button>
      <button onclick="closeInfoPanel()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>
    <div class="flex-1 overflow-y-auto overscroll-contain p-4">
      <div class="animate-pulse space-y-3">
        <div class="h-44 bg-gray-100 rounded-2xl"></div>
        <div class="h-5 bg-gray-200 rounded-lg w-3/4"></div>
        <div class="h-4 bg-gray-100 rounded-lg w-1/2"></div>
        <div class="h-4 bg-gray-100 rounded-lg w-2/3"></div>
      </div>
    </div>
  `);

  panel.classList.add("panel-open");

  // Fetch in parallel
  const [rates, photos] = await Promise.all([
    fetchRates(carparkId),
    fetchPhotos(carparkId),
  ]);

  // ── Photo gallery ──
  let galleryHTML = "";
  if (photos.length) {
    const imgs = photos
      .map(
        (p) =>
          `<img src="${p.photo_path}" class="h-48 w-auto flex-shrink-0 object-cover rounded-xl border border-gray-100" alt="Car park photo">`,
      )
      .join("");
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
    ratesHTML = rates
      .map(
        (r) =>
          `<div class="flex justify-between text-sm py-2 border-b border-gray-50 last:border-0">
         <span class="text-gray-600">${r.duration_minutes} minute${r.duration_minutes != 1 ? "s" : ""}</span>
         <span class="font-bold text-gray-900">£${(r.price / 100).toFixed(2)}</span>
       </div>`,
      )
      .join("");
  } else {
    ratesHTML = `<p class="text-sm text-gray-400">No pricing information available.</p>`;
  }

  // ── Stats row ──
  const dist = carpark.distance
    ? `${parseFloat(carpark.distance).toFixed(1)} km`
    : null;
  const spaces = parseInt(carpark.spaces_left) || 0;
  const stats = [
    dist
      ? `<div class="text-center"><p class="text-lg font-bold text-gray-900">${dist}</p><p class="text-xs text-gray-500">Distance</p></div>`
      : "",
    `<div class="text-center"><p class="text-lg font-bold ${spaces > 0 ? "text-green-600" : "text-red-500"}">${spaces}</p><p class="text-xs text-gray-500">Spaces left</p></div>`,
    `<div class="text-center"><p class="text-lg font-bold text-gray-900">${carpark.carpark_capacity}</p><p class="text-xs text-gray-500">Capacity</p></div>`,
  ]
    .filter(Boolean)
    .join("");

  // ── Action button ──
  const actionBtn =
    carpark.carpark_type === "affiliate"
      ? `<a href="${carpark.carpark_affiliate_url}" target="_blank"
          class="block w-full text-center bg-[#060745] hover:bg-[#0a1a6e] active:scale-[0.98] text-white font-bold py-3.5 rounded-2xl transition-all shadow-sm">
          Visit Partner Site
       </a>`
      : `<a href="/book.php?carpark_id=${carparkId}"
          class="block w-full text-center bg-[#6ae6fc] hover:bg-cyan-400 active:scale-[0.98] text-gray-900 font-bold py-3.5 rounded-2xl transition-all shadow-sm">
          Book Now
       </a>`;

  const badges = carparkBadges(carpark, true);
  const features = featureTags(carpark.carpark_features);

  panel.innerHTML = panelShell(`
    <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-gray-100 flex-shrink-0">
      <button onclick="backToResults()"
        class="flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
        <i class="fa-solid fa-chevron-left text-xs"></i> Results
      </button>
      <button onclick="closeInfoPanel()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto overscroll-contain">
      <div class="p-4 space-y-4">

        ${galleryHTML}

        <div>
          <h2 class="text-xl font-bold text-gray-900 leading-tight mb-1">${carpark.carpark_name}</h2>
          <p class="text-xs text-gray-400 mb-2">${carpark.carpark_address || ""}</p>
          ${
            carpark.carpark_description
              ? `<p class="text-sm text-gray-600 leading-relaxed">${carpark.carpark_description}</p>`
              : ""
          }
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-2 bg-gray-50 rounded-2xl p-3">
          ${stats}
        </div>

        <!-- Restriction / attribute badges -->
        ${badges ? `<div class="flex flex-wrap gap-1.5">${badges}</div>` : ""}

        <!-- Feature tags -->
        ${features ? `<div class="flex flex-wrap gap-1.5">${features}</div>` : ""}

        <!-- Pricing -->
        <div>
          <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pricing</p>
          <div class="bg-gray-50 rounded-2xl px-3 divide-y divide-gray-100">
            ${ratesHTML}
          </div>
        </div>

        ${actionBtn}

      </div>
    </div>
  `);

  panel.classList.add("panel-open");
}

// Keep backward-compat alias so any old calls still work
function toggleBookingForm(carparkId) {
  showCarparkDetail(carparkId);
}
