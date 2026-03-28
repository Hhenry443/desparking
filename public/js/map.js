document.addEventListener("DOMContentLoaded", mapboxSetup);

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
    // If we arrived from the homepage search form, pre-fill and auto-search
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

// ─── Markers ─────────────────────────────────────────────────────────────────

function renderMarkers(carparks) {
  currentCarparks = carparks;

  activeMarkers.forEach((m) => m.remove());
  activeMarkers = [];

  carparks.forEach((carpark) => {
    const marker = new mapboxgl.Marker({ color: "#0a1a44" })
      .setLngLat([carpark.carpark_lng, carpark.carpark_lat])
      .addTo(map);

    marker.getElement().style.cursor = "pointer";
    marker.getElement().addEventListener("click", () => {
      toggleBookingForm(carpark.carpark_id);
    });

    activeMarkers.push(marker);
  });
}

// ─── Info panel close ────────────────────────────────────────────────────────

function closeInfoPanel() {
  const el = document.getElementById("carpark-information-container");
  el.classList.remove("panel-open");
  // Clear content after transition finishes
  setTimeout(() => {
    if (!el.classList.contains("panel-open")) el.innerHTML = "";
  }, 320);
}

// ─── Search ──────────────────────────────────────────────────────────────────

async function searchCarparks() {
  const ids = [
    "search-location",
    "search-from-date",
    "search-from-time",
    "search-until-date",
    "search-until-time",
    "search-radius",
  ];
  for (const id of ids) {
    if (!document.getElementById(id)) {
      console.error(`searchCarparks: element #${id} not found in DOM`);
      alert(
        `Search form error: missing element #${id}. Please reload the page.`,
      );
      return;
    }
  }

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

  // Build ISO datetime strings expected by the API: "YYYY-MM-DD HH:MM:SS"
  const startISO = `${fromDate} ${fromTime}:00`;
  const endISO = `${untilDate} ${untilTime}:00`;

  // Geocode the location via Mapbox
  let lng, lat;
  try {
    const geoRes = await fetch(
      `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(location)}.json?access_token=${MAPBOX_TOKEN}`,
    );
    const geoData = await geoRes.json();

    if (!geoData.features || geoData.features.length === 0) {
      alert("Location not found. Please try a different search.");
      return;
    }

    [lng, lat] = geoData.features[0].center;
  } catch (err) {
    console.error("Geocoding error:", err);
    alert("Could not reach the mapping service. Please try again.");
    return;
  }

  // Search available carparks
  try {
    const params = new URLSearchParams({
      id: "searchCarparks",
      lat,
      lng,
      radius,
      startTime: startISO,
      endTime: endISO,
    });

    const res = await fetch(`/php/api/index.php?${params.toString()}`);
    const json = await res.json();

    if (!json.data || !Array.isArray(json.data)) {
      console.error("Search API error:", json);
      alert("No available car parks found for those times.");
      return;
    }

    renderMarkers(json.data);
    map.flyTo({ center: [lng, lat], zoom: 13 });
  } catch (err) {
    console.error("Search error:", err);
    alert("Something went wrong. Please try again.");
  }
}
