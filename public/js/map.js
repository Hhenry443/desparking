addEventListener("DOMContentLoaded", mapboxSetup());

function mapboxSetup() {
  mapboxgl.accessToken = MAPBOX_TOKEN;

  const lat = 52.6293;
  const lng = 1.2979;

  const map = new mapboxgl.Map({
    container: "map",
    center: [lng, lat],
    zoom: 9,
  });

  markers.forEach((marker) => {
    var newMarker = new mapboxgl.Marker({
      color: "#" + marker.marker_colour,
    })
      .setLngLat([marker.carpark_lng, marker.carpark_lat])
      .addTo(map);

    newMarker.getElement().addEventListener("click", () => {
      toggleBookingForm(marker.carpark_id, marker.carpark_name);
    });
  });
}
