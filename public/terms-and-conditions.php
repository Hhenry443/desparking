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
        Terms &amp; Conditions
    </h1>

    <div class="space-y-10 text-sm leading-relaxed text-gray-700">

        <p>
            These terms and conditions govern your use of our website,
            <a href="https://www.desparking.uk" class="text-cyan-600 font-medium hover:underline">www.desparking.uk</a>
            (the Site), and the DesParking app (the App), as well as any services you access through our Site or App
            (collectively referred to as our Online Service).
        </p>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Importance of Reading These Terms</h2>
            <p>
                Please carefully read these terms before using our Site or App, and before paying to park or listing a
                parking space via our Online Service. These terms:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4">
                <li>Explain who we are and describe the relationships between users and businesses utilizing our Online Service.</li>
                <li>Detail the functioning of the Online Service, including how to use it to pay for parking or to list a parking space.</li>
                <li>Outline how contracts are established through the Online Service, what steps to take if issues arise, and other crucial information.</li>
            </ul>
        </section>

        <!-- Information About Us -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Information About Us and How to Contact Us</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Who We Are</h3>
                    <p>
                        We are Everyonesparking Limited, trading as DesParking, a company registered in England and Wales.
                        Our company registration number is 16143127.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">How to Contact Us</h3>
                    <p>
                        You can reach us by writing to us at Flat 71 Discovery Dock Apartments East, South Quay Square,
                        London, England, E14 9RU.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Definition of "Writing"</h3>
                    <p>References to "writing" or "written" in these terms include email communications.</p>
                </div>
            </div>
        </section>

        <!-- Roles and Legal Relationships -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Roles and Legal Relationships</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Using the Site and the App</h3>
                    <p>
                        Your use of our Site or App is permitted under these Terms of Use, regardless of whether you pay to
                        park, list a parking space, or register with us.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Privacy Policy</h3>
                    <p>Our Privacy Policy, available <a href="/privacy" class="text-cyan-600 font-medium hover:underline">here</a>, applies when you visit our Site or use our App.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Our Online Service</h3>
                    <p>
                        Our Online Service enables individuals and businesses to advertise available parking spaces and enter
                        agreements for individuals to pay for parking at these spaces.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">The Parking Contract</h3>
                    <p>
                        When you use our Online Service to pay for parking at an advertised space, the service facilitates a
                        parking contract between you and the parking space owner (a Parking Contract). The terms of the
                        Parking Contract are available <a href="/parking-contract" class="text-cyan-600 font-medium hover:underline">here</a>.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Drivers</h3>
                    <p>
                        The term "Drivers" refers to individuals who pay for parking, even if they are not the ones driving the vehicle.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Advertising a Parking Space</h3>
                    <p>
                        Terms applicable to advertising your parking space for rental via our Online Service are available
                        <a href="/space-owner-terms" class="text-cyan-600 font-medium hover:underline">here</a>.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Space Owners</h3>
                    <p>
                        The term "Space Owners" refers to individuals or businesses advertising parking spaces through the Online Service. This includes:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li><strong>Private Space Owners:</strong> Individuals advertising spaces on private property not used for business.</li>
                        <li><strong>Commercial Space Owners:</strong> Individuals or businesses advertising spaces on business premises, including dedicated car parks or car parks associated with other businesses such as shops or offices.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Our Online Service -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Our Online Service</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Provision of the Online Service</h3>
                    <p>The Online Services will be provided in accordance with their descriptions in these terms, as amended periodically.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Availability of the Online Service</h3>
                    <p>We aim to make the Online Service accessible via the Site and App, but we do not guarantee uninterrupted or error-free access.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Suspension or Withdrawal of the Online Service</h3>
                    <p>
                        We may suspend, withdraw, or restrict the availability of our Site, App, or Online Service for business or operational
                        reasons. We will endeavour to provide reasonable notice of any suspension or withdrawal. Such actions will not affect
                        any existing Parking Contracts between Drivers and Space Owners.
                    </p>
                </div>
            </div>
        </section>

        <!-- The Parking Spaces -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">The Parking Spaces</h2>

            <p>
                DesParking strives to ensure that parking spaces listed through our online service are of high quality. However, we do not
                guarantee that any parking space will meet your specific requirements. We rely on the Space Owner for the details provided
                about a parking space on our website or app, and while we make reasonable efforts to verify the accuracy of this information,
                we do not offer any warranty regarding these details.
            </p>

            <div class="mt-4">
                <h3 class="font-semibold text-gray-900">Availability of Parking Spaces</h3>
                <p class="mt-1">
                    We reserve any parking space booked through our online service based on the availability information provided to us by the
                    Space Owner. However, we do not guarantee the availability of any parking space, whether booked with a private or commercial
                    Space Owner. In case of any problems with a booking, please refer to the relevant sections below for private and commercial
                    Space Owners.
                </p>
            </div>
        </section>

        <!-- Your Account -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Account</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Registering as a Driver</h3>
                    <p>To use our online service to pay for parking, you need to register as a Driver. During registration, you will be asked to provide:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Full Name</li>
                        <li>Mobile Number</li>
                        <li>Valid Email Address</li>
                        <li>Vehicle Registration Number(s)</li>
                    </ul>
                    <p class="mt-2">We may request additional information from time to time.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Registering as a Private Space Owner</h3>
                    <p>To advertise a parking space through our online service, you need to register as a Space Owner. During registration, you will be asked to provide:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Full Name</li>
                        <li>Mobile Number</li>
                        <li>Valid Email Address</li>
                        <li>Address for each relevant parking space</li>
                        <li>Parking Space Listing Information (e.g., parking permit or bay number)</li>
                        <li>Payment Details (bank account, PayPal, credit, or debit card) for making payments to you</li>
                    </ul>
                    <p class="mt-2">We may request additional information from time to time.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Password and Security</h3>
                    <p>
                        You will be required to create a password when registering. Keep your password confidential and do not disclose it to any
                        third party. If someone else uses your password to access our Site or App, they will be considered to be acting as your agent.
                        We are not responsible for any actions taken by third parties who have accessed your account with your password. If you suspect
                        that someone else knows your password, please reset it immediately or notify us, and we will close your account at our discretion.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Use of the Account</h3>
                    <p>You agree not to:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Create any false account related to our online service or use your account for any immoral or illegal activity, including, but not limited to, malicious or fraudulent bookings, fraudulent listings, or money laundering.</li>
                        <li>Use the online service or interact with other users in any way that could harm our business or reputation, or negatively affect our relationship with a Space Owner or Driver.</li>
                        <li>Solicit or perform services for, or induce or attempt to induce, any customer, supplier, licensee, business relation, Driver, or Space Owner to enter into any arrangement related to parking outside of our online service.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Suspension or Closure of Account</h3>
                    <p>
                        We may suspend or close your account if you breach any of these terms or any term of a Parking Contract (as a Driver or Space Owner).
                        We may also suspend or close your account if your actions risk our goodwill or reputation. If your account is suspended or closed,
                        you will no longer be able to use the online service and may lose access to certain areas of our website and app.
                    </p>
                </div>
            </div>
        </section>

        <!-- Your Personal Information -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Personal Information</h2>

            <p>
                We will use your personal information in accordance with our Privacy Policy for purposes including communicating with you about
                bookings and/or listings. You grant us the right to communicate with you via any method we choose, including email, phone, text
                message, or social media platforms.
            </p>

            <div class="mt-4 space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Inaccurate Contact Details</h3>
                    <p>
                        If you provide inaccurate contact information, you may not receive important communications about your booking/listing or account.
                        We are not responsible if you fail to receive communications in these circumstances. If you realise your contact information is
                        incorrect, please contact us immediately to update your information.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Automatic Email Filing</h3>
                    <p>
                        Some email providers may direct emails from us into your 'Junk' or 'Promotions' folders. We are not responsible if you fail to
                        receive communications due to email filtering. We recommend adding us to your 'Permitted Senders' list and regularly checking your
                        'Junk' / 'Promotions' folders, especially if you are expecting a communication from us.
                    </p>
                </div>
            </div>
        </section>

        <!-- Paying to Park -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Paying to Park</h2>

            <p class="mb-4">
                This section applies to you if you use our online service to pay for parking. If you also use our online service to advertise a
                parking space, this section applies to you in your role as a Driver only.
            </p>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">The Parking Contract</h3>
                    <p>
                        To pay for parking at one of our listed parking spaces, you must have a registered account with us. By completing the booking
                        form on our online service, you enter into a Parking Contract with the relevant Space Owner. This contract is a separate agreement
                        between you and the Space Owner, with terms set out <a href="/parking-contract" class="text-cyan-600 font-medium hover:underline">here</a>.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">How the Parking Contract is Formed</h3>
                    <p>
                        DesParking is authorised by Space Owners to enter into Parking Contracts with Drivers through our online service. When you complete
                        a booking, we immediately accept it on behalf of the Space Owner, forming the Parking Contract between you and the Space Owner.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Making Your Payment</h3>
                    <p>
                        When you pay for parking through our online service, we collect the payment on behalf of the relevant Space Owner. The advertised
                        price includes our service fee. We may issue invoices and receipts electronically.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Cancelling a Booking</h3>
                    <p>
                        To cancel a booking, do so through your account or contact us via our Contact page. Refer to the Parking Contract section for our
                        cancellation policy, including required notice periods and refund eligibility.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">If We or the Space Owner Cancel a Booking</h3>
                    <p>
                        We or the Space Owner may cancel a booking before or after the parking period starts. Refer to the Parking Contract section for
                        our cancellation policy, including notice periods and refund eligibility.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Refunds</h3>
                    <p>
                        Refunds for cancellations will be returned to the original payment method. If the original payment account is closed, provide
                        evidence of the closure to process a refund to an alternative account.
                    </p>
                </div>
            </div>
        </section>

        <!-- Parking with a Private Space Owner -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Parking with a Private Space Owner</h2>

            <p class="mb-4">
                This section applies if you use our online service to pay for parking at a space owned by a private individual.
            </p>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">If There Is a Problem with Your Short-Term Booking</h3>
                    <p>
                        If the parking space does not reasonably match the description on our website or app, contact us via our Contact page within
                        48 hours of the booking start time. We will provide a suitable alternative or a full refund.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">If There Is a Problem with Your Long-Term (Rolling Monthly) Booking</h3>
                    <p>
                        If you have paid to park at a space provided by a Private Space Owner, you (or the driver of the vehicle in question) should
                        inspect the parking space upon arrival to ensure it matches the description on our website and app. If it does not reasonably
                        match, contact us via our Contact page as soon as possible, providing a description of the issue. If you contact us within 48
                        hours of the scheduled start of your parking session, we will offer you a suitable alternative or a full refund, minus the cost
                        of the days used until you notify us of the issue.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Renewing Your Booking</h3>
                    <p>
                        By using the Online Service to pay for parking with a Private Space Owner, you acknowledge that we have introduced you to that
                        Private Space Owner. To renew with that Private Space Owner, you must do so through the Online Service. If you enter a new
                        arrangement with the same Private Space Owner within six months after the end of the Parking Contract, we may charge you the
                        amount that would have been due if you had renewed through our Online Service.
                    </p>
                </div>
            </div>
        </section>

        <!-- Parking with a Commercial Space Owner -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Parking with a Commercial Space Owner</h2>

            <p class="mb-4">
                These terms apply if you use the Online Service to pay for parking at a space owned by a Commercial Space Owner, which includes
                businesses advertising spaces at their premises, such as dedicated car parks or those associated with other businesses like shops or offices.
            </p>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">If There Is a Problem</h3>
                    <p>
                        If you encounter any issue with the availability or condition of the parking space that does not reasonably match the description
                        on the Online Service, contact us via our Contact page as soon as possible with a description of the issue. We will provide you
                        with a suitable alternative parking space.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Parking Spaces Subject to Availability</h3>
                    <p>
                        Parking spaces booked with a Commercial Space Owner are subject to availability. If no space is available at the time you have
                        paid to park, the Commercial Space Owner is not liable, and our liability will be to provide a suitable alternative parking space.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">SORN Vehicles</h3>
                    <p>
                        A Commercial Space Owner may refuse a SORN vehicle parking at their location. If your booking is flagged as a SORN vehicle that
                        needs to be removed, DesParking will attempt to find you an alternative location.
                    </p>
                </div>
            </div>
        </section>

        <!-- Listing a Parking Space -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Listing a Parking Space</h2>

            <p class="mb-4">
                These terms apply if you advertise a parking space using the Online Service. If you use the Online Service both to pay to park and
                to advertise a space, these terms apply to you as a Space Owner only.
            </p>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">How Your Contract with Us Is Formed</h3>
                    <p>
                        To advertise a parking space, you must register with us and have a valid account. By completing the listing form using our Online
                        Service, you offer to enter into a contract with us based on these terms. If we accept your application, the contract between you
                        and us is formed on these terms.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">The Parking Contract</h3>
                    <p>
                        The Parking Contract is a separate agreement between you and the Driver, with terms set out
                        <a href="/parking-contract" class="text-cyan-600 font-medium hover:underline">here</a>.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">How the Parking Contract Is Formed</h3>
                    <p>
                        By completing the listing process, you authorise us to act on your behalf to form binding contracts with Drivers who pay to park
                        at your space through the Online Service. When we receive a booking application from a Driver, we will accept it automatically
                        based on your specified availability, forming the Parking Contract between you and the Driver.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Listing a Parking Space</h3>
                    <p>By completing the listing process, you authorise us to:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Advertise your parking space on our Site and App.</li>
                        <li>Collect, accept, and hold payments from Drivers.</li>
                        <li>Transfer payments to you after deducting our fees, with payments made on the first business day of the month following the booking start date.</li>
                        <li>Process all transactions using your account details.</li>
                        <li>Issue invoices and receipts electronically.</li>
                        <li>Cancel upcoming short-term bookings to accept a long-term booking at our discretion.</li>
                        <li>Refer to you or your space in future publicity.</li>
                        <li>Send promotional materials to Drivers with relevant information about your space.</li>
                    </ul>
                    <p class="mt-2">If you breach these terms, we may withhold payments received from Drivers to compensate for the breach.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Information Required for the Listing</h3>
                    <p>You must include all relevant information about your parking space in the listing, such as:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>The address of the space.</li>
                        <li>Restrictions on vehicle types.</li>
                        <li>Any other restrictions or important information.</li>
                        <li>Rates and available parking periods.</li>
                        <li>Your contact details.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Your Responsibility for the Listing – Private Space Owner</h3>
                    <p>
                        You are responsible for the accuracy of all information in your listing. Ensure the space meets the Driver's requirements under
                        the Parking Contract. We may remove your listing or cancel your account if the space does not conform to the information provided
                        or our standards.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Your Responsibility for the Listing – Commercial Space Owner</h3>
                    <p>
                        If you are a Commercial Space Owner, we will typically prepare the listing. We are responsible for the accuracy of the listing
                        and providing a suitable alternative space if issues arise.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Changing a Listing</h3>
                    <p>
                        You can change your listing at any time through your account. Changes to rates or parking periods will not affect confirmed
                        bookings but will apply to future bookings. To change existing bookings, provide 30 days' notice.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Cancelling a Listing</h3>
                    <p>
                        You can remove your listing at any time through your account. This will not affect confirmed bookings. To close your account,
                        contact us via our Contact page.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Cancelling a Booking</h3>
                    <p>
                        To cancel a booking, contact us via our Contact page. Refer to the Parking Contract for the cancellation policy and any refund obligations.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">If a Parking Enforcement Event Occurs</h3>
                    <p>
                        If a clamp is applied at the parking space and the Driver is not at fault, we will give the Space Owner 5–10 days to resolve
                        the issue. If unresolved, we will deduct the amount due from the Space Owner's earnings and may charge additional fees and
                        expenses incurred in collection efforts.
                    </p>
                </div>
            </div>
        </section>

        <!-- Private Space Owner Restrictions -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Private Space Owner Restrictions</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Agent Agreement</h3>
                    <p>
                        You agree to appoint DesParking as your agent for advertising the availability of your parking space.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Parking Space Usage</h3>
                    <p>You agree that if any person who:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Has previously booked your parking space through our Online Service, or</li>
                        <li>Has made an inquiry about your parking space through our Online Service (regardless of whether a booking was completed), or</li>
                        <li>Became aware of your parking space through our Online Service,</li>
                    </ul>
                    <p class="mt-3">
                        parks at your space, or allows someone responsible for or entitled to drive the same car to park, within six months of the
                        relevant booking period, inquiry, or the time when they became aware of the space through our Online Service, DesParking may
                        charge that person the fees as though they had parked in accordance with this agreement, and apply that person's deposit towards
                        the amounts owed.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Space Owner's Responsibility for Taxes</h3>
                    <p>
                        You are responsible for paying all fees and applicable taxes, including general services taxes, income taxes, goods and services
                        taxes, and other similar taxes or withholdings related to any payments you receive from Drivers in connection with these
                        arrangements in a timely manner.
                    </p>
                </div>
            </div>
        </section>

        <!-- Payments -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Payments</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Price Calculation</h3>
                    <p>
                        The price for parking at a given space is set by the Space Owner, who can change rates at any time. Price changes will not
                        affect any previously confirmed bookings unless the Space Owner issues a price increase on a current long-term booking. In
                        such cases, the Space Owner must provide DesParking with 30 days' notice. The advertised price on the Online Service includes
                        our fee, charged on top of the Space Owner's price for providing the Online Service.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Service Charge</h3>
                    <p>
                        Our charge for making the Online Service available is included in the price paid by the Driver. This amount is added to the
                        Space Owner's set price and is detailed in the Parking Contract section on rates and pricing models.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">VAT</h3>
                    <p>Prices stated on the Online Service for parking and listing a parking space may include applicable VAT.</p>
                </div>
            </div>
        </section>

        <!-- AutoPay -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">AutoPay</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Activating AutoPay</h3>
                    <p>
                        By activating AutoPay, you authorise us to charge your selected payment method for each parking session at AutoPay enabled car parks.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">AutoPay Payments</h3>
                    <p>
                        The tariffs for using AutoPay are displayed on signage in the car park, on our kiosks within the car park, on our website/apps,
                        or on specific promotional material.
                    </p>
                </div>
            </div>
        </section>

        <!-- Ratings and Reviews -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Ratings and Reviews</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Writing a Review</h3>
                    <p>After completing a booking, Drivers can leave a public review and submit a star rating about the parking space and experience.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Review Content</h3>
                    <p>Reviews must be accurate and free of offensive or defamatory language, complying with DesParking Review Policy.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Review Visibility</h3>
                    <p>Reviews and ratings are part of a Space Owner's profile and appear on the listing page.</p>
                </div>
            </div>
        </section>

        <!-- Changes to These Terms -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Changes to These Terms</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Modifications to These Terms</h3>
                    <p>
                        DesParking may modify these terms at any time without prior notice. The version of the terms that will apply to your booking
                        will be the version available on our website at the time you make your booking.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Modifications to the Website, App, and Online Service</h3>
                    <p>
                        We may update the website, app, and online service periodically. The version in effect when you place your booking will apply
                        until the end of your current booking.
                    </p>
                </div>
            </div>
        </section>

        <!-- Our Responsibility for Loss or Damage -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Our Responsibility for Loss or Damage Suffered by You</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Liability We Cannot Exclude</h3>
                    <p>
                        We do not exclude or limit our liability for death or personal injury caused by our negligence, or for fraudulent
                        misrepresentation or any other liability that cannot be excluded by law.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Liability We Do Exclude</h3>
                    <p>We strive to provide the website, app, and online service according to these terms. However, if you use the online service as a Driver:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>We do not guarantee the accuracy of parking space information provided through the online service and are not responsible for inaccuracies provided by private space owners, interactions with them, or the unavailability of a parking space at the time of your booking.</li>
                        <li>We do not own or operate the parking spaces listed on the online service and are not responsible for any personal injury or property damage resulting from your use of the online service or any booking with a space owner, to the fullest extent permissible by law.</li>
                    </ul>
                    <p class="mt-3">If you use the online service as a Space Owner:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>We are not responsible for any personal injury or property damage you suffer in connection with your use of the online service or any booking with a driver, to the fullest extent permissible by law.</li>
                        <li>We are not responsible for any losses you incur from claims brought against you by a driver or arising from interactions with a driver related to a parking contract.</li>
                    </ul>
                    <p class="mt-3">Regardless of your role in accessing our website or app, we are not responsible for:</p>
                    <ul class="list-disc pl-6 space-y-2 mt-3">
                        <li>Interruptions or cessation of transmission to or from the website and app.</li>
                        <li>Bugs, viruses, Trojan horses, or similar issues transmitted to or through the website or app by third parties, or any loss or damage incurred from your use of the website, app, or online service.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Business Losses</h3>
                    <p>
                        Our products are for domestic and private use only. If you use the products for commercial, business, or resale purposes, we
                        are not liable for any business losses, including loss of profits, contracts, goodwill, opportunity, and similar losses.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Interactions with Other Users</h3>
                    <p>
                        You are solely responsible for all communications and interactions with other users of the website and app and with any persons
                        resulting from your use of the online service.
                    </p>
                </div>
            </div>
        </section>

        <!-- How We Use Your Personal Information -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">How We Use Your Personal Information</h2>
            <p>
                We will use your personal information in accordance with our
                <a href="/privacy" class="text-cyan-600 font-medium hover:underline">Privacy Policy</a>.
            </p>
        </section>

        <!-- Ending These Arrangements -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Ending These Arrangements</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Our Right to Terminate</h3>
                    <p>
                        If we withdraw the online service or close your account, these terms will end. However, the agreement terms between you and us,
                        and the terms of any parking contract, will remain in effect for existing bookings or their consequences, including terms relating
                        to payments, refunds, disclaimers, liability, and damage.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Your Right to Terminate</h3>
                    <p>
                        If you close your account, these terms will end. However, the agreement terms between you and us, and the terms of any parking
                        contract, will remain in effect for existing bookings or their consequences, including terms relating to payments, refunds,
                        disclaimers, liability, and damage.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">No New Bookings</h3>
                    <p>
                        If you are a Space Owner, from the termination date of these terms, we will not confirm or accept new bookings for your parking
                        space. We will relocate outstanding bookings to the nearest possible site or issue a refund at our discretion.
                    </p>
                </div>
            </div>
        </section>

        <!-- Other Important Terms -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Other Important Terms</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Transfer of Agreement</h3>
                    <p>
                        We may transfer our rights and obligations under these terms to another organisation. If you have an account with us, we will
                        inform you in writing of any such transfer, ensuring it does not affect your rights under the contract.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Your Consent to Transfer Rights</h3>
                    <p>You may only transfer your rights or obligations under these terms to another person with our written consent.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">No Third-Party Rights</h3>
                    <p>This contract is solely between you and us. No other person has the right to enforce any of its terms.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Severability</h3>
                    <p>
                        Each paragraph of these terms operates separately. If any court or relevant authority finds any paragraph unlawful, the remaining
                        paragraphs will remain in full force and effect.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Delay in Enforcement</h3>
                    <p>
                        If we delay enforcing any of our rights under these terms, we can still enforce them later. For example, if you miss a payment
                        and we do not immediately chase you but continue to provide the products, we can still require you to make the payment at a later date.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Applicable Law and Jurisdiction</h3>
                    <p>
                        These terms are governed by English law, and either party can bring legal proceedings in the English courts. If you live in
                        Scotland, you can bring legal proceedings in either the Scottish or English courts. If you live in Northern Ireland, you can
                        bring legal proceedings in either the Northern Irish or English courts.
                    </p>
                </div>
            </div>
        </section>

    </div>

</div>

<?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>