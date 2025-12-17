function toggleBookingForm(carparkID) {
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
  const carparkPrice = carpark.carpark_price || "N/a";
  const carparkFeatures = carpark.carpark_features || "N/a";

  informationContainer.classList.remove("hidden");

  // BOOKABLE — info only + book now button
  if (carpark.carpark_type === "bookable") {
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
          <p class="text-sm text-gray-700 mb-4"><strong>Price:</strong> £${carparkPrice} per hour</p>

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

  // AFFILIATE — keep link button
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
          <p class="text-sm text-gray-700 mb-4"><strong>Price:</strong> £${carparkPrice}</p>

          <p class="text-sm text-gray-700 mb-4"><strong>Features:</strong> ${carparkFeatures}</p>

          <a 
              href="${carpark.affiliate_url}" 
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
