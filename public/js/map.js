document.addEventListener("DOMContentLoaded", () => {
  mapboxSetup();
  setupLocationAutocomplete();
  setupDatePickers();
});

let map;
let activeMarkers = [];
let currentCarparks = [];
let currentView = "map";
let currentBookingType = "hourly";

// ─── Map init ────────────────────────────────────────────────────────────────

function mapboxSetup() {
  mapboxgl.accessToken = MAPBOX_TOKEN;

  map = new mapboxgl.Map({
    container: "map",
    style: "mapbox://styles/mapbox/streets-v12",
    center: [1.2979, 52.6293],
    zoom: 9,
  });

  map.on("load", () => {
    const p = new URLSearchParams(window.location.search);
    const location = p.get("location");
    const bookingType = p.get("booking_type") || "hourly";
    currentBookingType = bookingType;
    setMapBookingType(bookingType);

    if (bookingType === "monthly" && location) {
      document.getElementById("search-location").value = location;
      const pad = (n) => String(n).padStart(2, "0");
      const fmt = (d) =>
        `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
      const entryDate = p.get("entry_date");
      const startDt = entryDate ? new Date(entryDate + "T00:00:00") : new Date();
      const endDt = new Date(startDt);
      endDt.setMonth(endDt.getMonth() + 1);
      if (window._mapPickerFrom) window._mapPickerFrom.select(fmt(startDt));
      if (window._mapPickerUntil) window._mapPickerUntil.select(fmt(endDt));
      if (window._mapPickerFromTime)
        window._mapPickerFromTime.setValue("00:00");
      if (window._mapPickerUntilTime)
        window._mapPickerUntilTime.setValue("00:00");
      searchCarparks();
    } else {
      const entryDate = p.get("entry_date");
      const entryTime = p.get("entry_time");
      const exitDate = p.get("exit_date");
      const exitTime = p.get("exit_time");
      const radius = p.get("radius") ?? 5;

      if (location && entryDate && entryTime && exitDate && exitTime) {
        document.getElementById("search-location").value = location;
        if (window._mapPickerFromTime)
          window._mapPickerFromTime.setValue(entryTime);
        if (window._mapPickerUntilTime)
          window._mapPickerUntilTime.setValue(exitTime);
        document.getElementById("search-radius").value = radius;
        if (window._mapPickerFrom) window._mapPickerFrom.select(entryDate);
        if (window._mapPickerUntil) window._mapPickerUntil.select(exitDate);
        searchCarparks();
      }
    }
  });
}

// ─── Location autocomplete ───────────────────────────────────────────────────

function setupLocationAutocomplete() {
  const input = document.getElementById("search-location");
  const results = document.getElementById("location-results");
  if (!input || !results) return;

  let debounceTimer;

  input.addEventListener("input", () => {
    // Clear stored coords when user edits the text
    document.getElementById("search-lat").value = "";
    document.getElementById("search-lng").value = "";

    clearTimeout(debounceTimer);
    const q = input.value.trim();
    if (q.length < 3) {
      results.classList.add("hidden");
      return;
    }
    debounceTimer = setTimeout(() => fetchLocationSuggestions(q), 280);
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (
      !e.target.closest("#search-location") &&
      !e.target.closest("#location-results")
    ) {
      results.classList.add("hidden");
    }
  });

  // Allow Enter key to trigger search directly
  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      results.classList.add("hidden");
      searchCarparks();
    }
  });

  // Use my location button
  const geoBtn = document.getElementById("map-geolocate");
  if (geoBtn) {
    geoBtn.addEventListener("click", useMyLocation);
  }
}

// ─── Date pickers ────────────────────────────────────────────────────────────

function setupDatePickers() {
  const pad = (n) => String(n).padStart(2, "0");
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const tomorrow = new Date(today);
  tomorrow.setDate(today.getDate() + 1);
  const fmtDate = (d) =>
    `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  const now = new Date();
  const roundedMins = now.getMinutes() <= 30 ? 30 : 0;
  const fromTime = new Date(now);
  fromTime.setMinutes(roundedMins, 0, 0);
  if (now.getMinutes() > 30) fromTime.setHours(fromTime.getHours() + 1);
  const untilTime = new Date(fromTime.getTime() + 60 * 60 * 1000);
  const fmtTime = (d) => `${pad(d.getHours())}:${pad(d.getMinutes())}`;

  // Read saved state to use as defaults
  let saved = null;
  try {
    saved = JSON.parse(localStorage.getItem("desparking_map_search") || "null");
  } catch {}

  window._mapPickerFrom = makeDatePicker(
    "map-from-trigger",
    "map-from-label",
    "search-from-date",
  );
  window._mapPickerUntil = makeDatePicker(
    "map-until-trigger",
    "map-until-label",
    "search-until-date",
  );
  if (window._mapPickerFrom)
    window._mapPickerFrom.select(saved?.fromDate || fmtDate(today));
  if (window._mapPickerUntil)
    window._mapPickerUntil.select(saved?.untilDate || fmtDate(tomorrow));

  window._mapPickerFromTime = makeTimePicker(
    "map-from-time-btn",
    "map-from-time-label",
    "search-from-time",
  );
  window._mapPickerUntilTime = makeTimePicker(
    "map-until-time-btn",
    "map-until-time-label",
    "search-until-time",
  );
  if (window._mapPickerFromTime)
    window._mapPickerFromTime.setValue(saved?.fromTime || fmtTime(fromTime));
  if (window._mapPickerUntilTime)
    window._mapPickerUntilTime.setValue(saved?.untilTime || fmtTime(untilTime));
}

// ─── Geolocation ─────────────────────────────────────────────────────────────

function useMyLocation() {
  if (!navigator.geolocation) return;
  const btn = document.getElementById("map-geolocate");
  const input = document.getElementById("search-location");
  if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  navigator.geolocation.getCurrentPosition(
    async (pos) => {
      const { latitude: lat, longitude: lng } = pos.coords;
      document.getElementById("search-lat").value = lat;
      document.getElementById("search-lng").value = lng;

      try {
        const res = await fetch(
          `https://api.mapbox.com/search/geocode/v6/reverse?longitude=${lng}&latitude=${lat}&access_token=${MAPBOX_TOKEN}`,
        );
        const data = await res.json();
        const feature = data.features && data.features[0];
        input.value = feature
          ? feature.properties.full_address || feature.properties.name
          : `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
      } catch {
        input.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
      }

      if (btn)
        btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
    },
    () => {
      if (btn)
        btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
      alert(
        "Could not get your location. Please check your browser permissions.",
      );
    },
    { timeout: 10000 },
  );
}

async function fetchLocationSuggestions(query) {
  const results = document.getElementById("location-results");
  try {
    const res = await fetch(
      `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${MAPBOX_TOKEN}&limit=5`,
    );
    const data = await res.json();

    if (!data.features || !data.features.length) {
      results.innerHTML = `<div class="p-3 text-gray-500 text-sm">No results found</div>`;
      results.classList.remove("hidden");
      return;
    }

    results.innerHTML = data.features
      .map(
        (f) => `
      <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition border-b border-gray-100 last:border-0"
           onclick='selectSearchLocation(${JSON.stringify(f)})'>
        <p class="text-sm font-semibold text-gray-800">${f.text}</p>
        <p class="text-xs text-gray-500">${f.place_name}</p>
      </div>
    `,
      )
      .join("");
    results.classList.remove("hidden");
  } catch {
    results.classList.add("hidden");
  }
}

function selectSearchLocation(feature) {
  const [lng, lat] = feature.center;
  document.getElementById("search-location").value = feature.place_name;
  document.getElementById("search-lat").value = lat;
  document.getElementById("search-lng").value = lng;
  document.getElementById("location-results").classList.add("hidden");
}

// ─── Markers ─────────────────────────────────────────────────────────────────

function renderMarkers(carparks) {
  activeMarkers.forEach((m) => m.remove());
  activeMarkers = [];

  carparks.forEach((carpark) => {
    const el = document.createElement("div");
    el.style.cssText = `
      background: #060745; color: #fff; font-size: 11px; font-weight: 700;
      padding: 4px 8px; border-radius: 999px; white-space: nowrap;
      cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.3);
      border: 2px solid #fff;
    `;

    let label = "–";
    if (parseInt(carpark.is_monthly) && carpark.monthly_price != null) {
      label = `£${(carpark.monthly_price / 100).toFixed(2)}/mo`;
    } else if (carpark.min_price != null) {
      label = `from £${(carpark.min_price / 100).toFixed(2)}`;
    }
    el.textContent = label;

    // Desktop: click works fine
    el.addEventListener("click", () => showCarparkDetail(carpark.carpark_id));

    // Mobile: Mapbox swallows touch events before click fires on custom elements.
    // Track touchstart position and only treat touchend as a tap if the finger
    // didn't move significantly (i.e. it's not a map drag).
    let _touchStart = null;
    el.addEventListener("touchstart", (e) => {
      e.stopPropagation();
      _touchStart = { x: e.touches[0].clientX, y: e.touches[0].clientY };
    });
    el.addEventListener("touchend", (e) => {
      if (!_touchStart) return;
      const dx = Math.abs(e.changedTouches[0].clientX - _touchStart.x);
      const dy = Math.abs(e.changedTouches[0].clientY - _touchStart.y);
      _touchStart = null;
      if (dx < 8 && dy < 8) {
        e.stopPropagation();
        showCarparkDetail(carpark.carpark_id);
      }
    });

    const marker = new mapboxgl.Marker({ element: el })
      .setLngLat([carpark.carpark_lng, carpark.carpark_lat])
      .addTo(map);

    activeMarkers.push(marker);
  });
}

// ─── Info panel close ────────────────────────────────────────────────────────

function closeInfoPanel() {
  const el = document.getElementById("carpark-information-container");
  el.classList.remove("panel-open");
  setTimeout(() => {
    if (!el.classList.contains("panel-open")) el.innerHTML = "";
  }, 420);
}

// ─── Map / List view toggle (mobile) ─────────────────────────────────────────

function showViewToggle() {
  if (window.innerWidth >= 1024) return;
  document.getElementById("view-toggle").classList.remove("hidden");
}

function setMapBookingType(type) {
  currentBookingType = type;

  const untilSection = document.getElementById("map-until-section");
  const fromTimeSep = document.getElementById("map-from-time-sep");
  const fromTimeBtn = document.getElementById("map-from-time-btn");
  const hourlyBtn = document.getElementById("map-toggle-hourly");
  const monthlyBtn = document.getElementById("map-toggle-monthly");

  const active =
    "flex-1 py-1.5 rounded-lg bg-[#6ae6fc] text-gray-800 text-xs font-bold transition-all whitespace-nowrap";
  const inactive =
    "flex-1 py-1.5 rounded-lg text-gray-600 text-xs font-semibold transition-all whitespace-nowrap hover:bg-white/50";

  if (type === "monthly") {
    if (untilSection) untilSection.classList.add("hidden");
    if (fromTimeSep) fromTimeSep.classList.add("hidden");
    if (fromTimeBtn) fromTimeBtn.classList.add("hidden");
    if (hourlyBtn) hourlyBtn.className = inactive;
    if (monthlyBtn) monthlyBtn.className = active;
  } else {
    if (untilSection) untilSection.classList.remove("hidden");
    if (fromTimeSep) fromTimeSep.classList.remove("hidden");
    if (fromTimeBtn) fromTimeBtn.classList.remove("hidden");
    if (hourlyBtn) hourlyBtn.className = active;
    if (monthlyBtn) monthlyBtn.className = inactive;
  }

  // Re-search if results are already showing
  if (currentCarparks.length || document.getElementById("search-lat").value) {
    searchCarparks();
  }
}

function backToResults() {
  if (window.innerWidth < 1024) {
    closeInfoPanel();
    setView("list");
  } else {
    renderResultsList(currentCarparks);
  }
}

function setView(view) {
  currentView = view;
  const listView = document.getElementById("list-view");
  const mapBtn = document.getElementById("toggle-map-btn");
  const listBtn = document.getElementById("toggle-list-btn");

  const active =
    "flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold transition-all bg-gray-100 text-[#060745]";
  const inactive =
    "flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold transition-all text-white/70";

  if (view === "list") {
    listView.classList.remove("hidden");
    closeInfoPanel();
    if (mapBtn) mapBtn.className = inactive;
    if (listBtn) listBtn.className = active;
  } else {
    listView.classList.add("hidden");
    if (mapBtn) mapBtn.className = active;
    if (listBtn) listBtn.className = inactive;
    if (currentCarparks.length) renderResultsList(currentCarparks);
  }
}

// ─── Mobile search minimize / expand ─────────────────────────────────────────

function minimizeMobileSearch() {
  if (window.innerWidth >= 1024) return;

  const location = document.getElementById("search-location").value;
  const fromDate = document.getElementById("search-from-date").value;
  const fromTime = document.getElementById("search-from-time").value;
  const untilDate = document.getElementById("search-until-date").value;
  const untilTime = document.getElementById("search-until-time").value;

  const fmt = (d) => {
    const parts = d.split("-");
    if (parts.length !== 3) return d;
    const date = new Date(
      parseInt(parts[0]),
      parseInt(parts[1]) - 1,
      parseInt(parts[2]),
    );
    return date.toLocaleDateString("en-GB", { day: "numeric", month: "short" });
  };

  document.getElementById("pill-location").textContent = location;
  document.getElementById("pill-dates").textContent =
    `${fmt(fromDate)} ${fromTime} → ${fmt(untilDate)} ${untilTime}`;

  document.getElementById("search-bar").style.display = "none";
  document.getElementById("search-pill").classList.remove("hidden");
}

function expandMobileSearch() {
  document.getElementById("search-bar").style.display = "";
  document.getElementById("search-pill").classList.add("hidden");
}

// ─── Search ──────────────────────────────────────────────────────────────────

async function searchCarparks() {
  const location = document.getElementById("search-location").value.trim();
  const fromDate = document.getElementById("search-from-date").value;
  const fromTime = document.getElementById("search-from-time").value;
  const untilDate = document.getElementById("search-until-date").value;
  const untilTime = document.getElementById("search-until-time").value;
  const radius = document.getElementById("search-radius").value || 15;

  const isMonthly = currentBookingType === "monthly";

  if (
    !location ||
    !fromDate ||
    (!isMonthly && (!fromTime || !untilDate || !untilTime))
  ) {
    alert("Please fill in all fields before searching.");
    return;
  }

  let startISO, endISO;
  if (isMonthly) {
    startISO = `${fromDate} 00:00:00`;
    const endDt = new Date(fromDate);
    endDt.setMonth(endDt.getMonth() + 1);
    const pad = (n) => String(n).padStart(2, "0");
    endISO = `${endDt.getFullYear()}-${pad(endDt.getMonth() + 1)}-${pad(endDt.getDate())} 00:00:00`;
  } else {
    startISO = `${fromDate} ${fromTime}:00`;
    endISO = `${untilDate} ${untilTime}:00`;
  }

  // Use coords stored by autocomplete selection; fall back to geocoding if user typed manually
  let lat = document.getElementById("search-lat").value;
  let lng = document.getElementById("search-lng").value;

  if (!lat || !lng) {
    try {
      const geoRes = await fetch(
        `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(location)}.json?access_token=${MAPBOX_TOKEN}`,
      );
      const geoData = await geoRes.json();
      if (!geoData.features || !geoData.features.length) {
        alert("Location not found. Please try a different search.");
        return;
      }
      [lng, lat] = geoData.features[0].center;
    } catch {
      alert("Could not reach the mapping service. Please try again.");
      return;
    }
  }

  try {
    const params = new URLSearchParams({
      id: "searchCarparks",
      lat,
      lng,
      radius,
      startTime: startISO,
      endTime: endISO,
      booking_type: currentBookingType,
    });
    const res = await fetch(`/php/api/index.php?${params}`);
    const json = await res.json();

    currentCarparks = Array.isArray(json.data) ? json.data : [];
    renderMarkers(currentCarparks);
    renderResultsList(currentCarparks);
    map.flyTo({ center: [lng, lat], zoom: 13 });
    minimizeMobileSearch();
    showViewToggle();

    // Persist search so it survives page navigation
    try {
      localStorage.setItem(
        "desparking_map_search",
        JSON.stringify({
          location: document.getElementById("search-location").value,
          lat: document.getElementById("search-lat").value,
          lng: document.getElementById("search-lng").value,
          fromDate: document.getElementById("search-from-date").value,
          fromTime: document.getElementById("search-from-time").value,
          untilDate: document.getElementById("search-until-date").value,
          untilTime: document.getElementById("search-until-time").value,
        }),
      );
    } catch {}
  } catch {
    alert("Something went wrong. Please try again.");
  }
}
