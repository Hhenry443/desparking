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
        Privacy Policy
    </h1>

    <div class="space-y-10 text-sm leading-relaxed text-gray-700">

        <!-- 1 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Introduction</h2>

            <p>
                This privacy notice provides details of how we collect and process your personal data through your use of our site
                <a href="https://desparking.uk/" class="text-cyan-600 font-medium hover:underline">
                    https://desparking.uk/
                </a>.
            </p>

            <p class="mt-4">
                By providing us with your data, you warrant that you are over 13 years of age.
            </p>

            <p class="mt-4">
                DesParking is operated by <strong>Everyonesparking Limited</strong>, the data controller responsible for your personal data
                (referred to as “we”, “us” or “our” in this notice).
            </p>

            <div class="mt-6 bg-white border border-gray-200 p-6 rounded-xl">
                <p class="font-semibold mb-2">Contact Details</p>
                <p>Email: support@desparking.uk</p>
                <p>Postal Address: Flat 71 Discovery Dock Apartments East, South Quay Square, London, England, E14 9RU</p>
            </div>

            <p class="mt-4">
                It is important that the information we hold about you is accurate and up to date.
                Please notify us of any changes by emailing support@desparking.uk.
            </p>
        </section>

        <!-- 2 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                2. What Data We Collect, Why We Collect It, and Legal Grounds
            </h2>

            <p>
                Personal data means any information capable of identifying an individual. It does not include anonymised data.
            </p>

            <div class="mt-6 space-y-6">

                <div>
                    <h3 class="font-semibold text-gray-900">Communication Data</h3>
                    <p>
                        Includes communications sent via forms, email, text, social media or other channels.
                        Processed for communication, record keeping and legal claims.
                        Lawful basis: legitimate interests.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Customer Data</h3>
                    <p>
                        Includes name, billing/delivery address, contact details, purchase and transaction data.
                        Processed to fulfil contracts and maintain transaction records.
                        Lawful basis: performance of contract.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">User Data</h3>
                    <p>
                        Includes website usage behaviour and content posted.
                        Used to operate, secure and administer the website.
                        Lawful basis: legitimate interests.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900">Technical Data</h3>
                    <p>
                        Includes IP address, login data, browser details, page visits and analytics data.
                        Used for analysis, security and marketing effectiveness.
                        Lawful basis: legitimate interests.
                    </p>
                </div>
            </div>

            <h3 class="font-semibold text-gray-900 mt-8">Sensitive Data</h3>
            <p>
                We do not collect sensitive personal data or information relating to criminal convictions.
            </p>

            <div>
              <p class="mt-4">
                  Where we are required to collect personal data by law, or under the terms of the contract between us and you do not provide us with that data when requested, we may not be able to perform the contract (for example, to deliver goods or services to you). If you don't provide us with the requested data, we may have to cancel a product or service you have ordered but if we do, we will notify you at the time.
              </p>
              <p class="mt-4">
                  We will only use your personal data for a purpose it was collected for or a reasonably compatible purpose if necessary. In case we need to use your details for an unrelated new purpose we will let you know and explain the legal grounds for processing. We may process your personal data without your knowledge or consent where this is required or permitted by law. We do not carry out automated decision making or any type of automated profiling.
              </p>
            </div>

        </section>

        <!-- 3 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                3. How We Collect Your Personal Data
            </h2>

            <ul class="list-disc pl-6 space-y-2">
                <li>Directly from you (forms, emails, account registration)</li>
                <li>Automatically via cookies and analytics tools</li>
                <li>From third parties such as Google Analytics or Facebook</li>
                <li>From public sources such as Companies House</li>
            </ul>
        </section>

        <!-- 4 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                4. Marketing Communications
            </h2>

            <p>
                We may send marketing communications based on your consent or legitimate interests.
                You may opt out at any time by clicking unsubscribe links or emailing support@desparking.uk.
            </p>
        </section>

        <!-- 5 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                5. Disclosure of Your Personal Data
            </h2>

            <ul class="list-disc pl-6 space-y-2">
                <li>IT and system administration providers</li>
                <li>Professional advisers</li>
                <li>Government bodies</li>
                <li>Professional service providers</li>
                <li>Business sale or merger parties</li>
            </ul>
        </section>

        <!-- 6 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                6. International Transfers
            </h2>

            <p>
                Countries outside of the European Economic Area (EEA) do not always offer the same levels of protection to your personal data, so European law has prohibited transfers of personal data outside of the EEA unless the transfer meets certain criteria. Whenever we transfer your personal data out of the EEA, we ensure at least one of the following safeguards is in place:
            </p>
            <ul class="list-disc pl-6 space-y-2 mt-4">
                <li>We will only transfer your personal data to countries that the European Commission have approved as providing an adequate level of protection; or</li>
                <li>Where we use certain service providers, we may use specific contracts or codes of conduct or certification mechanisms approved by the European Commission which give personal data the same protection it has in Europe; or</li>
                <li>If we use US-based providers that are part of EU-US Privacy Shield, we may transfer data to them, as they have equivalent safeguards in place.</li>
            </ul>
            <p class="mt-4">
                If none of the above safeguards is available, we may request your explicit consent to the specific transfer. You will have the right to withdraw this consent at any time.
            </p>
        </section>

        <!-- 7 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                7. Data Retention
            </h2>

            <p>
                We retain personal data only as long as necessary for legal, accounting and reporting purposes.
                For tax purposes, certain data is retained for six years.
            </p>
        </section>

        <!-- 8 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                8. Your Legal Rights
            </h2>

            <p>
                You have rights including access, correction, erasure, restriction, objection and portability.
                See:
                <a href="https://ico.org.uk/" class="text-cyan-600 font-medium hover:underline">
                    ico.org.uk
                </a>.
            </p>

            <p class="mt-4">
                To exercise your rights, email support@desparking.uk.
            </p>
        </section>

        <!-- 9 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                9. Third-Party Links
            </h2>

            <p>
                We are not responsible for privacy policies of third-party websites linked from our site.
            </p>
        </section>

        <!-- 10 -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                10. Cookies
            </h2>

            <p>
                You can disable cookies in your browser settings.
                For full details, see our Cookie Policy.
            </p>
        </section>

    </div>

</div>

<?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>