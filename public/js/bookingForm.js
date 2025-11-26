function toggleBookingForm(carparkID) {
  const bookingForm = document.getElementById("booking-form-container");

  bookingForm.classList.toggle("hidden");
  bookingForm.innerHTML = `
        <form method="POST" id="booking-form">
            <input type="hidden" value="insertBooking" name="id">
            <input type="text" name="carpark_id" id="carpark_id" value="${carparkID}" hidden></input>
            <input type="text" name="booking_name" id="booking_name" class="bg-white"></input>
            <input type="button" onclick=insertBooking()>Send booking</button>
        </form>
  `;
}

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
