<?php

require_once "includes/db.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: destinations.php");
    exit();
}

$destination_id = intval($_GET["id"]);

$stmt = $conn->prepare("
    SELECT *
    FROM destinations
    WHERE id = ?
");

$stmt->bind_param("i", $destination_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: destinations.php");
    exit();
}

$destination = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($destination["name"]); ?>
        - TripGenie
    </title>

    <link rel="stylesheet" href="css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Inter", Arial, sans-serif;
            background: #f5f7fb;
            color: #1e293b;
        }

        /* =========================
           NAVBAR
        ========================= */

        .top-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: auto;
            padding: 16px 20px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .logo {
            text-decoration: none;
            font-size: 25px;
            font-weight: 800;
            color: #2563eb;
        }

        .logo span {
            color: #111827;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .nav-links a:hover {
            color: #2563eb;
        }

        .nav-plan {
            background: #2563eb;
            color: white !important;
            padding: 10px 16px;
            border-radius: 9px;
        }

        .nav-plan:hover {
            background: #1d4ed8 !important;
        }

        /* =========================
           HERO
        ========================= */

        .details-page {
            min-height: calc(100vh - 70px);
            padding: 50px 20px 70px;
        }

        .details-container {
            max-width: 1100px;
            margin: auto;
        }

        .breadcrumb {
            margin-bottom: 22px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .breadcrumb span {
            color: #94a3b8;
            margin: 0 8px;
        }

        /* =========================
           MAIN CARD
        ========================= */

        .details-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(15, 23, 42, 0.10);

            border: 1px solid #eef2f7;
        }

        /* =========================
           IMAGE
        ========================= */

        .image-wrapper {
            position: relative;
            height: 470px;
            overflow: hidden;
        }

        .details-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;

            transition: transform 0.5s ease;
        }

        .details-card:hover .details-image {
            transform: scale(1.03);
        }

        .image-overlay {
            position: absolute;
            inset: 0;

            background:
                linear-gradient(
                    to top,
                    rgba(0, 0, 0, 0.55),
                    rgba(0, 0, 0, 0.05) 60%
                );
        }

        .image-title {
            position: absolute;
            left: 35px;
            bottom: 30px;
            color: white;
        }

        .image-title h1 {
            margin: 0 0 8px;
            font-size: 42px;
            font-weight: 800;
        }

        .image-location {
            font-size: 16px;
            font-weight: 600;
        }

        /* =========================
           CONTENT
        ========================= */

        .details-content {
            padding: 38px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 35px;
            align-items: start;
        }

        .section-label {
            color: #2563eb;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }

        .description {
            color: #475569;
            font-size: 16px;
            line-height: 1.8;
            margin: 0;
        }

        /* =========================
           INFO CARD
        ========================= */

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px;
        }

        .info-card h3 {
            margin: 0 0 18px;
            font-size: 18px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;

            background: #dbeafe;
            color: #2563eb;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 18px;
        }

        .info-text strong {
            display: block;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 3px;
        }

        .info-text span {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        /* =========================
           BUTTONS
        ========================= */

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 13px 20px;

            border-radius: 10px;
            text-decoration: none;

            font-size: 14px;
            font-weight: 700;

            transition: 0.25s;
        }

        .primary-btn {
            background: #2563eb;
            color: white;

            box-shadow:
                0 8px 20px rgba(37, 99, 235, 0.25);
        }

        .primary-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .secondary-btn {
            background: #f1f5f9;
            color: #334155;
        }

        .secondary-btn:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        /* =========================
           BOTTOM NOTE
        ========================= */

        .travel-note {
            margin-top: 25px;
            padding: 18px 20px;

            background: #eff6ff;
            border: 1px solid #bfdbfe;

            border-radius: 14px;

            color: #1e40af;
            font-size: 14px;
            line-height: 1.6;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 850px) {

            .content-grid {
                grid-template-columns: 1fr;
            }

            .info-card {
                width: 100%;
            }

            .nav-links {
                gap: 12px;
            }

        }

        @media (max-width: 650px) {

            .nav-links a:nth-child(1),
            .nav-links a:nth-child(2),
            .nav-links a:nth-child(3) {
                display: none;
            }

            .details-page {
                padding: 25px 15px 50px;
            }

            .image-wrapper {
                height: 320px;
            }

            .image-title {
                left: 22px;
                bottom: 22px;
            }

            .image-title h1 {
                font-size: 30px;
            }

            .details-content {
                padding: 25px 20px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

        }

        @media (max-width: 420px) {

            .logo {
                font-size: 21px;
            }

            .nav-plan {
                padding: 8px 11px;
                font-size: 12px !important;
            }

            .image-wrapper {
                height: 260px;
            }

            .image-title h1 {
                font-size: 25px;
            }

        }

    </style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<nav class="top-nav">

    <div class="nav-container">

        <a href="index.php" class="logo">
            Trip<span>Genie</span>
        </a>

        <div class="nav-links">

            <a href="index.php">
                Home
            </a>

            <a href="destinations.php">
                Destinations
            </a>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="my-trips.php">
                My Trips
            </a>

            <a href="profile.php">
                Profile
            </a>

            <a href="plan-trip.php" class="nav-plan">
                + Plan Trip
            </a>

        </div>

    </div>

</nav>


<!-- =========================
     PAGE
========================= -->

<div class="details-page">

    <div class="details-container">

        <div class="breadcrumb">

            <a href="destinations.php">
                Destinations
            </a>

            <span>›</span>

            <?php
            echo htmlspecialchars($destination["name"]);
            ?>

        </div>


        <div class="details-card">

            <!-- IMAGE -->

            <div class="image-wrapper">

                <?php if (!empty($destination["image"])): ?>

                    <img
                        src="<?php echo htmlspecialchars($destination["image"]); ?>"
                        alt="<?php echo htmlspecialchars($destination["name"]); ?>"
                        class="details-image"
                    >

                <?php else: ?>

                    <img
                        src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80"
                        alt="Travel destination"
                        class="details-image"
                    >

                <?php endif; ?>


                <div class="image-overlay"></div>


                <div class="image-title">

                    <h1>
                        <?php
                        echo htmlspecialchars($destination["name"]);
                        ?>
                    </h1>

                    <div class="image-location">

                        📍

                        <?php
                        echo htmlspecialchars($destination["location"]);
                        ?>

                    </div>

                </div>

            </div>


            <!-- CONTENT -->

            <div class="details-content">

                <div class="content-grid">

                    <!-- DESCRIPTION -->

                    <div>

                        <div class="section-label">
                            Discover
                        </div>

                        <p class="description">

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $destination["description"]
                                )
                            );
                            ?>

                        </p>


                        <div class="travel-note">

                            ✨ <strong>Plan your adventure with TripGenie.</strong>
                            Choose your travel dates and budget to create
                            your personalized trip plan.

                        </div>


                        <div class="action-buttons">

                            <a
                                href="plan-trip.php"
                                class="btn primary-btn"
                            >
                                ✈️ Plan This Trip
                            </a>

                            <a
                                href="destinations.php"
                                class="btn secondary-btn"
                            >
                                ← Back to Destinations
                            </a>

                        </div>

                    </div>


                    <!-- INFORMATION -->

                    <div class="info-card">

                        <h3>
                            📋 Destination Info
                        </h3>


                        <div class="info-item">

                            <div class="info-icon">
                                📍
                            </div>

                            <div class="info-text">

                                <strong>Location</strong>

                                <span>
                                    <?php
                                    echo htmlspecialchars(
                                        $destination["location"]
                                    );
                                    ?>
                                </span>

                            </div>

                        </div>


                        <div class="info-item">

                            <div class="info-icon">
                                🌍
                            </div>

                            <div class="info-text">

                                <strong>Destination</strong>

                                <span>
                                    <?php
                                    echo htmlspecialchars(
                                        $destination["name"]
                                    );
                                    ?>
                                </span>

                            </div>

                        </div>


                        <div class="info-item">

                            <div class="info-icon">
                                ✈️
                            </div>

                            <div class="info-text">

                                <strong>Trip Planning</strong>

                                <span>
                                    Available
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="action-buttons" style="margin-top:25px;">

            <a
                href="dashboard.php"
                class="btn secondary-btn"
            >
                ← Back to Dashboard
            </a>

        </div>

    </div>

</div>

</body>

</html>