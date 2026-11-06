<?php
/**
 * Privacy Policy for Delivery Partner App (Krish Partner / com.krishdelivery.krishdeliveryboy)
 * Used as the official Privacy Policy URL for Google Play Console.
 */
include_once('includes/crud.php');
$custom_content = '';
$app_name = 'Krish Partner';
$support_email = 'support@krishdelivery.com';
$support_phone = '+91 7402152219';

try {
    $db = new Database();
    $db->connect();

    // Query custom policy if updated by admin
    $db->sql("SELECT value FROM `settings` WHERE `variable` = 'delivery_boy_privacy_policy' LIMIT 1");
    $res = $db->getResult();
    if (!empty($res) && !empty($res[0]['value'])) {
        $custom_content = trim($res[0]['value']);
    }

    // Fetch dynamic contact details from settings if available
    $db->sql("SELECT value FROM `settings` WHERE `variable` = 'system_timezone' LIMIT 1");
    $s_res = $db->getResult();
    if (!empty($s_res) && !empty($s_res[0]['value'])) {
        $sys_data = @json_decode($s_res[0]['value'], true);
        if (!empty($sys_data['support_email'])) {
            $support_email = htmlspecialchars($sys_data['support_email']);
        }
        if (!empty($sys_data['support_number'])) {
            $support_phone = htmlspecialchars($sys_data['support_number']);
        }
    }
} catch (Exception $e) {
    // Graceful fallback if database connection is unavailable
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Krish Partner (Delivery Partner App)</title>
    <meta name="description" content="Privacy Policy for Krish Partner Delivery Boy App. Truthfully discloses data collection, foreground service location tracking, device permissions, and partner rights for Google Play compliance.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-light: #ecfdf5;
            --accent: #f59e0b;
            --accent-light: #fef3c7;
            --text-dark: #1f2937;
            --text-muted: #4b5563;
            --bg-body: #f9fafb;
            --bg-card: #ffffff;
            --border: #e5e7eb;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            line-height: 1.65;
            padding-bottom: 60px;
        }
        .header {
            background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%);
            color: #ffffff;
            padding: 40px 20px 45px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .header-content {
            max-width: 860px;
            margin: 0 auto;
        }
        .app-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(4px);
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        .header p {
            font-size: 15px;
            opacity: 0.92;
            max-width: 650px;
            margin: 0 auto;
        }
        .metadata-bar {
            margin-top: 18px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 13px;
            opacity: 0.88;
        }
        .metadata-bar span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .container {
            max-width: 900px;
            margin: -25px auto 0;
            padding: 0 20px;
        }
        .card {
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
            padding: 36px;
            margin-bottom: 24px;
        }
        .notice-box {
            background-color: var(--accent-light);
            border-left: 5px solid var(--accent);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0 30px;
            color: #78350f;
        }
        .notice-box h3 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #92400e;
        }
        .notice-box p {
            font-size: 14.5px;
            line-height: 1.6;
        }
        .policy-section {
            margin-bottom: 34px;
        }
        .policy-section:last-child {
            margin-bottom: 0;
        }
        h2 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin: 24px 0 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        h2 i {
            color: var(--primary);
            font-size: 19px;
        }
        h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin: 18px 0 8px;
        }
        p {
            margin-bottom: 12px;
            color: var(--text-muted);
            font-size: 15px;
        }
        ul {
            margin: 10px 0 16px 24px;
            color: var(--text-muted);
            font-size: 15px;
        }
        li {
            margin-bottom: 8px;
            line-height: 1.55;
        }
        li strong {
            color: var(--text-dark);
        }
        .highlight-card {
            background: var(--primary-light);
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 18px 22px;
            margin: 16px 0;
        }
        .highlight-card h4 {
            color: #065f46;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .highlight-card p {
            color: #047857;
            font-size: 14px;
            margin-bottom: 0;
        }
        .info-pill {
            display: inline-block;
            background: #e5e7eb;
            color: #374151;
            font-size: 12px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            margin-right: 6px;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .contact-item {
            background: #f9fafb;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .contact-item i {
            color: var(--primary);
            font-size: 20px;
        }
        .contact-item .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }
        .contact-item .val {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }
        .contact-item a {
            color: var(--primary-dark);
            text-decoration: none;
        }
        .contact-item a:hover {
            text-decoration: underline;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #9ca3af;
            font-size: 13px;
        }
        .footer a {
            color: var(--primary-dark);
            text-decoration: none;
        }
        .custom-policy-content {
            font-size: 15px;
            line-height: 1.7;
            color: var(--text-muted);
        }
        .custom-policy-content h1, 
        .custom-policy-content h2, 
        .custom-policy-content h3 {
            color: var(--text-dark);
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .custom-policy-content ul {
            margin-left: 20px;
            margin-bottom: 14px;
        }
        @media (max-width: 640px) {
            .header {
                padding: 30px 16px 36px;
            }
            .header h1 {
                font-size: 24px;
            }
            .card {
                padding: 22px 18px;
                border-radius: 10px;
            }
            h2 {
                font-size: 18px;
            }
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="header-content">
            <div class="app-badge">
                <i class="fa fa-shield-alt"></i> Official Google Play Policy
            </div>
            <h1>Privacy Policy for Krish Partner</h1>
            <p>Delivery Partner Application (<span style="font-family:monospace;font-size:13px;background:rgba(255,255,255,0.2);padding:2px 6px;border-radius:4px;">com.krishdelivery.krishdeliveryboy</span>)</p>
            <div class="metadata-bar">
                <span><i class="fa fa-building"></i> Krish Delivery</span>
                <span><i class="fa fa-calendar-check"></i> Effective Date: <?=date('F d, Y');?></span>
                <span><i class="fa fa-clock"></i> Last Updated: <?=date('F d, Y');?></span>
            </div>
        </div>
    </header>

    <main class="container">
        <article class="card">

            <?php if (!empty($custom_content)) { ?>
                <!-- Custom content saved via Admin Panel -->
                <div class="custom-policy-content">
                    <?=$custom_content;?>
                </div>
            <?php } else { ?>
                <!-- Default comprehensive Google Play Console compliant Privacy Policy -->

                <!-- PROMINENT DISCLOSURE (CRITICAL FOR GOOGLE PLAY APPROVAL) -->
                <section class="notice-box">
                    <h3><i class="fa fa-map-marker-alt"></i> PROMINENT DISCLOSURE: LIVE LOCATION DATA & FOREGROUND SERVICE USAGE</h3>
                    <p>
                        <strong>Krish Partner collects and processes precise GPS location data (latitude and longitude) to enable nearby delivery and ride order dispatching, calculate travel distance, display real-time live transit updates to customers and store merchants, and verify delivery pickup and drop-off points.</strong>
                    </p>
                    <p style="margin-top: 8px;">
                        This location tracking operates strictly through an Android <strong>Foreground Service (with a persistent notification titled <em>"You are Online - Sharing your live location so you can receive orders"</em>)</strong> while you are logged in and marked as <strong>"Online"</strong> on duty. 
                    </p>
                    <p style="margin-top: 8px;">
                        You have complete control: whenever you toggle your status to <strong>"Offline"</strong> or log out, all location tracking stops immediately. The app does not track your location when you are off duty, and your location data is never sold or used for advertising.
                    </p>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-info-circle"></i> 1. Introduction</h2>
                    <p>
                        Welcome to <strong>Krish Partner</strong> ("we," "our," "us," or "the App"), the official delivery partner mobile application operated by <strong>Krish Delivery</strong> (accessible at <a href="https://krishdelivery.com" target="_blank" style="color:var(--primary-dark);">https://krishdelivery.com</a>). This Privacy Policy explains truthfully and transparently what information we collect through the mobile app (Package Name: <code>com.krishdelivery.krishdeliveryboy</code>), how it is used, and how your privacy is protected.
                    </p>
                    <p>
                        By logging into and using the Krish Partner mobile application, you acknowledge and agree to the practices described in this Privacy Policy.
                    </p>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-user-check"></i> 2. Information We Collect Through the Mobile App</h2>
                    <p>To enable delivery operations, order assignments, and account management, the Krish Partner mobile app collects the following specific data:</p>

                    <h3>2.1 Account & Authentication Details</h3>
                    <ul>
                        <li><strong>Mobile Phone Number:</strong> Entered by you during login to authenticate your registered delivery partner account via One-Time Password (OTP).</li>
                        <li><strong>One-Time Password (OTP):</strong> Used for secure verification and session authorization.</li>
                        <li><strong>Firebase Cloud Messaging Token (FCM ID):</strong> Collected automatically to route instant real-time order alerts, status updates, and sound notifications to your device.</li>
                    </ul>

                    <h3>2.2 Partner Profile Information</h3>
                    <ul>
                        <li><strong>Name & Residential Address:</strong> Editable within the in-app Profile screen to keep your partner profile current.</li>
                        <li><strong>Profile Photo:</strong> You may optionally choose an image from your device's photo picker / gallery to display as your partner profile picture.</li>
                        <li><strong>Assigned Vehicle Details (Display Only):</strong> Your assigned vehicle type (e.g., Bike) and vehicle registration number are displayed in read-only mode from the server as registered by the system administrator.</li>
                    </ul>

                    <div class="highlight-card">
                        <h4><i class="fa fa-info-circle"></i> Note on Driver Onboarding & KYC Documentation:</h4>
                        <p>
                            Driver verification, driving license verification, and vehicle onboarding documents are verified directly with the Krish administrative team during account provisioning. The Krish Partner mobile application <strong>does not</strong> collect or ask you to upload Aadhaar cards, PAN cards, or bank account credentials inside the mobile app.
                        </p>
                    </div>

                    <h3>2.3 Real-Time Location Data</h3>
                    <ul>
                        <li><strong>GPS Coordinates (Latitude & Longitude):</strong> Collected while your duty status is set to "Online" via an active Android Foreground Service. Used to dispatch nearby food, parcel, or passenger ride requests, provide turn-by-turn navigation, and show your live arrival progress to the customer and store merchant.</li>
                        <li><strong>Order Query Coordinates:</strong> Sent when requesting new orders or parcel jobs within your operational radius.</li>
                    </ul>

                    <h3>2.4 Order & Delivery Execution Data</h3>
                    <ul>
                        <li><strong>Duty Status:</strong> Your toggle selection between "Online" (available for orders) and "Offline" (unavailable).</li>
                        <li><strong>Order Actions:</strong> Timestamps and status changes when you accept an order, confirm arrival at the pickup point, pick up items, and mark orders delivered.</li>
                        <li><strong>Delivery Verification OTP:</strong> For parcel deliveries and passenger ride starts, the delivery partner enters the customer's verification OTP to validate the handover.</li>
                        <li><strong>Cash Collection Confirmation:</strong> For cash-on-delivery orders or driver rides, recording the partner's confirmation of received payment.</li>
                    </ul>

                    <h3>2.5 Communication Data</h3>
                    <ul>
                        <li><strong>Phone Dialer Integration:</strong> When you tap the call button in the app to contact a customer or merchant for delivery directions, the app opens your phone's default dialer with the phone number pre-filled. Krish does not record or store your phone calls.</li>
                    </ul>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-mobile-alt"></i> 3. Device Permissions & Why They Are Requested</h2>
                    <p>The Krish Partner Android app requests only the permissions necessary for delivery logistics:</p>
                    <ul>
                        <li><strong><span class="info-pill">ACCESS_FINE_LOCATION & ACCESS_COARSE_LOCATION</span> Precise & Approximate Location:</strong> Required to locate you for order assignment, navigation, and live transit updates.</li>
                        <li><strong><span class="info-pill">FOREGROUND_SERVICE & FOREGROUND_SERVICE_LOCATION</span> Foreground Service Location:</strong> Required by Android to keep location synchronization active while you are "Online" on duty, accompanied by an ongoing notification.</li>
                        <li><strong><span class="info-pill">POST_NOTIFICATIONS</span> Push Notifications:</strong> Required on Android 13+ to alert you of incoming order requests, sound alerts, and delivery status updates.</li>
                        <li><strong><span class="info-pill">INTERNET</span> Full Network Access:</strong> Required to communicate with Krish Delivery backend servers and load Google Maps tiles.</li>
                        <li><strong><span class="info-pill">CALL_PHONE</span> Phone Calls:</strong> Enables quick calling to customers or merchants directly from the order screen when delivery coordination is needed.</li>
                        <li><strong><span class="info-pill">WAKE_LOCK</span> Device Awake:</strong> Prevents the processor from sleeping during active location streaming while on duty.</li>
                    </ul>

                    <p><strong>Permissions We DO NOT Request or Use:</strong></p>
                    <ul>
                        <li>We <strong>do not</strong> request <code>ACCESS_BACKGROUND_LOCATION</code> (the app tracks duty location strictly via visible foreground service).</li>
                        <li>We <strong>do not</strong> request <code>CAMERA</code> permission (profile photos are picked from your photo gallery/picker).</li>
                        <li>We <strong>do not</strong> access contacts, SMS, microphone, calendar, or external storage files.</li>
                    </ul>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-cogs"></i> 4. How We Use the Collected Information</h2>
                    <p>Information collected via the mobile app is used strictly for the following purposes:</p>
                    <ul>
                        <li><strong>Order Dispatch & Routing:</strong> Matching incoming customer orders with the nearest online delivery partner to minimize travel time.</li>
                        <li><strong>Live Transit Tracking:</strong> Displaying your first name and live map position to the waiting customer and merchant during an active delivery.</li>
                        <li><strong>Order Verification:</strong> Ensuring accurate parcel handover and ride completion using one-time verification codes (OTP).</li>
                        <li><strong>Settlement & Earnings Calculation:</strong> Tracking completed delivery runs, ride history, and cash collections for driver payout settlements.</li>
                        <li><strong>Account Administration & Security:</strong> Preventing unauthorized access and verifying delivery partner identity.</li>
                    </ul>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-share-alt"></i> 5. Data Sharing & Third-Party Service Providers</h2>
                    <p>We do not sell, rent, or monetize your personal data. We share information only in the following necessary contexts:</p>
                    <ul>
                        <li><strong>With Customers and Merchants:</strong> During an active order transit only, the customer and store merchant can see your first name, phone number, vehicle type, and live location on the map. This sharing automatically stops once the order is marked delivered or cancelled.</li>
                        <li><strong>Google Maps SDK (Google LLC):</strong> Used to render maps and calculate delivery routes. Subject to <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Privacy Policy</a>.</li>
                        <li><strong>Firebase Cloud Messaging (Google LLC):</strong> Used to deliver push notifications and incoming order alerts.</li>
                        <li><strong>Legal & Regulatory Authorities:</strong> If required by valid legal process, statutory requirements, or law enforcement investigations.</li>
                    </ul>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-lock"></i> 6. Data Security & Storage</h2>
                    <p>
                        We implement appropriate technical and administrative safeguards to protect your data. All communication between the Krish Partner app and our servers is encrypted using Transport Layer Security (TLS/HTTPS). Partner accounts are secured with OTP verification, and access to location records is restricted to authorized operations personnel.
                    </p>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-hourglass-half"></i> 7. Data Retention</h2>
                    <p>
                        We retain your delivery partner profile information as long as your account remains registered with Krish Delivery. Live GPS coordinates are retained only as long as needed for order fulfillment, dispute resolution, and settlement audits, after which historical raw coordinates are purged. Completed order delivery logs are retained for statutory accounting records.
                    </p>
                </section>

                <!-- ACCOUNT & DATA DELETION (CRITICAL FOR GOOGLE PLAY POLICY) -->
                <section class="policy-section">
                    <h2><i class="fa fa-user-times"></i> 8. Account Deactivation & Data Deletion Policy</h2>
                    <p>
                        Delivery partners have the right to request the deletion of their account and associated personal data at any time.
                    </p>
                    
                    <div class="highlight-card">
                        <h4><i class="fa fa-trash-alt"></i> How to Request Account & Data Deletion:</h4>
                        <p>
                            To permanently delete your Krish Partner account, send an email from your registered email or contact our support team at <strong><a href="mailto:<?=$support_email;?>?subject=Delete%20Delivery%20Partner%20Account" style="color:#065f46;text-decoration:underline;"><?=$support_email;?></a></strong> with the subject <em>"Delete Delivery Partner Account"</em>. Please mention your registered mobile number and full name.
                        </p>
                    </div>

                    <p><strong>Upon confirmation of your deletion request:</strong></p>
                    <ul>
                        <li>Your delivery partner account will be deactivated and logged out.</li>
                        <li>Your personal profile data, profile image, and location records will be permanently removed from active databases within <strong>30 days</strong>.</li>
                        <li>Completed financial payout transaction ledgers will be retained strictly in accordance with statutory accounting and tax regulations.</li>
                    </ul>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-child"></i> 9. Eligibility & Age Restriction</h2>
                    <p>
                        The Krish Partner application is exclusively intended for independent adult delivery personnel who are <strong>at least 18 years of age</strong> and legally licensed to operate a motor vehicle. We do not register or collect information from minors.
                    </p>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-sync-alt"></i> 10. Changes to This Privacy Policy</h2>
                    <p>
                        We may update this Privacy Policy periodically to reflect app updates or regulatory adjustments. Any updates will be published directly on this page with an updated "Last Updated" date.
                    </p>
                </section>

                <section class="policy-section">
                    <h2><i class="fa fa-envelope-open-text"></i> 11. Contact Information</h2>
                    <p>
                        For any questions, concerns, or data requests regarding this Privacy Policy, please contact us:
                    </p>

                    <div class="contact-grid">
                        <div class="contact-item">
                            <i class="fa fa-building"></i>
                            <div>
                                <div class="label">Platform</div>
                                <div class="val">Krish Delivery</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fa fa-envelope"></i>
                            <div>
                                <div class="label">Support Email</div>
                                <div class="val"><a href="mailto:<?=$support_email;?>"><?=$support_email;?></a></div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fa fa-phone"></i>
                            <div>
                                <div class="label">Helpline Number</div>
                                <div class="val"><a href="tel:<?=$support_phone;?>"><?=$support_phone;?></a></div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fa fa-globe"></i>
                            <div>
                                <div class="label">Official Website</div>
                                <div class="val"><a href="https://krishdelivery.com" target="_blank">krishdelivery.com</a></div>
                            </div>
                        </div>
                    </div>
                </section>

            <?php } ?>

        </article>

        <footer class="footer">
            <p>&copy; <?=date('Y');?> Krish Delivery. All rights reserved. | <a href="play-store-privacy-policy.php">Customer App Privacy Policy</a> | <a href="delivery-partner-privacy-policy.php">Partner App Privacy Policy</a></p>
        </footer>
    </main>

</body>
</html>
