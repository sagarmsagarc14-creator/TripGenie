<?php
session_start();

$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $_SESSION["user_name"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TripGenie | Plan. Explore. Travel.</title>

    <link rel="stylesheet" href="css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        /* =========================================
           TRIPGENIE HOME PAGE
        ========================================= */

        body {
            font-family: "Inter", Arial, sans-serif;
            background: #f7f9fc;
            color: #172033;
        }

        /* NAVBAR */

        .tg-navbar {
            position: sticky;
            top: 0;
            z-index: 1000;

            background: rgba(255,255,255,0.94);

            backdrop-filter: blur(14px);

            border-bottom: 1px solid #e8edf5;

            padding: 16px 0;
        }

        .tg-nav-container {
            width: 90%;
            max-width: 1200px;
            margin: auto;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tg-logo {
            text-decoration: none;
            font-size: 25px;
            font-weight: 800;
            color: #172033;
        }

        .tg-logo span {
            color: #2563eb;
        }

        .tg-logo-icon {
            margin-right: 6px;
        }

        .tg-nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .tg-nav-links a {
            text-decoration: none;
            color: #475569;
            font-size: 14px;
            font-weight: 600;

            transition: 0.2s;
        }

        .tg-nav-links a:hover {
            color: #2563eb;
        }

        .tg-nav-button {
            text-decoration: none;

            padding: 11px 20px;

            border-radius: 10px;

            background: #2563eb;

            color: white;

            font-size: 14px;
            font-weight: 700;

            box-shadow: 0 8px 20px rgba(37,99,235,0.22);

            transition: 0.2s;
        }

        .tg-nav-button:hover {
            transform: translateY(-2px);
            background: #1d4ed8;
        }

        /* HERO */

        .tg-hero {
            position: relative;
            overflow: hidden;

            min-height: 620px;

            display: flex;
            align-items: center;

            background:
                radial-gradient(circle at 80% 20%, rgba(96,165,250,0.35), transparent 30%),
                radial-gradient(circle at 10% 90%, rgba(59,130,246,0.25), transparent 30%),
                linear-gradient(135deg, #0f2f63, #1557a5 55%, #2563eb);

            color: white;
        }

        .tg-hero::before {
            content: "";

            position: absolute;

            width: 400px;
            height: 400px;

            border-radius: 50%;

            right: -120px;
            top: -160px;

            background: rgba(255,255,255,0.08);
        }

        .tg-hero::after {
            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            border-radius: 50%;

            left: -150px;
            bottom: -160px;

            background: rgba(255,255,255,0.07);
        }

        .tg-hero-container {
            position: relative;
            z-index: 2;

            width: 90%;
            max-width: 1200px;

            margin: auto;

            display: grid;
            grid-template-columns: 1.1fr 0.9fr;

            gap: 60px;

            align-items: center;
        }

        .tg-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 8px 14px;

            border-radius: 30px;

            background: rgba(255,255,255,0.12);

            border: 1px solid rgba(255,255,255,0.18);

            font-size: 13px;
            font-weight: 600;

            margin-bottom: 22px;
        }

        .tg-hero h1 {
            margin: 0;

            font-size: clamp(45px, 6vw, 72px);

            line-height: 1.05;

            letter-spacing: -2px;

            font-weight: 800;
        }

        .tg-hero h1 span {
            color: #93c5fd;
        }

        .tg-hero-text {
            max-width: 620px;

            margin: 24px 0;

            color: rgba(255,255,255,0.84);

            font-size: 17px;

            line-height: 1.8;
        }

        .tg-hero-buttons {
            display: flex;

            gap: 14px;

            flex-wrap: wrap;

            margin-top: 28px;
        }

        .tg-primary-btn,
        .tg-secondary-btn {
            text-decoration: none;

            padding: 14px 22px;

            border-radius: 12px;

            font-size: 14px;

            font-weight: 700;

            transition: 0.25s;
        }

        .tg-primary-btn {
            background: white;
            color: #1d4ed8;

            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .tg-secondary-btn {
            background: rgba(255,255,255,0.1);

            border: 1px solid rgba(255,255,255,0.25);

            color: white;
        }

        .tg-primary-btn:hover,
        .tg-secondary-btn:hover {
            transform: translateY(-3px);
        }

        .tg-hero-note {
            margin-top: 22px;

            font-size: 12px;

            color: rgba(255,255,255,0.7);
        }

        /* HERO VISUAL */

        .tg-travel-card {
            position: relative;

            min-height: 430px;
        }

        .tg-main-card {
            position: absolute;

            right: 20px;
            top: 10px;

            width: 330px;

            background: rgba(255,255,255,0.96);

            border-radius: 24px;

            padding: 14px;

            box-shadow: 0 30px 70px rgba(0,0,0,0.25);

            transform: rotate(2deg);
        }

        .tg-main-card img {
            width: 100%;
            height: 220px;

            object-fit: cover;

            border-radius: 18px;
        }

        .tg-card-content {
            padding: 16px 8px 8px;
        }

        .tg-card-content h3 {
            margin: 0 0 7px;

            color: #172033;

            font-size: 20px;
        }

        .tg-card-location {
            color: #64748b;

            font-size: 13px;

            margin-bottom: 14px;
        }

        .tg-card-info {
            display: flex;

            justify-content: space-between;

            color: #475569;

            font-size: 12px;
        }

        .tg-floating-card {
            position: absolute;

            display: flex;
            align-items: center;
            gap: 10px;

            background: rgba(255,255,255,0.96);

            color: #172033;

            padding: 12px 16px;

            border-radius: 14px;

            box-shadow: 0 15px 35px rgba(0,0,0,0.18);

            font-size: 12px;
            font-weight: 700;
        }

        .tg-floating-card strong {
            display: block;
            font-size: 13px;
        }

        .tg-floating-card small {
            color: #64748b;
            font-weight: 500;
        }

        .tg-floating-one {
            left: 0;
            top: 100px;
        }

        .tg-floating-two {
            right: 0;
            bottom: 60px;
        }

        .tg-floating-icon {
            font-size: 25px;
        }

        /* SEARCH */

        .tg-search-section {
            width: 90%;
            max-width: 1000px;

            margin: -42px auto 0;

            position: relative;
            z-index: 10;
        }

        .tg-search-box {
            background: white;

            border-radius: 18px;

            padding: 22px 25px;

            box-shadow: 0 15px 40px rgba(15,23,42,0.12);

            display: flex;
            align-items: center;
            gap: 15px;
        }

        .tg-search-icon {
            font-size: 25px;
        }

        .tg-search-text {
            flex: 1;
        }

        .tg-search-text strong {
            display: block;

            color: #172033;

            margin-bottom: 4px;
        }

        .tg-search-text span {
            color: #64748b;

            font-size: 13px;
        }

        .tg-search-btn {
            text-decoration: none;

            background: #2563eb;

            color: white;

            padding: 12px 20px;

            border-radius: 10px;

            font-size: 13px;
            font-weight: 700;
        }

        /* COMMON SECTION */

        .tg-section {
            width: 90%;
            max-width: 1200px;

            margin: auto;

            padding: 90px 0;
        }

        .tg-section-heading {
            text-align: center;

            max-width: 650px;

            margin: 0 auto 45px;
        }

        .tg-section-heading span {
            color: #2563eb;

            font-size: 13px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 1.5px;
        }

        .tg-section-heading h2 {
            margin: 10px 0;

            font-size: 36px;

            color: #172033;
        }

        .tg-section-heading p {
            color: #64748b;

            line-height: 1.7;

            font-size: 15px;
        }

        /* DESTINATIONS */

        .tg-destination-grid {
            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 22px;
        }

        .tg-destination {
            background: white;

            border-radius: 20px;

            overflow: hidden;

            box-shadow: 0 8px 25px rgba(15,23,42,0.07);

            transition: 0.3s;
        }

        .tg-destination:hover {
            transform: translateY(-7px);

            box-shadow: 0 18px 40px rgba(15,23,42,0.13);
        }

        .tg-destination-image {
            position: relative;
        }

        .tg-destination-image img {
            width: 100%;

            height: 210px;

            object-fit: cover;

            display: block;
        }

        .tg-destination-tag {
            position: absolute;

            top: 14px;
            left: 14px;

            padding: 6px 10px;

            border-radius: 20px;

            background: rgba(255,255,255,0.93);

            color: #1d4ed8;

            font-size: 11px;

            font-weight: 800;
        }

        .tg-destination-content {
            padding: 18px;
        }

        .tg-destination-content h3 {
            margin: 0 0 7px;

            font-size: 19px;
        }

        .tg-destination-content p {
            margin: 0 0 15px;

            color: #64748b;

            font-size: 13px;
        }

        .tg-destination-link {
            text-decoration: none;

            color: #2563eb;

            font-size: 13px;

            font-weight: 700;
        }

        /* FEATURES */

        .tg-feature-grid {
            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 20px;
        }

        .tg-feature {
            background: white;

            padding: 28px 22px;

            border-radius: 18px;

            border: 1px solid #edf1f7;

            transition: 0.3s;
        }

        .tg-feature:hover {
            transform: translateY(-5px);

            box-shadow: 0 15px 35px rgba(15,23,42,0.08);
        }

        .tg-feature-icon {
            width: 50px;
            height: 50px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eff6ff;

            border-radius: 14px;

            font-size: 24px;

            margin-bottom: 18px;
        }

        .tg-feature h3 {
            margin: 0 0 9px;

            font-size: 17px;
        }

        .tg-feature p {
            margin: 0;

            color: #64748b;

            font-size: 13px;

            line-height: 1.7;
        }

        /* CTA */

        .tg-cta {
            width: 90%;
            max-width: 1200px;

            margin: 20px auto 80px;

            padding: 55px 60px;

            border-radius: 25px;

            background:
                radial-gradient(circle at 90% 20%, rgba(255,255,255,0.15), transparent 30%),
                linear-gradient(135deg, #0f3d7a, #2563eb);

            color: white;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 30px;
        }

        .tg-cta h2 {
            margin: 0 0 10px;

            font-size: 31px;
        }

        .tg-cta p {
            margin: 0;

            color: rgba(255,255,255,0.78);

            font-size: 14px;
        }

        .tg-cta a {
            text-decoration: none;

            background: white;

            color: #1d4ed8;

            padding: 14px 22px;

            border-radius: 11px;

            font-size: 13px;

            font-weight: 800;

            white-space: nowrap;
        }

        /* FOOTER */

        .tg-footer {
            background: #0f172a;

            color: white;

            padding: 45px 0 25px;
        }

        .tg-footer-container {
            width: 90%;
            max-width: 1200px;

            margin: auto;

            display: flex;

            justify-content: space-between;

            gap: 30px;
        }

        .tg-footer-brand h3 {
            margin: 0 0 10px;

            font-size: 22px;
        }

        .tg-footer-brand h3 span {
            color: #60a5fa;
        }

        .tg-footer-brand p {
            max-width: 400px;

            color: #94a3b8;

            font-size: 13px;

            line-height: 1.7;
        }

        .tg-footer-links {
            display: flex;

            gap: 25px;

            align-items: center;
        }

        .tg-footer-links a {
            color: #cbd5e1;

            text-decoration: none;

            font-size: 13px;
        }

        .tg-footer-bottom {
            width: 90%;
            max-width: 1200px;

            margin: 30px auto 0;

            padding-top: 20px;

            border-top: 1px solid rgba(255,255,255,0.08);

            color: #64748b;

            font-size: 12px;
        }

        /* MOBILE */

        @media (max-width: 900px) {

            .tg-nav-links {
                display: none;
            }

            .tg-hero-container {
                grid-template-columns: 1fr;
            }

            .tg-hero {
                padding: 80px 0;
            }

            .tg-travel-card {
                max-width: 500px;
                width: 100%;
                margin: auto;
            }

            .tg-destination-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .tg-feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .tg-cta {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 600px) {

            .tg-hero h1 {
                font-size: 43px;
            }

            .tg-search-box {
                flex-direction: column;
                align-items: flex-start;
            }

            .tg-search-btn {
                width: 100%;
                text-align: center;
            }

            .tg-destination-grid,
            .tg-feature-grid {
                grid-template-columns: 1fr;
            }

            .tg-main-card {
                position: relative;

                right: auto;
                top: auto;

                width: 90%;

                margin: auto;
            }

            .tg-floating-one {
                left: 0;
                top: 50px;
            }

            .tg-floating-two {
                right: 0;
                bottom: 30px;
            }

            .tg-cta {
                padding: 35px 25px;
            }

            .tg-cta h2 {
                font-size: 25px;
            }

            .tg-footer-container {
                flex-direction: column;
            }

            .tg-footer-links {
                flex-wrap: wrap;
            }
        }

    </style>

</head>

<body>


<!-- =========================================
     NAVBAR
========================================= -->

<header class="tg-navbar">

    <div class="tg-nav-container">

        <a href="index.php" class="tg-logo">
            <span class="tg-logo-icon">✈️</span>
            Trip<span>Genie</span>
        </a>


        <nav class="tg-nav-links">

            <a href="index.php">Home</a>

            <a href="destinations.php">Destinations</a>

            <?php if ($isLoggedIn): ?>

                <a href="dashboard.php">Dashboard</a>

                <a href="my-trips.php">My Trips</a>

                <a href="profile.php">Profile</a>

            <?php else: ?>

                <a href="login.php">Login</a>

            <?php endif; ?>

        </nav>


        <?php if ($isLoggedIn): ?>

            <a href="plan-trip.php" class="tg-nav-button">
                + Plan Trip
            </a>

        <?php else: ?>

            <a href="register.php" class="tg-nav-button">
                Get Started
            </a>

        <?php endif; ?>

    </div>

</header>


<!-- =========================================
     HERO
========================================= -->

<section class="tg-hero">

    <div class="tg-hero-container">


        <div class="tg-hero-content">

            <div class="tg-badge">
                🌍 Your journey starts here
            </div>


            <h1>
                Travel smarter.<br>
                <span>Explore more.</span>
            </h1>


            <p class="tg-hero-text">
                TripGenie helps you discover beautiful destinations,
                plan your trips, organize itineraries and manage your
                travel budget — all in one simple place.
            </p>


            <div class="tg-hero-buttons">

                <a href="destinations.php" class="tg-primary-btn">
                    🌎 Explore Destinations
                </a>

                <?php if ($isLoggedIn): ?>

                    <a href="plan-trip.php" class="tg-secondary-btn">
                        ✈️ Plan My Trip
                    </a>

                <?php else: ?>

                    <a href="register.php" class="tg-secondary-btn">
                        🚀 Create Free Account
                    </a>

                <?php endif; ?>

            </div>


            <div class="tg-hero-note">
                ✓ Easy planning &nbsp;&nbsp;
                ✓ Smart itineraries &nbsp;&nbsp;
                ✓ Budget tracking
            </div>

        </div>


        <!-- HERO VISUAL -->

        <div class="tg-travel-card">


            <div class="tg-main-card">

                <img
                    src="https://images.unsplash.com/photo-1527631746610-bca00a040d60?auto=format&fit=crop&w=900&q=85"
                    alt="Travel destination"
                >


                <div class="tg-card-content">

                    <h3>
                        Discover your next adventure
                    </h3>

                    <div class="tg-card-location">
                        📍 Explore amazing places
                    </div>


                    <div class="tg-card-info">

                        <span>✈️ Travel</span>

                        <span>🗓️ Plan</span>

                        <span>💰 Budget</span>

                    </div>

                </div>

            </div>


            <div class="tg-floating-card tg-floating-one">

                <div class="tg-floating-icon">
                    🗺️
                </div>

                <div>
                    <strong>Smart Itinerary</strong>
                    <small>Organize your journey</small>
                </div>

            </div>


            <div class="tg-floating-card tg-floating-two">

                <div class="tg-floating-icon">
                    💰
                </div>

                <div>
                    <strong>Budget Planner</strong>
                    <small>Track your expenses</small>
                </div>

            </div>


        </div>

    </div>

</section>


<!-- =========================================
     SEARCH / QUICK ACTION
========================================= -->

<section class="tg-search-section">

    <div class="tg-search-box">

        <div class="tg-search-icon">
            🔎
        </div>

        <div class="tg-search-text">

            <strong>
                Where do you want to go?
            </strong>

            <span>
                Discover destinations and start planning your journey.
            </span>

        </div>

        <a href="destinations.php" class="tg-search-btn">
            Explore Now →
        </a>

    </div>

</section>


<!-- =========================================
     POPULAR DESTINATIONS
========================================= -->

<section class="tg-section">

    <div class="tg-section-heading">

        <span>Explore</span>

        <h2>
            Popular destinations
        </h2>

        <p>
            Find your next unforgettable destination and start
            planning a journey that fits your style and budget.
        </p>

    </div>


    <div class="tg-destination-grid">


        <div class="tg-destination">

            <div class="tg-destination-image">

                <img
                    src="https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=900&q=80"
                    alt="Goa"
                >

                <div class="tg-destination-tag">
                    BEACH
                </div>

            </div>


            <div class="tg-destination-content">

                <h3>
                    Goa
                </h3>

                <p>
                    Beaches, sunsets and unforgettable experiences.
                </p>

                <a href="destinations.php" class="tg-destination-link">
                    Explore Goa →
                </a>

            </div>

        </div>


        <div class="tg-destination">

            <div class="tg-destination-image">

                <img
                    src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=900&q=80"
                    alt="Manali"
                >

                <div class="tg-destination-tag">
                    MOUNTAINS
                </div>

            </div>


            <div class="tg-destination-content">

                <h3>
                    Manali
                </h3>

                <p>
                    Mountains, adventure and peaceful landscapes.
                </p>

                <a href="destinations.php" class="tg-destination-link">
                    Explore Manali →
                </a>

            </div>

        </div>


        <div class="tg-destination">

            <div class="tg-destination-image">

                <img
                    src="https://images.unsplash.com/photo-1477587458883-47145ed94245?auto=format&fit=crop&w=900&q=80"
                    alt="Jaipur"
                >

                <div class="tg-destination-tag">
                    CULTURE
                </div>

            </div>


            <div class="tg-destination-content">

                <h3>
                    Jaipur
                </h3>

                <p>
                    Discover royal architecture and rich culture.
                </p>

                <a href="destinations.php" class="tg-destination-link">
                    Explore Jaipur →
                </a>

            </div>

        </div>


    </div>

</section>


<!-- =========================================
     FEATURES
========================================= -->

<section class="tg-section" style="padding-top:20px;">

    <div class="tg-section-heading">

        <span>Everything in one place</span>

        <h2>
            Your complete travel companion
        </h2>

        <p>
            TripGenie brings the important parts of trip planning
            together in one easy-to-use platform.
        </p>

    </div>


    <div class="tg-feature-grid">


        <div class="tg-feature">

            <div class="tg-feature-icon">
                🌍
            </div>

            <h3>
                Explore Destinations
            </h3>

            <p>
                Discover destinations with useful information
                to help you decide where to travel.
            </p>

        </div>


        <div class="tg-feature">

            <div class="tg-feature-icon">
                🗓️
            </div>

            <h3>
                Plan Your Trip
            </h3>

            <p>
                Create trips with destinations, dates and
                planned travel budgets.
            </p>

        </div>


        <div class="tg-feature">

            <div class="tg-feature-icon">
                🗺️
            </div>

            <h3>
                Smart Itinerary
            </h3>

            <p>
                Organize activities by day and keep your
                entire journey structured.
            </p>

        </div>


        <div class="tg-feature">

            <div class="tg-feature-icon">
                💰
            </div>

            <h3>
                Budget Tracking
            </h3>

            <p>
                Record hotel, transport, food and activity
                expenses for your trips.
            </p>

        </div>


    </div>

</section>


<!-- =========================================
     CTA
========================================= -->

<section class="tg-cta">

    <div>

        <h2>
            Ready for your next adventure?
        </h2>

        <p>
            Create your TripGenie account and start planning today.
        </p>

    </div>


    <?php if ($isLoggedIn): ?>

        <a href="plan-trip.php">
            Plan My Trip →
        </a>

    <?php else: ?>

        <a href="register.php">
            Create Free Account →
        </a>

    <?php endif; ?>

</section>


<!-- =========================================
     FOOTER
========================================= -->

<footer class="tg-footer">

    <div class="tg-footer-container">


        <div class="tg-footer-brand">

            <h3>
                ✈️ Trip<span>Genie</span>
            </h3>

            <p>
                A simple travel planning platform designed to
                help you discover destinations, organize trips
                and manage your travel budget.
            </p>

        </div>


        <div class="tg-footer-links">

            <a href="index.php">
                Home
            </a>

            <a href="destinations.php">
                Destinations
            </a>

            <?php if ($isLoggedIn): ?>

                <a href="dashboard.php">
                    Dashboard
                </a>

                <a href="profile.php">
                    Profile
                </a>

            <?php else: ?>

                <a href="login.php">
                    Login
                </a>

                <a href="register.php">
                    Register
                </a>

            <?php endif; ?>

        </div>

    </div>


    <div class="tg-footer-bottom">

        © <?php echo date("Y"); ?> TripGenie.
        Travel smarter. Explore more.

    </div>

</footer>


</body>
</html>