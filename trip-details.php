<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: my-trips.php");
    exit();
}

$trip_id = intval($_GET["id"]);


/* =========================
   GET TRIP
========================= */

$stmt = $conn->prepare(
    "SELECT id, destination, start_date, end_date, budget, created_at
     FROM trips
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $trip_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    header("Location: my-trips.php");
    exit();
}

$trip = $result->fetch_assoc();
$stmt->close();


/* =========================
   GET ITINERARY COUNT
========================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM itineraries
     WHERE trip_id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $trip_id, $user_id);
$stmt->execute();

$count_result = $stmt->get_result();
$count_data = $count_result->fetch_assoc();

$activity_count = $count_data["total"];

$stmt->close();


/* =========================
   GET ITINERARY
========================= */

$stmt = $conn->prepare(
    "SELECT day, activity, location, notes
     FROM itineraries
     WHERE trip_id = ? AND user_id = ?
     ORDER BY day ASC, id ASC"
);

$stmt->bind_param("ii", $trip_id, $user_id);
$stmt->execute();

$itinerary_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($trip["destination"]); ?>
        - TripGenie
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {
            font-family: "Inter", Arial, sans-serif;
            background: #f5f7fb;
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

            backdrop-filter: blur(10px);

            border-bottom: 1px solid #e5e7eb;

            padding: 16px 5%;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }


        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #2563eb;
            text-decoration: none;
        }


        .logo span {
            color: #111827;
        }


        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }


        .nav-links a {
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;

            transition: 0.2s;
        }


        .nav-links a:hover {
            color: #2563eb;
        }


        .nav-button {
            background: #2563eb;
            color: white !important;

            padding: 10px 17px;

            border-radius: 9px;
        }


        .nav-button:hover {
            background: #1d4ed8;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {
            max-width: 1150px;
            margin: 0 auto;
            padding: 38px 20px 60px;
        }


        /* =========================
           BREADCRUMB
        ========================= */

        .breadcrumb {
            margin-bottom: 20px;

            font-size: 14px;
            color: #64748b;
        }


        .breadcrumb a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }


        /* =========================
           HERO
        ========================= */

        .hero {
            position: relative;

            overflow: hidden;

            min-height: 250px;

            padding: 45px;

            border-radius: 24px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1e3a8a
                );

            color: white;

            display: flex;
            align-items: flex-end;

            margin-bottom: 25px;

            box-shadow:
                0 15px 35px rgba(37,99,235,0.20);
        }


        .hero::before {
            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            right: -80px;
            top: -100px;

            background: rgba(255,255,255,0.08);

            border-radius: 50%;
        }


        .hero::after {
            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            right: 150px;
            bottom: -110px;

            background: rgba(255,255,255,0.07);

            border-radius: 50%;
        }


        .hero-content {
            position: relative;
            z-index: 2;
        }


        .hero-label {
            display: inline-block;

            background: rgba(255,255,255,0.15);

            border: 1px solid rgba(255,255,255,0.20);

            padding: 7px 12px;

            border-radius: 30px;

            font-size: 12px;
            font-weight: 700;

            margin-bottom: 14px;
        }


        .hero h1 {
            font-size: 38px;
            line-height: 1.2;

            margin-bottom: 10px;
        }


        .hero p {
            font-size: 15px;

            color: rgba(255,255,255,0.88);
        }


        /* =========================
           INFORMATION CARDS
        ========================= */

        .info-grid {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 16px;

            margin-bottom: 25px;
        }


        .info-card {
            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 16px;

            padding: 22px;

            box-shadow:
                0 6px 20px rgba(15,23,42,0.05);

            transition: 0.25s;
        }


        .info-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 12px 25px rgba(15,23,42,0.09);
        }


        .info-icon {
            width: 44px;
            height: 44px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eff6ff;

            border-radius: 12px;

            font-size: 22px;

            margin-bottom: 14px;
        }


        .info-title {
            color: #64748b;

            font-size: 12px;

            font-weight: 600;

            text-transform: uppercase;

            letter-spacing: 0.4px;

            margin-bottom: 7px;
        }


        .info-value {
            color: #111827;

            font-size: 17px;

            font-weight: 800;
        }


        /* =========================
           SECTION
        ========================= */

        .section {
            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 18px;

            padding: 28px;

            margin-bottom: 25px;

            box-shadow:
                0 6px 20px rgba(15,23,42,0.05);
        }


        .section-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 24px;
        }


        .section-header h2 {
            font-size: 22px;
            color: #111827;
        }


        .activity-count {
            background: #eff6ff;

            color: #2563eb;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;
        }


        /* =========================
           ITINERARY
        ========================= */

        .day {
            margin-bottom: 25px;

            position: relative;
        }


        .day:last-child {
            margin-bottom: 0;
        }


        .day-title {
            background: #2563eb;

            color: white;

            padding: 13px 17px;

            border-radius: 12px 12px 0 0;

            font-size: 14px;

            font-weight: 700;
        }


        .activity {
            padding: 18px;

            background: #f8fafc;

            border: 1px solid #e5e7eb;

            border-top: none;

            transition: 0.2s;
        }


        .activity:last-child {
            border-radius: 0 0 12px 12px;
        }


        .activity:hover {
            background: #f1f5f9;
        }


        .activity h3 {
            font-size: 16px;

            color: #111827;

            margin-bottom: 10px;
        }


        .activity p {
            color: #64748b;

            font-size: 14px;

            line-height: 1.6;

            margin-top: 7px;
        }


        .activity strong {
            color: #374151;
        }


        /* =========================
           EMPTY STATE
        ========================= */

        .empty {
            text-align: center;

            padding: 50px 20px;

            background: #f8fafc;

            border: 1px dashed #cbd5e1;

            border-radius: 14px;
        }


        .empty-icon {
            font-size: 42px;

            margin-bottom: 12px;
        }


        .empty h3 {
            color: #1f2937;

            margin-bottom: 8px;
        }


        .empty p {
            color: #64748b;

            font-size: 14px;

            margin-bottom: 20px;
        }


        /* =========================
           BUTTONS
        ========================= */

        .buttons {
            display: flex;

            gap: 12px;

            flex-wrap: wrap;
        }


        .btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 12px 18px;

            border-radius: 10px;

            text-decoration: none;

            font-size: 14px;

            font-weight: 700;

            transition: 0.2s;
        }


        .primary {
            background: #2563eb;

            color: white;

            box-shadow:
                0 5px 12px rgba(37,99,235,0.18);
        }


        .primary:hover {
            background: #1d4ed8;

            transform: translateY(-1px);
        }


        .secondary {
            background: #f1f5f9;

            color: #334155;
        }


        .secondary:hover {
            background: #e2e8f0;
        }


        /* =========================
           FOOTER NOTE
        ========================= */

        .trip-note {
            margin-top: 20px;

            color: #94a3b8;

            font-size: 12px;

            text-align: center;
        }


        /* =========================
           TABLET
        ========================= */

        @media (max-width: 900px) {

            .info-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .nav-links {
                gap: 13px;
            }

            .nav-links a {
                font-size: 13px;
            }

        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 650px) {

            .navbar {
                padding: 14px 18px;
            }


            .nav-links a:not(.nav-button) {
                display: none;
            }


            .logo {
                font-size: 21px;
            }


            .container {
                padding: 25px 15px 45px;
            }


            .hero {
                min-height: 230px;

                padding: 30px 25px;

                border-radius: 20px;
            }


            .hero h1 {
                font-size: 29px;
            }


            .hero p {
                font-size: 14px;
            }


            .info-grid {
                grid-template-columns: 1fr;
            }


            .section {
                padding: 20px;

                border-radius: 15px;
            }


            .section-header {
                align-items: flex-start;

                flex-direction: column;
            }


            .buttons {
                flex-direction: column;
            }


            .btn {
                width: 100%;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <a href="index.php" class="logo">
        Trip<span>Genie</span>
    </a>


    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="destinations.php">
            Destinations
        </a>

        <a href="my-trips.php">
            My Trips
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="plan-trip.php" class="nav-button">
            + Plan Trip
        </a>

    </div>

</nav>



<div class="container">


    <!-- =========================
         BREADCRUMB
    ========================= -->

    <div class="breadcrumb">

        <a href="my-trips.php">
            My Trips
        </a>

        &nbsp; / &nbsp;

        <?php echo htmlspecialchars($trip["destination"]); ?>

    </div>



    <!-- =========================
         HERO
    ========================= -->

    <div class="hero">

        <div class="hero-content">

            <div class="hero-label">
                ✈️ Your Travel Plan
            </div>


            <h1>
                🌍
                <?php echo htmlspecialchars($trip["destination"]); ?>
            </h1>


            <p>
                Your complete trip details and day-by-day itinerary.
            </p>

        </div>

    </div>



    <!-- =========================
         TRIP INFORMATION
    ========================= -->

    <div class="info-grid">


        <div class="info-card">

            <div class="info-icon">
                📅
            </div>

            <div class="info-title">
                Start Date
            </div>

            <div class="info-value">
                <?php
                echo htmlspecialchars(
                    date("d M Y", strtotime($trip["start_date"]))
                );
                ?>
            </div>

        </div>



        <div class="info-card">

            <div class="info-icon">
                🏁
            </div>

            <div class="info-title">
                End Date
            </div>

            <div class="info-value">
                <?php
                echo htmlspecialchars(
                    date("d M Y", strtotime($trip["end_date"]))
                );
                ?>
            </div>

        </div>



        <div class="info-card">

            <div class="info-icon">
                💰
            </div>

            <div class="info-title">
                Budget
            </div>

            <div class="info-value">

                ₹<?php
                echo number_format(
                    $trip["budget"],
                    2
                );
                ?>

            </div>

        </div>



        <div class="info-card">

            <div class="info-icon">
                🗓️
            </div>

            <div class="info-title">
                Activities
            </div>

            <div class="info-value">
                <?php echo $activity_count; ?>
            </div>

        </div>


    </div>



    <!-- =========================
         ITINERARY
    ========================= -->

    <div class="section">


        <div class="section-header">

            <h2>
                🗓️ Day-by-Day Itinerary
            </h2>

            <div class="activity-count">

                <?php echo $activity_count; ?>

                <?php
                echo ($activity_count == 1)
                    ? " Activity"
                    : " Activities";
                ?>

            </div>

        </div>



        <?php if ($itinerary_result->num_rows > 0): ?>


            <?php

            $current_day = 0;

            while ($item = $itinerary_result->fetch_assoc()):

                if ($current_day != $item["day"]):

                    if ($current_day != 0) {
                        echo "</div>";
                    }

                    $current_day = $item["day"];

            ?>


                    <div class="day">


                        <div class="day-title">

                            📅 Day
                            <?php echo $item["day"]; ?>

                        </div>


            <?php endif; ?>


                        <div class="activity">

                            <h3>

                                📍

                                <?php
                                echo htmlspecialchars(
                                    $item["activity"]
                                );
                                ?>

                            </h3>



                            <?php if (!empty($item["location"])): ?>

                                <p>

                                    <strong>
                                        Location:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $item["location"]
                                    );
                                    ?>

                                </p>

                            <?php endif; ?>



                            <?php if (!empty($item["notes"])): ?>

                                <p>

                                    <strong>
                                        Notes:
                                    </strong>

                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $item["notes"]
                                        )
                                    );
                                    ?>

                                </p>

                            <?php endif; ?>


                        </div>


            <?php endwhile; ?>


                    </div>


        <?php else: ?>


            <div class="empty">

                <div class="empty-icon">
                    🗓️
                </div>


                <h3>
                    No itinerary activities yet
                </h3>


                <p>
                    Start adding activities to create your day-by-day travel plan.
                </p>


                <a
                    href="itinerary.php?trip_id=<?php echo $trip_id; ?>"
                    class="btn primary"
                >
                    + Add Activities
                </a>

            </div>


        <?php endif; ?>


    </div>



    <!-- =========================
         MANAGE TRIP
    ========================= -->

    <div class="section">


        <div class="section-header">

            <h2>
                ⚙️ Manage Trip
            </h2>

        </div>


        <div class="buttons">


            <a
                href="itinerary.php?trip_id=<?php echo $trip_id; ?>"
                class="btn primary"
            >
                🗓️ Manage Itinerary
            </a>


            <a
                href="budget.php?trip_id=<?php echo $trip_id; ?>"
                class="btn primary"
            >
                💰 Manage Budget
            </a>


            <a
                href="my-trips.php"
                class="btn secondary"
            >
                ← Back to My Trips
            </a>


        </div>


    </div>


    <div class="trip-note">
        TripGenie • Your personal travel planning assistant
    </div>


</div>


</body>

</html>

<?php
$stmt->close();
?>