<?php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$faqs = [
    [
        'question' => 'Is DesParking part of the British Parking Association?',
        'answer' => 'Yes, DesParking is part of the British Parking Association'
    ],
    [
        'question' => 'Is DesParking partnered with KeyNest?',
        'answer' => 'Yes, we are proud partners with KeyNest. Hosts and drivers can use our code "desparking" for discounts on the KeyNest app.'
    ],
    [
        'question' => 'Does DesParking have an app?',
        'answer' => 'No DesParking does not have an app, we are a web-app. We may consider building an app in the future.'
    ],
    [
        'question' => 'What is DesParking contact number?',
        'answer' => 'Our phone number is: 0330 043 5623'
    ],
    [
        'question' => 'What Should I Do Before I Arrive?',
        'answer' => "Once you arrive at the parking space, DesParking and get on with your day!
Here are two things you should do in advance to ensure a smooth experience:
1. Read Your Confirmation Email
Your confirmation email contains everything you need to know about your booking, including:
Specific instructions on how to find and access the parking space
Your booking ID and the times of your reservation
The space owner's contact details
A security code or details on how to collect a key fob if required
2. Plan Your Journey
Check for potential travel disruptions such as traffic delays, road closures, or congestion charges to avoid unnecessary stress on the day of your booking."
    ],
    [
        'question' => 'I received a PCN/Parking ticket what shall I do?',
        'answer' => 'Contact us immediately on support@desparking.uk so that we can contact the host and investigate this matter. We also advice appealing it immediately if you feel you have followed the correct procedures and not at fault.'
    ],
    [
        'question' => 'What is DesParking fee?',
        'answer' => "

Fees for DesParking

• Listing fees
There is no fee to list a parking space on DesParking.

• Commission
DesParking adds a 19% commission to the price set by the space owner. This commission is transferred to the driver.

• Processing fee
DesParking deducts a 2% processing fee from the space owner’s earnings.

• Service fee
DesParking adds a service fee to the booking total to cover the costs of running the platform. The service fee is between £0.45 and £2.30, and the exact amount is shown at checkout.

• Additional fees
There may be additional fees if the booking is extended or if the space owner offers additional services, such as EV charging or a lift to the airport.

The price shown on the DesParking website is the price the driver will pay to book the parking space. There are no hidden costs or additional fees to pay on the day of the booking.
"
    ],
    [
        'question' => 'There is a car in my space, what do I do?',
        'answer' => 'Contact the space owner immediately and contact us on support@desparking.uk to make us aware of the situation and we will take the necessary action.'
    ],
    [
        'question' => 'How to Cancel Your Booking with DesParking?',
        'answer' => "The cancellation process varies depending on whether your booking is for hourly/daily use or a long-term (monthly) stay. You should contact us on support@desparking.uk immediately.
❗ Important: Your 30-day notice period begins on the day you submit your cancellation request. Depending on when your notice is submitted relative to your billing date, your final month's rent will be calculated on a pro-rata basis and may be equal to or less than your usual monthly charge."
    ],
    [
        'question' => 'How do I offer extras, such as airport transfers?',
        'answer' => "
When listing your parking space, you can select checkboxes for any additional services you offer, such as airport transfers or car washes.

Simply choose the applicable extras and include any relevant details, such as available dates and times for airport transfers, in the description.

For added clarity, you can specify the price for any additional services, such as airport transfers, in the Additional Post-Booking Instructions. Please note that prices cannot be listed in the main description of your parking listing.
"
    ],
    [
        'question' => 'Will I have an allocated parking space?',
        'answer' => "
Whether or not you are provided with an allocated parking space depends on the location you have booked.

In residential car parks, allocated parking bays are typically available. However, at some commercial locations, you may be directed to park in a designated area, such as 'Level 2' of the car park.

Most locations on our platform operate on a 'first-come, first-served' basis, meaning you can park in any available bay, excluding those reserved for parent & child spaces or blue badge holders without valid permission.

If your booking involves a specific parking bay, these instructions will be included in your post-booking email and will also be accessible via your account until the booking is completed.
"
    ],
    [
        'question' => 'Do I need to print my booking confirmation?',
        'answer' => "
No, printing your booking confirmation is not required. None of our space providers need a physical copy.

Once you make a booking, a confirmation email with all relevant details will be sent to you and the driver. You can also access your booking information anytime via your account. However, please note that full booking details will only be available until the booking has been completed.
"
    ],
    [
        'question' => 'What happens if I lose the access equipment provided to me?',
        'answer' => "
While this is uncommon, it can happen. To cover such situations, we hold a deposit for any booking that requires access equipment.

If the equipment is lost or damaged, the replacement fee will apply, and your booking may be suspended until a replacement is provided. If you lose the access equipment during your booking, you will need to top up your deposit to the equivalent of a full month's rent in order for the booking to continue.
"
    ],
    [
        'question' => 'Will I get the same parking rates if I don’t book online?',
        'answer' => "Likely not. Without a DesParking booking, you risk paying higher drive-up rates, and your parking space is not guaranteed. This could lead to wasted time and fuel searching for alternative spaces. Additionally, please note that 90% of our parking spaces are exclusively available for online booking via our website."
    ],
    [
        'question' => 'Do I need to arrive and leave exactly at the times listed in my booking?',
        'answer' => 'Not necessarily. You have the flexibility to arrive or leave slightly outside the specified times, as long as you stay within the general booking window. For instance, if your booking is from 7am to 7pm, you can arrive later, say at 9am, and leave earlier, such as at 4pm. Just be sure not to exceed the booking period.'
    ],
    [
        'question' => 'Is my booking guaranteed once confirmed?',
        'answer' => 'Yes, once your booking is confirmed, it is guaranteed. You will receive a confirmation both in your account and via email. When you arrive to park, simply follow the "How to Access" instructions provided. Rest assured, you are protected by our booking guarantee.'
    ],
    [
        'question' => 'How can I change my email address?',
        'answer' => "
For security reasons, we do not allow users to change their email address directly from their account.

If you need to update your email address, please contact our support team support@desparking.uk. Be sure to provide your current email address and the new email you'd like to use. Our team will process your request and confirm once the change has been made.
"
    ],
    [
        'question' => 'How do I close my account?',
        'answer' => "
We're sorry to hear that you no longer require our services. If you wish to close your DesParking account, please get in touch with us on support@desparking.uk, and our support team will assist you with the process.

Please note that you will not be able to close your account if there is an active booking linked to it.
"
    ],
    [
        'question' => 'Is the DesParking website secure?',
        'answer' => "
Yes, the DesParking website is fully secure. We use HTTPS across the entire site to ensure your data is encrypted between your browser and our servers, providing maximum protection for your information.

To further enhance the security of your account, we recommend using a strong password that includes a combination of uppercase and lowercase letters, numbers, and special characters. Please remember to keep your password confidential and never share it with anyone.

Additionally, ensure that your computer and browser's security features are kept up to date, and avoid saving your details when using public or shared computers.
"
    ],
    [
        'question' => 'How can I contact DesParking customer support for assistance?',
        'answer' => '
Our customer support team is available 24/7 to assist you with any questions or concerns. You can reach us by email at support@desparking.uk.

Tel: 03300435623 - Urgent issues

Alternatively, you can use the contact form on our website to send us a message, and we’ll get back to you promptly.
'
    ],
    [
        'question' => 'What amenities do DesParking facilities offer?',
        'answer' => "
Our facilities are meticulously designed not only to ensure accessibility and security but also to enhance the overall parking experience for our customers.

Depending on the location, you can expect a range of amenities that add convenience and comfort to your visit. These amenities may include covered parking options to protect your vehicle from the elements, EV charging stations for electric vehicles, and convenient car wash services to keep your vehicle looking its best.

We continuously strive to offer a comprehensive range of services that cater to your needs, making your parking experience with DesParking both comfortable and convenient.
"
    ],
    [
        'question' => 'Are DesParking facilities accessible for individuals with disabilities?',
        'answer' => '
DesParking is dedicated to providing fully accessible parking facilities that comply with all relevant accessibility standards. Our spaces are designed to ensure equal access for individuals with disabilities, including conveniently located accessible parking spots and clear, obstacle-free pathways.

Our trained staff is also available to provide assistance as needed, ensuring a welcoming and inclusive experience for all customers.
'
    ],
    [
        'question' => 'What are your parking rates?',
        'answer' => "
Parking rates at DesParking are flexible and tailored to accommodate various needs, taking into account factors such as location, duration of stay, and any ongoing promotions. For detailed information regarding our parking rates, we encourage you to visit our website, where you can easily access comprehensive pricing details for each location.

Additionally, our dedicated customer support team is readily available to assist you with any inquiries you may have regarding parking rates or to provide personalised recommendations based on your specific requirements.

Whether you’re planning a short-term visit or require long-term parking solutions, we strive to offer transparent pricing information to ensure that you can make informed decisions effortlessly.
"
    ],
    [
        'question' => 'Are DesParking facilities secure?',
        'answer' => '
Certainly! Ensuring the safety and security of our customers and their vehicles is our top priority at DesParking. Each of our facilities is outfitted with cutting-edge security systems that include comprehensive surveillance cameras and well-lit surroundings.

These measures are designed to provide a secure parking environment, giving you peace of mind knowing that your vehicle is protected at all times.

Our commitment to safety extends beyond technology; our dedicated staff also undergo rigorous training to maintain a vigilant presence and assist customers whenever needed. Your safety and the security of your vehicle are always our foremost concerns.
'
    ],
    [
        'question' => 'What payment methods do you accept?',
        'answer' => '
We accept a wide range of payment methods to ensure a convenient and seamless payment experience for our customers.

You can pay using major credit cards, debit cards, and popular mobile payment options such as Apple Pay and Google Pay.

This flexibility allows you to choose the payment method that best suits your preferences, making the process quick and hassle-free. Whether you prefer traditional payment methods or the latest mobile technologies, we’ve got you covered.
'
    ],
    [
        'question' => 'Can I reserve a parking spot in advance?',
        'answer' => "
Certainly! Reserving your parking spot in advance is a breeze with our easy-to-use website. Just visit our site, choose your preferred location, select the date and time you need, and complete the reservation process.

You’ll have peace of mind knowing your parking spot is secured, ensuring a hassle-free experience when you arrive.
"
    ],
    [
        'question' => 'How do I find a DesParking facility near me?',
        'answer' => 'Locating a DesParking facility is simple! Visit our booking facilities or contact our customer support team to find the nearest parking facility to your destination.'
    ]
];
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>DesParking</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

    <link href="./css/output.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

<body class="min-h-screen bg-white">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- HERO / SECTION 1 -->
    <section id="section-1" class="relative bg-white overflow-hidden pt-48 pb-32">

        <!-- Inner content -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 items-start bg-[url('/images/desparking-business-header.jpg')] bg-cover bg-center rounded-lg ">

            <!-- LEFT BOX -->
            <div class="bg-white w-full h-full px-10 py-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-6">Frequently Asked Questions</h1>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
            </div>

            <!-- RIGHT BOX -->
            <div class="w-full h-full">
                <div class="w-1/4 h-1/2 bg-white"></div>
            </div>
        </div>
    </section>

    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16">
        <div class="max-w-7xl mx-auto px-10 flex flex-col items-start">
            <div class="space-y-6" id="faq">
                <?php foreach ($faqs as $faq) : ?>
                    <!-- FAQ Item -->
                    <div class="faq-item border-b border-[#6ae6fc] pb-4">
                        <button
                            class="faq-toggle w-full flex justify-between items-center text-left text-lg font-semibold text-gray-900 py-4">
                            <?= $faq['question'] ?>
                            <svg
                                class="faq-icon w-5 h-5 transition-transform duration-300 origin-center"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 text-gray-600">
                            <p class="pb-2">
                                <?= nl2br($faq['answer']) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            </div>
        </div>
    </section>
    
    <script>
        document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const item = button.parentElement;
                const content = item.querySelector('.faq-content');
                const icon = button.querySelector('.faq-icon');

                // Close others (accordion style)
                document.querySelectorAll('.faq-item').forEach(other => {
                    if (other !== item) {
                        other.querySelector('.faq-content').style.maxHeight = null;
                        other.querySelector('.faq-icon').classList.remove('rotate-180');
                    }
                });

                // Toggle current
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                    icon.classList.remove('rotate-180');
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                    icon.classList.add('rotate-180');
                }
            });
        });
    </script>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>