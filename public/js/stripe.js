// Initialize Stripe.js
const stripe = Stripe("pk_test_wGQVF7QeuldBJrMPt10D2esF");

initialize();

// Fetch Checkout Session and retrieve the client secret
async function initialize() {
  const fetchClientSecret = async () => {
    const response = await fetch(
      "/php/api/stripe/create-checkout-session.php",
      {
        method: "POST",
      }
    );

    const data = await response.json();

    // DEBUG: Look at your console!
    console.log("Full response object:", data);
    console.log("Client Secret value:", data.clientSecret);

    if (!data.clientSecret || typeof data.clientSecret !== "string") {
      console.error("ERROR: clientSecret is missing or not a string!");
    }

    return data.clientSecret;
  };

  const checkout = await stripe.initEmbeddedCheckout({
    fetchClientSecret,
  });

  checkout.mount("#checkout");
}
