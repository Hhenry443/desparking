<?php
session_start();

$userId = $_SESSION['user_id'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/css/output.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gray-50 pt-24 min-h-screen">

<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="max-w-4xl mx-auto px-6 pb-20">

    <h1 class="text-3xl font-bold text-gray-900 mb-10">
        Parking Contract
    </h1>

    <div class="space-y-10 text-sm leading-relaxed text-gray-700">

        <!-- About These Terms -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">About These Terms</h2>
            <p>
                These terms form part of the terms and conditions for using the DesParking online service
                (available <a href="/terms" class="text-cyan-600 font-medium hover:underline">here</a>) and set out
                the terms of the contract between two users of the service when one pays to park at a space advertised
                by another (the "Parking Contract").
            </p>
        </section>

        <!-- The Parties -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">The Parties to This Parking Contract</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Drivers</h3>
                    <p>
                        Individuals who pay to park are referred to as "Drivers" in this agreement, even if they are not
                        the ones driving the vehicle.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Space Owners</h3>
                    <p>Those who advertise parking spaces are referred to as "Space Owners".</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li><strong>Private Space Owners:</strong> Individuals offering parking spaces located on private property not used for business purposes.</li>
                        <li><strong>Commercial Space Owners:</strong> Businesses offering spaces on commercial premises, such as dedicated car parks or car parks associated with other businesses.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">DesParking's Role</h3>
                    <p>
                        DesParking acts as an agent for the Space Owner to create a binding contract with the Driver when a
                        booking is made. DesParking is not a party to the Parking Contract but may exercise specific rights
                        under it as expressly stated.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Relationship to DesParking Terms</h3>
                    <p>
                        By entering into the Parking Contract, both the Driver and the Space Owner agree to comply with the
                        terms and conditions of DesParking's online service in relation to the booking.
                    </p>
                </div>
            </div>
        </section>

        <!-- Formation of the Parking Contract -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Formation of the Parking Contract</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">When the Contract Becomes Binding</h3>
                    <p>
                        The Parking Contract becomes binding when the Driver completes the booking process, and payment is
                        received and accepted by DesParking on behalf of the Space Owner.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">No Further Terms Required</h3>
                    <p>
                        The Driver and Space Owner are not obligated to agree to additional terms or conditions beyond those
                        outlined in this Parking Contract.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Entire Agreement</h3>
                    <p>
                        The Parking Contract constitutes the entire agreement between the parties, and neither party may rely
                        on any statements outside the terms of the contract, except for the advertised description of the
                        parking space.
                    </p>
                </div>
            </div>
        </section>

        <!-- Driver Rights and Obligations -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Driver Rights and Obligations</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Conditions for Entering the Contract as a Driver</h3>
                    <p>By entering the Parking Contract, the Driver confirms they:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Have the authority to enter the contract.</li>
                        <li>Hold a valid driving licence (or ensure that any authorised user of the space does).</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Driver's Rights</h3>
                    <p>The Driver has the right to:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Park the vehicle specified in the booking at the selected space during the agreed times.</li>
                        <li>Authorise another person to park the same vehicle, subject to the terms of the contract.</li>
                    </ul>
                    <p class="mt-3 text-gray-500 italic">Driver must give one month's notice for monthly bookings.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Driver's Obligations</h3>
                    <p>The Driver must:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Ensure any authorised person complies with the Parking Contract.</li>
                        <li>Use the parking space only during the booked times and for the booked vehicle.</li>
                        <li>Notify DesParking or the Space Owner of any damage to the space during the booking period.</li>
                        <li>Maintain the cleanliness of the parking space and leave it in its original condition.</li>
                        <li>Comply with all specific requirements outlined in the booking confirmation.</li>
                        <li>Vacate the space at the end of the booking period.</li>
                        <li>Return any access equipment provided within 48 hours of the booking's end or pay for its replacement.</li>
                        <li>Take full responsibility for the safety of the vehicle and its contents during the booking period.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Space Owner Rights and Obligations -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Space Owner Rights and Obligations</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Conditions for Entering the Contract as a Space Owner</h3>
                    <p>By entering the Parking Contract, the Space Owner confirms they:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Have the authority to grant use of the parking space.</li>
                        <li>Have obtained any necessary permissions and approvals for renting the space.</li>
                        <li>Comply with all relevant laws and regulations.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Space Owner's Rights</h3>
                    <p>The Space Owner is entitled to receive payment for bookings, less DesParking's service fees.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Space Owner's Obligations</h3>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li><strong>Private Space Owners:</strong> Must ensure the parking space is available as advertised and comply with all legal requirements.</li>
                        <li><strong>Commercial Space Owners:</strong> Must provide spaces as advertised, subject to availability, and comply with legal requirements.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Payment -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Short-Term Bookings</h3>
                    <p>Payment for bookings of 30 days or fewer is collected in full at the time of booking.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Long-Term Bookings</h3>
                    <p>
                        For bookings longer than 30 days, an initial deposit equivalent to one month's payment is required,
                        with monthly payments collected in advance.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Failed Payments</h3>
                    <p>
                        DesParking may pursue outstanding payments through legal or debt collection measures, with associated
                        costs passed on to the Driver.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Booking Confirmation</h3>
                    <p>
                        A confirmation email will be sent upon receipt of payment, detailing the booking period and vehicle
                        registration number.
                    </p>
                </div>
            </div>
        </section>

        <!-- Cancellations -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Cancellations</h2>

            <p class="mb-6">
                The Driver or Space Owner may cancel a booking in accordance with the cancellation policy outlined below.
            </p>

            <div class="space-y-6">
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Cancellation by the Driver</h3>
                    <p class="mb-4">Refunds depend on the timing of the cancellation and the type of booking:</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border border-gray-200 rounded-xl overflow-hidden">
                            <thead>
                                <tr class="bg-gray-100 text-gray-900">
                                    <th class="text-left px-4 py-3 font-semibold border-b border-gray-200">Type of Booking</th>
                                    <th class="text-left px-4 py-3 font-semibold border-b border-gray-200">Timing of Cancellation</th>
                                    <th class="text-left px-4 py-3 font-semibold border-b border-gray-200">Refund</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr class="bg-white">
                                    <td class="px-4 py-3 align-top" rowspan="3">Short-Term<br><span class="text-gray-500">(29 days or fewer)</span></td>
                                    <td class="px-4 py-3">At least 48 hours before start time</td>
                                    <td class="px-4 py-3 text-green-700 font-medium">Full refund</td>
                                </tr>
                                <tr class="bg-white border-t border-gray-100">
                                    <td class="px-4 py-3">Less than 24 hours before start time</td>
                                    <td class="px-4 py-3 text-red-600 font-medium">No refund</td>
                                </tr>
                                <tr class="bg-white border-t border-gray-100">
                                    <td class="px-4 py-3">After parking session has started</td>
                                    <td class="px-4 py-3 text-red-600 font-medium">No refund</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-3 align-top" rowspan="3">Long-Term<br><span class="text-gray-500">(30 days or more)</span></td>
                                    <td class="px-4 py-3">At least 48 hours before start time</td>
                                    <td class="px-4 py-3 text-green-700 font-medium">Full refund</td>
                                </tr>
                                <tr class="bg-gray-50 border-t border-gray-100">
                                    <td class="px-4 py-3">Up to the second day of the session</td>
                                    <td class="px-4 py-3 text-yellow-700 font-medium">Pro-rata refund</td>
                                </tr>
                                <tr class="bg-gray-50 border-t border-gray-100">
                                    <td class="px-4 py-3">After the second day</td>
                                    <td class="px-4 py-3 text-yellow-700 font-medium">Refund minus 30 days' cost</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-3 text-gray-500 text-xs">Excludes service fees.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Cancellation by DesParking or the Space Owner</h3>
                    <p>
                        DesParking or the Space Owner may cancel a booking under certain conditions, such as non-payment by
                        the Driver.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Deposit Returns</h3>
                    <p>
                        For deposits to be returned, anything the host has given the driver such as key fobs, permits, etc.
                        must be returned and verified before a refund is processed. Failure to do so will result in no refund
                        and further charges may apply.
                    </p>
                </div>
            </div>
        </section>

        <!-- Other Terms -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Other Terms</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Transfer of Rights</h3>
                    <p>Neither party may transfer their rights under this contract.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Severability</h3>
                    <p>If any part of this contract is found to be invalid, the remaining provisions will continue in effect.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Governing Law</h3>
                    <p>
                        This Parking Contract is governed by English law. Legal proceedings may be brought in English, Scottish,
                        or Northern Irish courts, depending on the parties' location.
                    </p>
                </div>
            </div>

            <p class="mt-6">
                For further details, please refer to the
                <a href="/terms" class="text-cyan-600 font-medium hover:underline">DesParking Terms and Conditions</a>.
            </p>
        </section>

    </div>

</div>

<?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>