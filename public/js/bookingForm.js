function toggleBookingForm(carparkID) {
  carparkID = String(carparkID);

  const bookingForm = document.getElementById("booking-form-container");
  const current = bookingForm.dataset.current;

  // Toggle if clicking same marker again
  if (current === carparkID) {
    bookingForm.classList.toggle("hidden");
    return;
  }

  // Set active carpark
  bookingForm.dataset.current = carparkID;
  bookingForm.classList.remove("hidden");

  // Pull carpark data
  const carpark = markers.find((m) => String(m.carpark_id) === carparkID);

  if (!carpark) {
    bookingForm.innerHTML = `<div class="p-4 bg-red-200 rounded-xl">Carpark not found.</div>`;
    return;
  }

  const carparkName = carpark.name || carpark.carpark_name || "Car Park";

  // Get current date and time
  const now = new Date();
  const today = now.toISOString().split("T")[0]; // YYYY-MM-DD format
  const currentTime = now.toTimeString().slice(0, 5); // HH:MM format

  // 🔹 BOOKABLE — Full Form
  if (carpark.carpark_type === "bookable") {
    bookingForm.innerHTML = `
      <div class="bg-white shadow-xl rounded-xl p-5 w-full max-w-md border border-gray-200 relative">

        <button 
          onclick="document.getElementById('booking-form-container').classList.add('hidden');" 
          class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
          ✕
        </button>

        <h2 class="text-xl font-semibold mb-3 text-gray-800">
          Book a Space
        </h2>

        <p class="text-sm text-gray-600 mb-4">
          You’re booking car park: <span class="font-semibold text-gray-800">${carparkName}</span>
        </p>

        <form method="POST" id="booking-form" class="space-y-4">
          <input type="hidden" name="booking_carpark_id" value="${carparkID}">
          <input type="hidden" name="action" value="insertBooking">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
            <input 
              type="text" 
              name="booking_name" 
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
              placeholder="John Smith"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
            <input 
              type="email" 
              name="booking_email" 
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
              placeholder="you@example.com"
            >
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
              <input 
                type="date" 
                name="booking_date" 
                value="${today}"
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
              <input 
                type="time" 
                name="booking_time" 
                value="${currentTime}"
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
              >
            </div>
          </div>

          <button 
            type="button" 
            onclick="insertBooking()"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg transition"
          >
            Confirm Booking
          </button>
        </form>
      </div>
    `;
    return;
  }

  // 🔹 AFFILIATE — Simple Link Box (styled to match)
  if (carpark.carpark_type === "affiliate") {
    bookingForm.innerHTML = `
      <div class="bg-white shadow-xl rounded-xl p-5 w-full max-w-md border border-gray-200 relative">

        <button 
          onclick="document.getElementById('booking-form-container').classList.add('hidden');" 
          class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
          ✕
        </button>

        <h2 class="text-xl font-semibold mb-3 text-gray-800">
          Partner Booking
        </h2>

        <p class="text-sm text-gray-600 mb-4">
          This car park (<span class="font-semibold text-gray-800">${carparkName}</span>) is booked via our trusted partner.
        </p>

        <a 
          href="${carpark.affiliate_url}" 
          target="_blank"
          class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition"
        >
          Visit Partner Site
        </a>
      </div>
    `;
    return;
  }

  // Unknown type fallback
  bookingForm.innerHTML = `<div class="p-4 bg-yellow-100 rounded-xl border">Unknown carpark type.</div>`;
}

// Comment
function insertBooking() {
  let bookingData = new FormData(document.getElementById("booking-form"));

  for (var pair of bookingData.entries()) {
    console.log(pair[0] + ", " + pair[1]);
  }

  fetch("php/api/index.php?id=insertBooking", {
    method: "POST",
    body: bookingData,
  })
    .then((response) => {
      console.log(response);
    })
    .catch(function (error) {
      console.log(error);
    });
}
