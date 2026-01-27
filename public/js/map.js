document.addEventListener("DOMContentLoaded", mapboxSetup);

let map;
let activeMarkers = [];
let currentCarparks = [];

function mapboxSetup() {
  mapboxgl.accessToken = MAPBOX_TOKEN;

  const lat = 52.6293;
  const lng = 1.2979;

  map = new mapboxgl.Map({
    container: "map",
    center: [lng, lat],
    zoom: 9,
  });

  map.on("load", () => {
    const params = new URLSearchParams(window.location.search);

    const location = params.get("location");
    const entryDate = params.get("entry_date");
    const entryTime = params.get("entry_time");
    const exitDate = params.get("exit_date");
    const exitTime = params.get("exit_time");
    const radius = params.get("radius") ?? 5;

    console.log("URL params:", Object.fromEntries(params.entries()));

    // Only auto-run if homepage sent everything
    if (location && entryDate && entryTime && exitDate && exitTime) {
      const startDateTime = `${entryDate}T${entryTime}`;
      const endDateTime = `${exitDate}T${exitTime}`;

      // Fill your map form
      const locInput = document.getElementById("search-location");
      const startInput = document.getElementById("search-start");
      const endInput = document.getElementById("search-end");
      const radInput = document.getElementById("search-radius");

      if (locInput) locInput.value = location;
      if (startInput) startInput.value = startDateTime;
      if (endInput) endInput.value = endDateTime;
      if (radInput) radInput.value = radius;

      // Fire the normal search
      searchCarparks();
    }
  });
}

/**
 * Clears existing markers and renders new ones
 */
function renderMarkers(carparks) {
  if (carparks) {
    currentCarparks = carparks;
  }

  activeMarkers.forEach((marker) => marker.remove());
  activeMarkers = [];

  carparks.forEach((carpark) => {
    const newMarker = new mapboxgl.Marker({
      color: "#0a1a44",
    })
      .setLngLat([carpark.carpark_lng, carpark.carpark_lat])
      .addTo(map);

    newMarker.getElement().style.cursor = "pointer";

    newMarker.getElement().addEventListener("click", () => {
      toggleBookingForm(carpark.carpark_id);
    });

    activeMarkers.push(newMarker);
  });
}

async function searchCarparks() {
  const location = document.getElementById("search-location").value;
  const searchRadius = document.getElementById("search-radius").value;
  const startVal = document.getElementById("search-start").value;
  const endVal = document.getElementById("search-end").value;

  if (!location || !startVal || !endVal) {
    alert("Please fill in all fields");
    return;
  }

  // Convert datetime-local → SQL format
  const startISO = startVal.replace("T", " ") + ":00";
  const endISO = endVal.replace("T", " ") + ":00";

  const geoRes = await fetch(
    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(
      location
    )}.json?access_token=${MAPBOX_TOKEN}`
  );

  const geoData = await geoRes.json();

  if (!geoData.features.length) {
    alert("Location not found");
    return;
  }

  const [lng, lat] = geoData.features[0].center;

  const params = new URLSearchParams({
    id: "searchCarparks",
    lat,
    lng,
    radius: searchRadius,
    startTime: startISO,
    endTime: endISO,
  });

  const res = await fetch(`/php/api/index.php?${params.toString()}`);
  const json = await res.json();

  if (!json.data || !Array.isArray(json.data)) {
    console.error("Search failed:", json);
    alert("No available carparks found.");
    return;
  }

  renderMarkers(json.data);
  map.flyTo({ center: [lng, lat], zoom: 12 });
}
