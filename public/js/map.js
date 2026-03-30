document.addEventListener("DOMContentLoaded", () => {
  mapboxSetup();
  setupLocationAutocomplete();
});

let map;
let activeMarkers = [];
let currentCarparks = [];

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
    const entryDate = p.get("entry_date");
    const entryTime = p.get("entry_time");
    const exitDate = p.get("exit_date");
    const exitTime = p.get("exit_time");
    const radius = p.get("radius") ?? 5;

    if (location && entryDate && entryTime && exitDate && exitTime) {
      document.getElementById("search-location").value = location;
      document.getElementById("search-from-date").value = entryDate;
      document.getElementById("search-from-time").value = entryTime;
      document.getElementById("search-until-date").value = exitDate;
      document.getElementById("search-until-time").value = exitTime;
      document.getElementById("search-radius").value = radius;

      searchCarparks();
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
  el.style.transform = ""; // clear any inline drag offset
  el.classList.remove("panel-open");
  setTimeout(() => {
    if (!el.classList.contains("panel-open")) el.innerHTML = "";
  }, 420);
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

  if (!location || !fromDate || !fromTime || !untilDate || !untilTime) {
    alert("Please fill in all fields before searching.");
    return;
  }

  const startISO = `${fromDate} ${fromTime}:00`;
  const endISO = `${untilDate} ${untilTime}:00`;

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
    });
    const res = await fetch(`/php/api/index.php?${params}`);
    const json = await res.json();

    currentCarparks = Array.isArray(json.data) ? json.data : [];
    renderMarkers(currentCarparks);
    renderResultsList(currentCarparks);
    map.flyTo({ center: [lng, lat], zoom: 13 });
    minimizeMobileSearch();
  } catch {
    alert("Something went wrong. Please try again.");
  }
}
