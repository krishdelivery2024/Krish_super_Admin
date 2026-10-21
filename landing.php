<?php
    include_once('includes/crud.php');
    $db = new Database();
    $db->connect();
    include_once('includes/custom-functions.php');
    $fn = new custom_functions();
    date_default_timezone_set('Asia/Kolkata');

    $settings = $fn->get_settings('system_timezone', true);
    if (!is_array($settings)) {
        $settings = array();
    }

    $setting_value = function ($key, $default = '') use ($settings) {
        return (isset($settings[$key]) && trim($settings[$key]) !== '') ? $settings[$key] : $default;
    };

    $app_name       = $setting_value('app_name', 'Krish Delivery');
    $support_number = $setting_value('support_number');
    $support_email  = $setting_value('support_email');
    $whatsapp       = $setting_value('whatsapp_number');
    $store_address  = $setting_value('address');

    // Dynamic Logo (works local & production)
    $logo = $fn->get_settings('logo');
    $logo_url = !empty($logo) ? 'dist/img/' . $logo : 'dist/img/logo.png';

    // Social Media Links & Visibility
    $social_links = array(
        'facebook' => array(
            'title'   => 'Facebook',
            'icon'    => 'fab fa-facebook-f',
            'url'     => $setting_value('facebook_url'),
            'enabled' => $setting_value('facebook_status') === '1',
            'class'   => 'facebook',
        ),
        'instagram' => array(
            'title'   => 'Instagram',
            'icon'    => 'fab fa-instagram',
            'url'     => $setting_value('instagram_url'),
            'enabled' => $setting_value('instagram_status') === '1',
            'class'   => 'instagram',
        ),
        'youtube' => array(
            'title'   => 'YouTube',
            'icon'    => 'fab fa-youtube',
            'url'     => $setting_value('youtube_url'),
            'enabled' => $setting_value('youtube_status') === '1',
            'class'   => 'youtube',
        ),
    );
    $active_socials = array_filter($social_links, function ($s) {
        return $s['enabled'] && !empty($s['url']);
    });

    // WhatsApp Floating Button Settings
    $show_whatsapp_float = $setting_value('show_whatsapp_floating_btn', '1') === '1';
    $whatsapp_float_msg  = $setting_value('whatsapp_floating_msg', 'Hello Krish Delivery, I have an inquiry!');

    // Apps download links
    $apps = array(
        array(
            'name'  => 'Customer App',
            'desc'  => 'Order from nearby stores and get everything delivered to your doorstep.',
            'icon'  => 'fa-bag-shopping',
            'play'  => $setting_value('customer_play_store_url'),
            'ios'   => $setting_value('customer_app_store_url'),
        ),
        array(
            'name'  => 'Seller App',
            'desc'  => 'Register your store, manage products and orders on the go.',
            'icon'  => 'fa-store',
            'play'  => $setting_value('seller_play_store_url'),
            'ios'   => $setting_value('seller_app_store_url'),
        ),
        array(
            'name'  => 'Delivery Boy App',
            'desc'  => 'Accept delivery requests and track your earnings in real time.',
            'icon'  => 'fa-motorcycle',
            'play'  => $setting_value('delivery_play_store_url'),
            'ios'   => $setting_value('delivery_app_store_url'),
        ),
    );

    // Dynamic Services (fall back to defaults)
    $services = array(
        array('icon' => 'fa-utensils',        'title' => 'Food Delivery',            'desc' => 'Fresh meals from your favourite local restaurants, delivered hot and fast.'),
        array('icon' => 'fa-basket-shopping', 'title' => 'Grocery & Daily Needs',    'desc' => 'Fruits, vegetables, dairy and daily essentials from stores near you.'),
        array('icon' => 'fa-shop',            'title' => 'Online Shopping',          'desc' => 'A multi-vendor marketplace with a wide range of products at great prices.'),
        array('icon' => 'fa-box-open',        'title' => 'Parcel Delivery',          'desc' => 'Send and receive parcels quickly with safe, reliable doorstep pickup and drop.'),
        array('icon' => 'fa-location-dot',    'title' => 'Nearby Stores',            'desc' => 'Discover the best rated shops around you and get hyperlocal same-day delivery.'),
        array('icon' => 'fa-credit-card',     'title' => 'Easy Payments',            'desc' => 'Pay the way you like - cash on delivery, wallet, UPI or online payment.'),
        array('icon' => 'fa-store',           'title' => 'Store Pickup',             'desc' => 'Order online and pick up your items directly from the store at your convenience.'),
        array('icon' => 'fa-truck-fast',      'title' => 'Live Order Tracking',      'desc' => 'Follow your order at every step from the store to your doorstep.'),
        array('icon' => 'fa-gift',            'title' => 'Offers & Rewards',         'desc' => 'Save more with daily offers, coupons and a refer & earn rewards program.'),
    );

    $saved_services = $setting_value('landing_services');
    if (!empty($saved_services)) {
        $decoded_services = json_decode($saved_services, true);
        if (is_array($decoded_services) && !empty($decoded_services)) {
            $services = $decoded_services;
        }
    }

    $escape = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($app_name) ?> - Delivery Made Simple</title>
    <?php if (!empty($logo_url)) { ?>
    <link rel="icon" type="image/png" href="<?= $escape($logo_url) ?>">
    <?php } ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #3F51B5;
            --primary-dark: #303F9F;
            --accent: #FF6D00;
            --ink: #1c2033;
            --muted: #6b7280;
            --bg: #f6f7fb;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }
        .container { width: 100%; max-width: 1140px; margin: 0 auto; padding: 0 20px; }

        header.site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #eceef5;
        }
        .nav { display: flex; align-items: center; justify-content: space-between; height: 72px; }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 800; font-size: 20px; }
        .brand img { height: 42px; width: auto; max-width: 160px; object-fit: contain; }
        .brand .brand-badge {
            width: 42px; height: 42px; border-radius: 12px; display: inline-flex;
            align-items: center; justify-content: center; color: #fff; font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
        }
        .nav-links { display: flex; align-items: center; gap: 24px; }
        .nav-links a { font-weight: 600; font-size: 15px; color: #3a3f52; transition: color .15s ease; }
        .nav-links a:hover { color: var(--primary); }
        .nav-toggle { display: none; background: none; border: 0; font-size: 22px; color: var(--ink); cursor: pointer; }

        .hero {
            background: linear-gradient(135deg, #3F51B5 0%, #5C6BC0 55%, #FF6D00 140%);
            color: #fff;
            padding: 84px 0 96px;
            position: relative;
            overflow: hidden;
        }
        .hero::after {
            content: "";
            position: absolute; right: -120px; top: -120px;
            width: 420px; height: 420px; border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .hero-grid { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 48px; align-items: center; position: relative; z-index: 1; }
        .hero h1 { font-size: 46px; line-height: 1.15; margin: 0 0 18px; font-weight: 800; }
        .hero p.lead { font-size: 18px; color: rgba(255,255,255,0.9); margin: 0 0 30px; max-width: 520px; }
        .hero-cta { display: flex; gap: 16px; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 10px; padding: 14px 26px;
            border-radius: 999px; font-weight: 700; font-size: 15px; cursor: pointer;
            border: 0; transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-white { background: #fff; color: var(--primary); box-shadow: 0 10px 24px rgba(0,0,0,0.12); }
        .btn-outline { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,0.6); }
        .btn-outline:hover { background: rgba(255,255,255,0.12); border-color: #fff; }

        .hero-badges { display: flex; gap: 32px; margin-top: 40px; padding-top: 28px; border-top: 1px solid rgba(255,255,255,0.18); }
        .hero-badges .stat b { display: block; font-size: 24px; font-weight: 800; }
        .hero-badges .stat span { font-size: 13px; color: rgba(255,255,255,0.8); }

        .hero-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .hero-card h3 { margin: 0 0 18px; font-size: 22px; }
        .hero-card ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 14px; }
        .hero-card li { display: flex; align-items: center; gap: 12px; font-size: 15px; color: rgba(255,255,255,0.92); }
        .hero-card li i { color: #FFE082; font-size: 18px; }

        section { padding: 80px 0; }
        .section-head { text-align: center; max-width: 680px; margin: 0 auto 56px; }
        .section-head .eyebrow {
            display: inline-block; font-weight: 700; font-size: 13px; text-transform: uppercase;
            letter-spacing: 1.5px; color: var(--accent); margin-bottom: 10px;
        }
        .section-head h2 { font-size: 36px; margin: 0 0 14px; font-weight: 800; }
        .section-head p { font-size: 16px; color: var(--muted); margin: 0; }

        .grid { display: grid; gap: 24px; }
        .grid-4 { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); }
        .grid-3 { grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); }

        .service {
            background: #fff;
            border-radius: 18px;
            padding: 28px;
            border: 1px solid #eceef5;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .service:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(63,81,181,0.08); }
        .service .icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: #eef1fc; color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 18px;
        }
        .service h3 { font-size: 18px; margin: 0 0 10px; font-weight: 700; }
        .service p { font-size: 14px; color: var(--muted); margin: 0; line-height: 1.55; }

        .app-card {
            background: #fff;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid #eceef5;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .app-card:hover { transform: translateY(-4px); box-shadow: 0 18px 36px rgba(0,0,0,0.06); }
        .app-card .icon {
            width: 58px; height: 58px; border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff; display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 18px;
        }
        .app-card h3 { font-size: 20px; margin: 0 0 10px; font-weight: 800; }
        .app-card p { font-size: 14px; color: var(--muted); margin: 0 0 24px; }
        .store-btns { display: flex; flex-direction: column; gap: 10px; margin-top: auto; }
        .store-btn {
            display: inline-flex; align-items: center; gap: 12px; padding: 10px 18px;
            background: #111827; color: #fff; border-radius: 12px;
            font-size: 14px; font-weight: 600; text-decoration: none;
            transition: background .15s ease, transform .15s ease;
        }
        .store-btn:hover { background: #1f2937; transform: translateY(-1px); }
        .store-btn i { font-size: 22px; }
        .store-btn span { display: flex; flex-direction: column; line-height: 1.2; text-align: left; }
        .store-btn span small { font-size: 10px; font-weight: 500; opacity: 0.8; text-transform: uppercase; }
        .store-btn.ios { background: #000; }
        .coming-soon {
            display: inline-flex; align-items: center; gap: 8px; font-size: 13px;
            color: var(--muted); background: #f3f4f6; padding: 10px 16px; border-radius: 10px;
            font-weight: 600;
        }

        #contact { background: #fff; }
        .contact-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        .contact-card {
            background: var(--bg); border: 1px solid #eef1f6;
            border-radius: 16px; padding: 24px; display: flex; gap: 16px; align-items: flex-start;
            transition: transform .2s ease;
        }
        .contact-card:hover { transform: translateY(-2px); }
        .contact-card .icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: #eef1fc; color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .contact-card h4 { margin: 0 0 6px; font-size: 15px; font-weight: 700; }
        .contact-card a, .contact-card p { margin: 0; font-size: 14px; color: var(--muted); word-break: break-word; }
        .contact-card a:hover { color: var(--primary); }

        /* Social Media Icons */
        .social-bar {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .social-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff !important;
            font-size: 16px;
            transition: transform .2s ease, box-shadow .2s ease;
            text-decoration: none;
        }
        .social-btn:hover {
            transform: translateY(-3px) scale(1.05);
            color: #fff !important;
        }
        .social-btn.facebook { background: #1877F2; box-shadow: 0 4px 12px rgba(24, 119, 242, 0.35); }
        .social-btn.instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); box-shadow: 0 4px 12px rgba(220, 39, 67, 0.35); }
        .social-btn.youtube { background: #FF0000; box-shadow: 0 4px 12px rgba(255, 0, 0, 0.35); }

        /* Floating WhatsApp Button */
        .whatsapp-float-btn {
            position: fixed;
            bottom: 26px;
            right: 26px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: #fff !important;
            padding: 12px 20px 12px 16px;
            border-radius: 999px;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.45);
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: transform .25s ease, box-shadow .25s ease;
            animation: waPulse 3s infinite;
        }
        .whatsapp-float-btn i {
            font-size: 26px;
        }
        .whatsapp-float-btn:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 12px 30px rgba(37, 211, 102, 0.65);
            color: #fff !important;
            animation: none;
        }
        @keyframes waPulse {
            0%, 100% { box-shadow: 0 8px 24px rgba(37, 211, 102, 0.45); }
            50% { box-shadow: 0 8px 32px rgba(37, 211, 102, 0.8); }
        }

        footer.site-footer {
            background: #111422;
            color: rgba(255,255,255,0.7);
            padding: 60px 0 30px;
            font-size: 14px;
        }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .footer-grid h5 { color: #fff; font-size: 15px; margin: 0 0 16px; font-weight: 700; }
        .footer-grid ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
        .footer-grid a:hover { color: #fff; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            flex-wrap: wrap;
            gap: 12px;
        }

        @media (max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr; }
            .hero { padding: 60px 0; }
            .hero h1 { font-size: 38px; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .nav-links {
                display: none; position: absolute; top: 72px; left: 0; right: 0; background: #fff;
                flex-direction: column; align-items: flex-start; padding: 16px 20px; gap: 14px;
                border-bottom: 1px solid #eceef5;
            }
            .nav-links.open { display: flex; }
            .nav-toggle { display: block; }
            .grid-4, .grid-3, .contact-grid, .footer-grid { grid-template-columns: 1fr; }
            .hero h1 { font-size: 32px; }
            .hero { padding: 50px 0 60px; }
            section { padding: 50px 0; }
            .section-head h2 { font-size: 26px; }

            .whatsapp-float-btn {
                padding: 14px;
                border-radius: 50%;
                bottom: 20px;
                right: 20px;
            }
            .whatsapp-float-btn span {
                display: none;
            }
            .whatsapp-float-btn i {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a href="./" class="brand">
                <?php if (!empty($logo_url)) { ?>
                    <img src="<?= $escape($logo_url) ?>" alt="<?= $escape($app_name) ?>">
                <?php } else { ?>
                    <span class="brand-badge"><?= $escape(strtoupper(substr($app_name, 0, 1))) ?></span>
                    <span><?= $escape($app_name) ?></span>
                <?php } ?>
            </a>
            <button class="nav-toggle" aria-label="Menu" onclick="document.getElementById('navLinks').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="nav-links" id="navLinks">
                <a href="#services">Services</a>
                <a href="#apps">Apps</a>
                <a href="#contact">Contact</a>
                <a class="btn btn-white" href="#apps" style="padding:10px 20px;">Download App</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-grid">
            <div>
                <h1>Everything you need,<br>delivered to your door.</h1>
                <p class="lead"><?= $escape($app_name) ?> brings food, groceries, shopping and parcel delivery together in one simple app - powered by stores near you.</p>
                <div class="hero-cta">
                    <a class="btn btn-white" href="#apps"><i class="fas fa-download"></i> Get the App</a>
                    <a class="btn btn-outline" href="#services"><i class="fas fa-layer-group"></i> Our Services</a>
                </div>
                <div class="hero-badges">
                    <div class="stat"><b>Fast</b><span>Doorstep delivery</span></div>
                    <div class="stat"><b>Live</b><span>Order tracking</span></div>
                    <div class="stat"><b>Secure</b><span>Easy payments</span></div>
                </div>
            </div>
            <div class="hero-card">
                <h3>Why choose us</h3>
                <ul>
                    <li><i class="fas fa-circle-check"></i><span>Order from trusted stores in your neighbourhood.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Transparent pricing with daily offers and coupons.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Multiple payment options, including cash on delivery.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Flexible options - doorstep delivery, store pickup, and live order tracking.</span></li>
                </ul>
            </div>
        </div>
    </section>

    <section id="services">
        <div class="container">
            <div class="section-head">
                <span class="eyebrow">What we offer</span>
                <h2>All the services we provide</h2>
                <p>One platform for all your daily delivery needs - from a hot meal to an urgent parcel.</p>
            </div>
            <div class="grid grid-4">
                <?php foreach ($services as $service) {
                    $icon = !empty($service['icon']) ? $service['icon'] : 'fa-check-circle';
                    if (strpos($icon, 'fa-') === false) {
                        $icon = 'fa-' . $icon;
                    }
                ?>
                <div class="service">
                    <span class="icon"><i class="fas <?= $escape($icon) ?>"></i></span>
                    <h3><?= $escape($service['title']) ?></h3>
                    <p><?= $escape($service['desc']) ?></p>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <section id="apps">
        <div class="container">
            <div class="section-head">
                <span class="eyebrow">Download</span>
                <h2>Get our apps</h2>
                <p>Download the app that fits your needs - for customers, sellers and delivery partners.</p>
            </div>
            <div class="grid grid-3">
                <?php foreach ($apps as $app) {
                    $has_play = !empty($app['play']);
                    $has_ios  = !empty($app['ios']);
                ?>
                <div class="app-card">
                    <span class="icon"><i class="fas <?= $escape($app['icon']) ?>"></i></span>
                    <h3><?= $escape($app['name']) ?></h3>
                    <p><?= $escape($app['desc']) ?></p>
                    <div class="store-btns">
                        <?php if ($has_play) { ?>
                        <a class="store-btn" href="<?= $escape($app['play']) ?>" target="_blank" rel="noopener">
                            <i class="fab fa-google-play"></i>
                            <span><small>Get it on</small>Google Play</span>
                        </a>
                        <?php } ?>
                        <?php if ($has_ios) { ?>
                        <a class="store-btn ios" href="<?= $escape($app['ios']) ?>" target="_blank" rel="noopener">
                            <i class="fab fa-apple"></i>
                            <span><small>Download on the</small>App Store</span>
                        </a>
                        <?php } ?>
                        <?php if (!$has_play && !$has_ios) { ?>
                        <span class="coming-soon"><i class="fas fa-clock"></i> Coming soon</span>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <section id="contact">
        <div class="container">
            <div class="section-head">
                <span class="eyebrow">Say hello</span>
                <h2>Get in touch</h2>
                <p>We are here to help you with orders, partnerships and support.</p>
            </div>
            <div class="contact-grid">
                <?php if (!empty($support_number)) { ?>
                <div class="contact-card">
                    <i class="fas fa-phone icon"></i>
                    <div>
                        <h4>Support Phone</h4>
                        <a href="tel:<?= $escape($support_number) ?>"><?= $escape($support_number) ?></a>
                    </div>
                </div>
                <?php } ?>
                <?php if (!empty($support_email)) { ?>
                <div class="contact-card">
                    <i class="fas fa-envelope icon"></i>
                    <div>
                        <h4>Support Email</h4>
                        <a href="mailto:<?= $escape($support_email) ?>"><?= $escape($support_email) ?></a>
                    </div>
                </div>
                <?php } ?>
                <?php if (!empty($whatsapp)) { ?>
                <div class="contact-card">
                    <i class="fab fa-whatsapp icon"></i>
                    <div>
                        <h4>Whatsapp</h4>
                        <a href="https://wa.me/<?= $escape(preg_replace('/[^0-9]/', '', $whatsapp)) ?>" target="_blank" rel="noopener"><?= $escape($whatsapp) ?></a>
                    </div>
                </div>
                <?php } ?>
                <?php if (!empty($store_address)) { ?>
                <div class="contact-card">
                    <i class="fas fa-location-dot icon"></i>
                    <div>
                        <h4>Address</h4>
                        <p><?= $escape($store_address) ?></p>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="brand">
                        <?php if (!empty($logo_url)) { ?>
                            <img src="<?= $escape($logo_url) ?>" alt="<?= $escape($app_name) ?>" style="height:44px;background:#fff;border-radius:10px;padding:6px;">
                        <?php } else { ?>
                            <span style="font-weight:800; font-size:20px; color:#fff;"><?= $escape($app_name) ?></span>
                        <?php } ?>
                    </div>
                    <p style="margin-top:12px;">Your one-stop destination for food, grocery, shopping and parcel delivery.</p>
                    <?php if (!empty($active_socials)) { ?>
                    <div style="margin-top:16px;">
                        <span style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#9ca3af; margin-bottom:8px;">Follow Us</span>
                        <div class="social-bar">
                            <?php foreach ($active_socials as $soc) { ?>
                            <a href="<?= $escape($soc['url']) ?>" target="_blank" rel="noopener noreferrer" class="social-btn <?= $escape($soc['class']) ?>" aria-label="<?= $escape($soc['title']) ?>" title="<?= $escape($soc['title']) ?>">
                                <i class="<?= $escape($soc['icon']) ?>"></i>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div>
                    <h5>Company</h5>
                    <ul>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#apps">Download Apps</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Legal</h5>
                    <ul>
                        <li><a href="privacypolicy.html">Privacy Policy</a></li>
                        <li><a href="terms_and_conditions.html">Terms &amp; Conditions</a></li>
                        <li><a href="refundpolicy.html">Refund Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?= date('Y') ?> <?= $escape($app_name) ?>. All rights reserved.</span>
                <span>Fast. Reliable. Delivered.</span>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <?php
    $wa_clean_num = preg_replace('/[^0-9]/', '', $whatsapp);
    if ($show_whatsapp_float && !empty($wa_clean_num)) {
        $wa_float_link = 'https://wa.me/' . $wa_clean_num . '?text=' . rawurlencode($whatsapp_float_msg);
    ?>
    <a href="<?= $escape($wa_float_link) ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-float-btn" aria-label="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
        <span>Chat with us</span>
    </a>
    <?php } ?>

    <script>
        document.querySelectorAll('#navLinks a').forEach(function (link) {
            link.addEventListener('click', function () {
                document.getElementById('navLinks').classList.remove('open');
            });
        });
    </script>
</body>
</html>
