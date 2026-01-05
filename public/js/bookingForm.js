async function toggleBookingForm(carparkID) {
  carparkID = String(carparkID);

  const bookingForm = document.getElementById("booking-form-container");
  const informationContainer = document.getElementById(
    "carpark-information-container"
  );

  // Hide booking panel since bookings are now handled on another page
  bookingForm.classList.add("hidden");

  // Pull carpark data
  const carpark = currentCarparks.find(
    (c) => String(c.carpark_id) === String(carparkID)
  );

  if (!carpark) {
    informationContainer.innerHTML = `<div class="p-4 bg-red-200 rounded-xl">Carpark not found.</div>`;
    return;
  }

  const carparkName = carpark.name || carpark.carpark_name || "Car Park";
  const carparkDescription = carpark.carpark_description || "N/a";
  const carparkCapacity = carpark.carpark_capacity || "N/a";
  const carparkFeatures = carpark.carpark_features || "N/a";

  informationContainer.classList.remove("hidden");

  // BOOKABLE – fetch rates and show pricing table
  if (carpark.carpark_type === "bookable") {
    // Fetch rates for this car park
    let ratesHTML =
      '<p class="text-sm text-gray-500 italic">Loading pricing...</p>';

    try {
      const response = await fetch(
        `/php/api/index.php?id=getCarparkRates&carpark_id=${carparkID}`
      );
      const data = await response.json();

      if (data.success && data.rates && data.rates.length > 0) {
        ratesHTML = `
          <div class="mb-4">
            <p class="text-sm font-semibold text-gray-700 mb-2">Pricing:</p>
            <div class="bg-gray-50 rounded-lg p-3 space-y-1">
              ${data.rates
                .map(
                  (rate) => `
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">${rate.duration_minutes} min${
                    rate.duration_minutes > 1 ? "s" : ""
                  }</span>
                  <span class="font-medium text-gray-800">£${(
                    rate.price / 100
                  ).toFixed(2)}</span>
                </div>
              `
                )
                .join("")}
            </div>
          </div>
        `;
      } else {
        ratesHTML =
          '<p class="text-sm text-gray-500 mb-4">No pricing information available.</p>';
      }
    } catch (error) {
      console.error("Error fetching rates:", error);
      ratesHTML =
        '<p class="text-sm text-red-500 mb-4">Unable to load pricing.</p>';
    }

    informationContainer.innerHTML = `
      <div class="h-full w-full bg-white p-6 overflow-y-auto">

          <img 
              src="${
                carpark.carpark_image || "/images/default-carpark-image.png"
              }"
              class="w-full h-40 object-cover rounded-lg mb-4"
              alt="Car Park Image"
          />

          <h2 class="text-xl font-semibold mb-3 text-gray-800">${carparkName}</h2>

          <p class="text-sm text-gray-600 mb-4">${carparkDescription}</p>

          <p class="text-sm text-gray-700 mb-2"><strong>Capacity:</strong> ${carparkCapacity}</p>

          ${ratesHTML}

          <p class="text-sm text-gray-700 mb-4"><strong>Features:</strong> ${carparkFeatures}</p>

          <a 
              href="/book.php?carpark_id=${carparkID}"
              class="w-full block text-center bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg transition cursor-pointer mt-4"
          >
              Book Now
          </a>

      </div>`;
    return;
  }

  // AFFILIATE – keep link button
  if (carpark.carpark_type === "affiliate") {
    informationContainer.innerHTML = `
      <div class="h-full w-full bg-white p-6 overflow-y-auto">

          <img 
              src="${
                carpark.carpark_image || "/images/default-carpark-image.png"
              }"
              class="w-full h-40 object-cover rounded-lg mb-4"
              alt="Car Park Image"
          />

          <h2 class="text-xl font-semibold mb-3 text-gray-800">${carparkName}</h2>

          <p class="text-sm text-gray-600 mb-4">${carparkDescription}</p>

          <p class="text-sm text-gray-700"><strong>Capacity:</strong> ${carparkCapacity}</p>

          <p class="text-sm text-gray-700 mb-4"><strong>Features:</strong> ${carparkFeatures}</p>

          <a 
              href="${carpark.carpark_affiliate_url}" 
              target="_blank"
              class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition mt-4"
          >
              Visit Partner Site
          </a>

      </div>`;
    return;
  }

  // Unknown type fallback
  informationContainer.innerHTML = `<div class="p-4 bg-yellow-100 rounded-xl border">Unknown carpark type.</div>`;
}
