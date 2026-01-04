<?php

session_start();

// If not logged in, kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100 pt-20">

<div class="max-w-xl mx-auto bg-white rounded-2xl shadow-lg p-8 mt-10">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">
            Create a Car Park
        </h1>
        <p class="text-gray-500 mt-1">
            Add a new parking space and start accepting bookings.
        </p>
    </div>

    <form action="/php/api/index.php?id=insertCarpark" method="POST" class="space-y-6">

        <!-- Car Park Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Car Park Name
            </label>
            <input
                type="text"
                name="carpark_name"
                required
                placeholder="e.g. Queens Road Space"
                class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-2.5
                       focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            >
        </div>

        <!-- Address -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Address
            </label>
            <input
                type="text"
                name="carpark_address"
                required
                placeholder="Norwich NR1 2AB"
                class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-2.5
                       focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            >
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Description
            </label>
            <input
                type="text"
                name="carpark_description"
                required
                placeholder="e.g. Secure parking with CCTV"
                class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-2.5
                       focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            >
        </div>

        <!-- Location -->
        <div>
            <p class="text-sm font-medium text-gray-700 mb-2">
                Location
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input
                    type="number"
                    step="any"
                    name="carpark_lat"
                    required
                    placeholder="Latitude"
                    class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-2.5
                           focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                >
                <input
                    type="number"
                    step="any"
                    name="carpark_lng"
                    required
                    placeholder="Longitude"
                    class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-2.5
                           focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                >
            </div>
        </div>

        <!-- Price -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Price per 30 minutes (£)
            </label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="carpark_price"
                required
                placeholder="1.50"
                class="w-full rounded-xl border-gray-300 bg-gray-50 px-4 py-2.5
                       focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            >
        </div>

        <!-- Submit -->
        <div class="pt-4">
            <button
                type="submit"
                class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800
                       text-white font-semibold py-3 rounded-xl transition
                       shadow-md hover:shadow-lg"
            >
                Create Car Park
            </button>
        </div>

    </form>

</div>
</body>

</html>