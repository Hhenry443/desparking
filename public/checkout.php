<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/paypal.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get booking data from POST
$isGuest  = !isset($_SESSION['user_id']);
$userID   = $isGuest ? null : (int) $_SESSION['user_id'];

$carparkID           = $_POST['booking_carpark_id'] ?? null;
$bookingName         = $_POST['booking_name'] ?? null;
$bookingEmail        = $_POST['booking_email'] ?? null;
$vehicleID           = $isGuest ? null : ($_POST['booking_vehicle_id'] ?? null);
$bookingRegistration = $isGuest ? (trim($_POST['booking_registration'] ?? '')) : null;
$isMonthly           = ($_POST['booking_is_monthly'] ?? '0') === '1';

// Validate common required fields
if (!$carparkID || !$bookingName || !$bookingEmail) {
    header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Missing required fields"));
    exit();
}

if ($isGuest && !$bookingRegistration) {
    header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Please enter your vehicle registration"));
    exit();
}

if (!$isGuest && !$vehicleID) {
    header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Please select a vehicle"));
    exit();
}

if ($isMonthly) {
    $startDate    = $_POST['booking_start_date'] ?? date('Y-m-d');
    $bookingStart = $startDate . " 00:00:00";
    $bookingEnd   = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($startDate)));
} else {
    $startDate = $_POST['booking_start_date'] ?? null;
    $endDate   = $_POST['booking_end_date'] ?? null;
    $startTime = $_POST['booking_start_time'] ?? null;
    $endTime   = $_POST['booking_end_time'] ?? null;

    if (!$startDate || !$endDate || !$startTime || !$endTime) {
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Missing required fields"));
        exit();
    }

    $bookingStart = $startDate . " " . $startTime . ":00";
    $bookingEnd   = $endDate   . " " . $endTime   . ":00";

    if ($bookingStart >= $bookingEnd) {
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("End time must be after start time"));
        exit();
    }
}

// For logged-in users, verify the vehicle belongs to them
if (!$isGuest) {
    $db = Dbh::getConnection();

    $stmt = $db->prepare("
        SELECT vehicle_id
        FROM vehicles
        WHERE vehicle_id = :vehicleID
        AND user_id = :userID
        LIMIT 1
    ");

    $stmt->execute([
        ':vehicleID' => $vehicleID,
        ':userID'    => $userID
    ]);

    if (!$stmt->fetch()) {
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Invalid vehicle selected"));
        exit();
    }
}

// Overlap / capacity check before showing payment form
if (!$isMonthly) {
    $bookingsModel  = new WriteBookings();
    $ReadCarparks   = new ReadCarparks();
    $carparkForCap  = $ReadCarparks->getCarparkById($carparkID);
    $capacity       = (int) ($carparkForCap['carpark_capacity'] ?? 1);
    $overlapping    = $bookingsModel->countOverlappingBookings((int) $carparkID, $bookingStart, $bookingEnd);

    if ($overlapping >= $capacity) {
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Sorry, this car park is fully booked for your selected time. Please choose a different slot."));
        exit();
    }
}

// Store booking data in session
$_SESSION['pending_booking'] = [
    'carpark_id'   => (int) $carparkID,
    'name'         => $bookingName,
    'email'        => $bookingEmail,
    'start'        => $bookingStart,
    'end'          => $bookingEnd,
    'vehicle_id'   => $vehicleID ? (int) $vehicleID : null,
    'user_id'      => $userID,
    'is_monthly'   => $isMonthly,
    'registration' => $bookingRegistration,
];

if (!isset($carparkForCap)) {
    $ReadCarparks = new ReadCarparks();
    $carpark = $ReadCarparks->getCarparkById($carparkID);
} else {
    $carpark = $carparkForCap;
}
$title = "Payment –" . htmlspecialchars($carpark['carpark_name']);

?>
<!DOCTYPE html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="bg-[#ebebeb] min-h-screen">

    <!-- IMAGE HEADER -->
    <div class="w-full h-56 md:h-72 lg:h-80 overflow-hidden">
        <img
            src=" /images/default-carpark-image.png"
            class="w-full h-full object-cover"
            alt="Car Park Image">
    </div>

    <!-- MAIN CONTENT -->
    <div class="max-w-2xl mx-auto bg-white shadow-xl rounded-xl p-6 mt-6 border border-gray-200">
        <!-- Back Button -->
        <a href="/map.php" class="text-blue-600 hover:underline text-sm mb-3 inline-block">
            ← Back to map
        </a>

        <h1 class="text-2xl font-semibold text-gray-800 mb-4">Complete Your Payment</h1>

        <?php if (!$isMonthly): ?>
        <!-- Apple Pay: hidden until the SDK confirms the device and merchant are eligible -->
        <div id="applepay-container" class="hidden">
            <div id="applepay-error" class="hidden mb-3 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm"></div>

            <apple-pay-button id="applepay-button" buttonstyle="black" type="buy" locale="en-GB"></apple-pay-button>

            <div class="flex items-center gap-3 my-5">
                <span class="h-px flex-1 bg-gray-200"></span>
                <span class="text-xs uppercase tracking-wide text-gray-400">or</span>
                <span class="h-px flex-1 bg-gray-200"></span>
            </div>
        </div>
        <?php endif; ?>

        <div id="checkout">
            <!-- PayPal Buttons will insert the payment form here -->
        </div>
    </div>

    <br><br>

    <?php
    $sdkParams = [
        'client-id' => PAYPAL_CLIENT_ID,
        'currency'  => 'GBP',
        'intent'    => $isMonthly ? 'subscription' : 'capture',
    ];

    if ($isMonthly) {
        $sdkParams['vault'] = 'true';
    } else {
        // Apple Pay only funds one-off orders — PayPal subscriptions can't be
        // created from an Apple Pay token, so the monthly flow stays PayPal-only.
        $sdkParams['components'] = 'buttons,applepay';
    }
    ?>
    <script src="https://www.paypal.com/sdk/js?<?= http_build_query($sdkParams) ?>"></script>
    <script>
        const isMonthly = <?= $isMonthly ? 'true' : 'false' ?>;

        function showError(message) {
            document.getElementById('checkout').innerHTML =
                '<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">' +
                '<p class="font-bold">Payment Error</p>' +
                '<p class="text-sm">' + message + '</p>' +
                '</div>';
        }

        async function postJSON(url, payload) {
            const response = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload || {})
            });
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            return data;
        }

        if (isMonthly) {
            paypal.Buttons({
                createSubscription: async function (data, actions) {
                    const session = await postJSON("/php/api/paypal/create-subscription-session.php", {
                        carpark_id: "<?= $carparkID ?>"
                    });
                    window.__checkoutRef = session.checkoutRef;
                    return actions.subscription.create({
                        plan_id: session.planId,
                        custom_id: session.checkoutRef
                    });
                },
                onApprove: async function (data) {
                    const result = await postJSON("/php/api/paypal/confirm-subscription.php", {
                        subscriptionID: data.subscriptionID,
                        checkoutRef: window.__checkoutRef
                    });
                    window.location = result.redirect;
                },
                onError: function (err) {
                    console.error("PayPal subscription error:", err);
                    showError(err.message || "Something went wrong. Please try again.");
                }
            }).render('#checkout');
        } else {
            paypal.Buttons({
                createOrder: async function () {
                    const data = await postJSON("/php/api/paypal/create-order.php", {
                        carpark_id: "<?= $carparkID ?>",
                        start_time: "<?= $bookingStart ?>",
                        end_time: "<?= $bookingEnd ?>",
                        vehicle_id: "<?= $vehicleID ?>"
                    });
                    return data.orderId;
                },
                onApprove: async function (data) {
                    const result = await postJSON("/php/api/paypal/capture-order.php", {
                        orderID: data.orderID
                    });
                    window.location = result.redirect;
                },
                onError: function (err) {
                    console.error("PayPal order error:", err);
                    showError(err.message || "Something went wrong. Please try again.");
                }
            }).render('#checkout');
        }
    </script>

<?php if (!$isMonthly): ?>
    <style>
        apple-pay-button {
            display: block;
            --apple-pay-button-width: 100%;
            --apple-pay-button-height: 48px;
            --apple-pay-button-border-radius: 8px;
            --apple-pay-button-padding: 0;
        }
    </style>

    <!-- Apple supplies the button element; their guidelines require their own markup -->
    <script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js" crossorigin async></script>
    <script>
        function showApplePayError(message) {
            const box = document.getElementById('applepay-error');
            box.textContent = message;
            box.classList.remove('hidden');
        }

        (async function initApplePay() {
            // Not an Apple Pay capable device/browser — the PayPal buttons stay the only option.
            if (!window.ApplePaySession || !ApplePaySession.supportsVersion(4) || !ApplePaySession.canMakePayments()) {
                return;
            }

            const applepay = paypal.Applepay();
            let config;

            try {
                config = await applepay.config();
            } catch (err) {
                console.error("Apple Pay config error:", err);
                return;
            }

            // Merchant account isn't enabled for Apple Pay, or this domain isn't registered.
            if (!config.isEligible) {
                return;
            }

            document.getElementById('applepay-container').classList.remove('hidden');
            document.getElementById('applepay-button').addEventListener('click', function () {
                startApplePay(applepay, config);
            });
        })();

        async function startApplePay(applepay, config) {
            let order;

            // The sheet has to show a total, so the order is created up front —
            // same call the PayPal button's createOrder makes.
            try {
                order = await postJSON("/php/api/paypal/create-order.php", {
                    carpark_id: "<?= $carparkID ?>",
                    start_time: "<?= $bookingStart ?>",
                    end_time: "<?= $bookingEnd ?>",
                    vehicle_id: "<?= $vehicleID ?>"
                });
            } catch (err) {
                console.error("Apple Pay create-order error:", err);
                showApplePayError(err.message || "Something went wrong. Please try again.");
                return;
            }

            const session = new ApplePaySession(4, {
                countryCode: config.countryCode,
                currencyCode: order.currency,
                merchantCapabilities: config.merchantCapabilities,
                supportedNetworks: config.supportedNetworks,
                requiredBillingContactFields: ["name", "phone", "postalAddress"],
                total: {
                    label: <?= json_encode($carpark['carpark_name'], JSON_UNESCAPED_UNICODE) ?>,
                    type: "final",
                    amount: order.amount
                }
            });

            session.onvalidatemerchant = async function (event) {
                try {
                    const payload = await applepay.validateMerchant({
                        validationUrl: event.validationURL,
                        displayName: "EveryonesParking"
                    });
                    session.completeMerchantValidation(payload.merchantSession);
                } catch (err) {
                    console.error("Apple Pay merchant validation error:", err);
                    session.abort();
                    showApplePayError("Apple Pay is unavailable right now — please pay with PayPal or card below.");
                }
            };

            session.onpaymentauthorized = async function (event) {
                try {
                    await applepay.confirmOrder({
                        orderId: order.orderId,
                        token: event.payment.token,
                        billingContact: event.payment.billingContact
                    });

                    const result = await postJSON("/php/api/paypal/capture-order.php", {
                        orderID: order.orderId
                    });

                    session.completePayment(ApplePaySession.STATUS_SUCCESS);
                    window.location = result.redirect;
                } catch (err) {
                    console.error("Apple Pay payment error:", err);
                    session.completePayment(ApplePaySession.STATUS_FAILURE);
                    showApplePayError(err.message || "Payment could not be completed. Please try again.");
                }
            };

            session.begin();
        }
    </script>
<?php endif; ?>
</body>

</html>