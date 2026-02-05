<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Optional: pull booking ID from query string
$bookingID = $_GET['booking_id'] ?? null;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Booking Confirmed • DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="./css/output.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

<body class="bg-[#ebebeb] min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8 text-center">

        <!-- Tick -->
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                <svg xmlns'svg' viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="w-8 h-8 text-green-600" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-semibold text-gray-800 mb-2">
            Booking Confirmed
        </h1>

        <p class="text-gray-600 mb-6">
            Your parking space has been successfully reserved.
        </p>

        <?php if ($bookingID): ?>
            <p class="text-sm text-gray-500 mb-6">
                Booking reference:<br>
                <span class="font-mono text-gray-800 font-semibold">
                    #<?= htmlspecialchars($bookingID) ?>
                </span>
            </p>
        <?php endif; ?>

        <div class="space-y-3">
            <a
                href="/index.php"
                class="block w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg transition">
                Back to Map
            </a>

            <a
                href="/account.php"
                class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 rounded-lg transition">
                View My Bookings
            </a>
        </div>

    </div>

</body>

</html>