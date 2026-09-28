<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"] ?? "Traveler";


/* =========================
   TRIP STATISTICS
========================= */

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_trips,
        COALESCE(SUM(budget), 0) AS total_budget
    FROM trips
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$stats = $result->fetch_assoc();

$total_trips = (int)$stats["total_trips"];
$total_budget = (float)$stats["total_budget"];

$stmt->close();


/* =========================
   UPCOMING TRIP
========================= */

$stmt = $conn->prepare("
    SELECT
        id,
        destination,
        start_date,
        end_date,
        budget
    FROM trips
    WHERE user_id = ?
    AND start_date >= CURDATE()
    ORDER BY start_date ASC
    LIMIT 1
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$upcoming_trip = $result->fetch_assoc();

$stmt->close();


/* =========================
   CREATE ITINERARY TABLE
========================= */

$conn->query("
    CREATE TABLE IF NOT EXISTS itineraries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        trip_id INT NOT NULL,
        day INT NOT NULL,
        activity VARCHAR(255) NOT NULL,
        location VARCHAR(255),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");


/* =========================
   ITINERARY COUNT
========================= */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total_itineraries
    FROM itineraries
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$itinerary_data = $result->fetch_assoc();

$total_itineraries =
    (int)$itinerary_data["total_itineraries"];

$stmt->close();


/* =========================
   UPCOMING TRIP DAYS
========================= */

$days_until_trip = null;

if ($upcoming_trip) {

    $today = new DateTime();

    $trip_date = new DateTime(
        $upcoming_trip["start_date"]
    );

    $difference =
        $today->diff($trip_date);

    $days_until_trip =
        (int)$difference->format("%r%a");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Dashboard | TripGenie</title>

<link rel="preconnect"
      href="https://fonts.googleapis.com">

<link rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet">


<style>

/* =========================
   RESET
========================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;

    font-family: "Inter", Arial, sans-serif;

    background: #f6f8fc;

    color: #172033;
}


/* =========================
   NAVBAR
========================= */

.navbar {
    position: sticky;

    top: 0;

    z-index: 1000;

    background: rgba(255,255,255,0.96);

    backdrop-filter: blur(12px);

    border-bottom: 1px solid #e7ebf2;
}

.nav-container {
    max-width: 1180px;

    margin: auto;

    padding: 15px 22px;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.logo {
    text-decoration: none;

    font-size: 24px;

    font-weight: 800;

    color: #172033;
}

.logo span {
    color: #2563eb;
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


/* =========================
   PAGE
========================= */

.dashboard-page {
    min-height: calc(100vh - 70px);

    padding: 35px 20px 65px;
}

.dashboard-container {
    max-width: 1120px;

    margin: auto;
}


/* =========================
   WELCOME
========================= */

.welcome {
    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #1d4ed8
        );

    color: white;

    padding: 34px;

    border-radius: 20px;

    margin-bottom: 22px;

    box-shadow:
        0 15px 35px rgba(37,99,235,0.18);
}

.welcome::after {
    content: "";

    position: absolute;

    width: 220px;

    height: 220px;

    right: -80px;

    top: -90px;

    border-radius: 50%;

    background: rgba(255,255,255,0.08);
}

.welcome-content {
    position: relative;

    z-index: 2;
}

.welcome h1 {
    margin: 0 0 9px;

    font-size: 30px;

    font-weight: 800;
}

.welcome p {
    margin: 0;

    font-size: 14px;

    opacity: 0.9;
}

.welcome-action {
    display: inline-block;

    margin-top: 20px;

    padding: 11px 17px;

    border-radius: 9px;

    background: white;

    color: #2563eb;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;
}


/* =========================
   STATS
========================= */

.stats {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 17px;

    margin-bottom: 22px;
}

.stat-card {
    background: white;

    border: 1px solid #e7ebf2;

    border-radius: 16px;

    padding: 22px;

    box-shadow:
        0 6px 22px rgba(15,23,42,0.05);

    transition: 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-3px);

    box-shadow:
        0 10px 28px rgba(15,23,42,0.08);
}

.stat-icon {
    font-size: 26px;

    margin-bottom: 12px;
}

.stat-value {
    font-size: 26px;

    font-weight: 800;

    margin-bottom: 5px;
}

.stat-label {
    color: #64748b;

    font-size: 12px;

    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: 0.4px;
}


/* =========================
   UPCOMING TRIP
========================= */

.upcoming {
    background: white;

    border: 1px solid #e7ebf2;

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 25px;

    box-shadow:
        0 7px 25px rgba(15,23,42,0.05);
}

.section-heading {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 18px;
}

.section-heading h2 {
    margin: 0;

    font-size: 19px;

    font-weight: 800;
}

.section-heading a {
    color: #2563eb;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;
}

.trip-info {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    padding: 20px;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            #eff6ff,
            #f8fbff
        );

    border: 1px solid #dbeafe;
}

.trip-destination {
    font-size: 21px;

    font-weight: 800;

    margin-bottom: 8px;
}

.trip-detail {
    color: #64748b;

    font-size: 13px;

    margin-top: 6px;
}

.trip-countdown {
    color: #2563eb;

    font-size: 12px;

    font-weight: 700;

    margin-top: 8px;
}

.trip-budget {
    text-align: right;

    min-width: 130px;
}

.trip-budget span {
    display: block;

    color: #64748b;

    font-size: 11px;

    margin-bottom: 5px;
}

.trip-budget strong {
    font-size: 21px;

    color: #2563eb;
}

.view-btn {
    display: inline-block;

    margin-top: 12px;

    padding: 10px 15px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;
}

.no-trip {
    padding: 18px;

    border-radius: 12px;

    background: #f8fafc;

    color: #64748b;

    font-size: 13px;
}

.no-trip .view-btn {
    margin-top: 15px;
}


/* =========================
   QUICK ACTIONS
========================= */

.quick-title {
    margin: 0 0 16px;

    font-size: 20px;

    font-weight: 800;
}

.dashboard-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 17px;
}

.dashboard-card {
    background: white;

    border: 1px solid #e7ebf2;

    border-radius: 16px;

    padding: 23px;

    text-decoration: none;

    color: #172033;

    box-shadow:
        0 6px 22px rgba(15,23,42,0.04);

    transition: 0.2s ease;
}

.dashboard-card:hover {
    transform: translateY(-4px);

    border-color: #bfdbfe;

    box-shadow:
        0 12px 30px rgba(15,23,42,0.08);
}

.card-icon {
    font-size: 30px;

    margin-bottom: 13px;
}

.dashboard-card h3 {
    margin: 0 0 7px;

    font-size: 16px;
}

.dashboard-card p {
    margin: 0;

    color: #64748b;

    font-size: 12px;

    line-height: 1.6;
}


/* =========================
   FOOTER
========================= */

footer {
    text-align: center;

    padding: 25px;

    color: #94a3b8;

    font-size: 12px;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 850px) {

    .nav-links {
        display: none;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .dashboard-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 600px) {

    .dashboard-page {
        padding: 25px 14px 50px;
    }

    .welcome {
        padding: 27px 22px;
    }

    .welcome h1 {
        font-size: 24px;
    }

    .trip-info {
        flex-direction: column;

        align-items: flex-start;
    }

    .trip-budget {
        text-align: left;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .upcoming {
        padding: 20px;
    }
}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<header class="navbar">

    <div class="nav-container">

        <a href="index.php" class="logo">
            Trip<span>Genie</span>
        </a>


        <nav class="nav-links">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="destinations.php">
                Destinations
            </a>

            <a href="my-trips.php">
                My Trips
            </a>

            <a href="budget.php">
                Budget
            </a>

            <a href="profile.php">
                Profile
            </a>

            <a href="plan-trip.php"
               class="nav-plan">

                + Plan Trip

            </a>

        </nav>

    </div>

</header>


<!-- =========================
     DASHBOARD
========================= -->

<main class="dashboard-page">

<div class="dashboard-container">


    <!-- WELCOME -->

    <section class="welcome">

        <div class="welcome-content">

            <h1>

                Welcome back,
                <?php

                echo htmlspecialchars(
                    $user_name
                );

                ?> 👋

            </h1>

            <p>
                Plan your next adventure and manage
                all your travel plans in one place.
            </p>

            <a
                href="plan-trip.php"
                class="welcome-action"
            >

                ✈️ Plan a New Trip

            </a>

        </div>

    </section>


    <!-- STATISTICS -->

    <section class="stats">


        <div class="stat-card">

            <div class="stat-icon">
                🧳
            </div>

            <div class="stat-value">

                <?php
                echo $total_trips;
                ?>

            </div>

            <div class="stat-label">
                Total Trips
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                💰
            </div>

            <div class="stat-value">

                ₹<?php

                echo number_format(
                    $total_budget,
                    2
                );

                ?>

            </div>

            <div class="stat-label">
                Planned Budget
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🗓️
            </div>

            <div class="stat-value">

                <?php
                echo $total_itineraries;
                ?>

            </div>

            <div class="stat-label">
                Itinerary Activities
            </div>

        </div>


    </section>


    <!-- UPCOMING TRIP -->

    <section class="upcoming">


        <div class="section-heading">

            <h2>
                ✈️ Upcoming Trip
            </h2>

            <a href="my-trips.php">
                View all trips →
            </a>

        </div>


        <?php if ($upcoming_trip): ?>


            <div class="trip-info">


                <div>

                    <div class="trip-destination">

                        📍

                        <?php

                        echo htmlspecialchars(
                            $upcoming_trip["destination"]
                        );

                        ?>

                    </div>


                    <div class="trip-detail">

                        📅

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $upcoming_trip["start_date"]
                            )
                        );

                        ?>

                        →

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $upcoming_trip["end_date"]
                            )
                        );

                        ?>

                    </div>


                    <?php if ($days_until_trip !== null): ?>

                        <?php if ($days_until_trip > 0): ?>

                            <div class="trip-countdown">

                                🕐

                                <?php

                                echo $days_until_trip;

                                ?>

                                day<?php

                                echo $days_until_trip == 1
                                    ? ""
                                    : "s";

                                ?>

                                until your trip

                            </div>

                        <?php elseif ($days_until_trip === 0): ?>

                            <div class="trip-countdown">

                                🎉 Your trip starts today!

                            </div>

                        <?php endif; ?>

                    <?php endif; ?>


                    <a
                        href="my-trips.php"
                        class="view-btn"
                    >

                        🧳 View My Trips

                    </a>

                </div>


                <div class="trip-budget">

                    <span>
                        Trip Budget
                    </span>

                    <strong>

                        ₹<?php

                        echo number_format(
                            $upcoming_trip["budget"],
                            2
                        );

                        ?>

                    </strong>

                </div>


            </div>


        <?php else: ?>


            <div class="no-trip">

                You don't have any upcoming trips yet.

                <br>

                <a
                    href="plan-trip.php"
                    class="view-btn"
                >

                    ✈️ Plan Your First Trip

                </a>

            </div>


        <?php endif; ?>


    </section>


    <!-- QUICK ACTIONS -->

    <h2 class="quick-title">
        ⚡ Quick Actions
    </h2>


    <section class="dashboard-grid">


        <a
            href="plan-trip.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                ✈️
            </div>

            <h3>
                Plan Trip
            </h3>

            <p>
                Create a new trip with your destination,
                travel dates and budget.
            </p>

        </a>


        <a
            href="destinations.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                🌍
            </div>

            <h3>
                Explore Destinations
            </h3>

            <p>
                Discover destinations and find
                inspiration for your next adventure.
            </p>

        </a>


        <a
            href="my-trips.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                🧳
            </div>

            <h3>
                My Trips
            </h3>

            <p>
                View, manage and organize
                all your planned trips.
            </p>

        </a>


        <a
            href="itinerary.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                🗓️
            </div>

            <h3>
                My Itinerary
            </h3>

            <p>
                Build a day-by-day plan
                for your travel activities.
            </p>

        </a>


        <a
            href="budget.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                💰
            </div>

            <h3>
                Travel Budget
            </h3>

            <p>
                Track your expenses and
                monitor your trip budget.
            </p>

        </a>


        <a
            href="profile.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                👤
            </div>

            <h3>
                My Profile
            </h3>

            <p>
                View your account information
                and travel statistics.
            </p>

        </a>


    </section>


</div>

</main>


<footer>

    © 2026 TripGenie · Smart Travel Planner

</footer>


</body>

</html>