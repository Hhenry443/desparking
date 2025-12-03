function toggleBookingForm(carparkID, carparkName = "") {
  carparkID = String(carparkID);

  const bookingForm = document.getElementById("booking-form-container");
  const current = bookingForm.dataset.current;

  // Toggle if same car park was clicked
  if (current === carparkID) {
    bookingForm.classList.toggle("hidden");
    return;
  }

  bookingForm.dataset.current = carparkID;
  bookingForm.classList.remove("hidden");

  bookingForm.innerHTML = `
    <div class="bg-white shadow-xl rounded-xl p-5 w-full max-w-md border border-gray-200 relative">

      <!-- Close button -->
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
        <input type="hidden" name="carpark_id" value="${carparkID}">

        <!-- Name field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
          <input 
            type="text" 
            name="booking_name" 
            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
            placeholder="John Smith"
          >
        </div>

        <!-- Contact field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
          <input 
            type="email" 
            name="booking_email" 
            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
            placeholder="you@example.com"
          >
        </div>

        <!-- Date/time -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input 
              type="date" 
              name="booking_date" 
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
            <input 
              type="time" 
              name="booking_time" 
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
            >
          </div>
        </div>

        <!-- Submit button -->
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
}

// Comment
function insertBooking() {
  let bookingData = new FormData(document.getElementById("booking-form"));

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
