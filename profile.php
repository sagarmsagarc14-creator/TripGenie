<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];


/* =========================
   GET USER
========================= */

$stmt = $conn->prepare("
    SELECT id, name, email, created_at
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    session_destroy();

    header("Location: login.php");

    exit();
}

$user = $result->fetch_assoc();

$stmt->close();


/* =========================
   GET USER TRIP STATS
========================= */

$tripStmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_trips,
        COALESCE(SUM(budget), 0) AS total_budget
    FROM trips
    WHERE user_id = ?
");

$tripStmt->bind_param("i", $user_id);

$tripStmt->execute();

$tripResult = $tripStmt->get_result();

$tripStats = $tripResult->fetch_assoc();

$tripStmt->close();


$total_trips =
    (int)$tripStats["total_trips"];

$total_budget =
    (float)$tripStats["total_budget"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Profile | TripGenie</title>

<link rel="stylesheet"
      href="css/style.css">

<link rel="preconnect"
      href="https://fonts.googleapis.com">

<link rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet">


<style>

/* =========================
   PAGE
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

.profile-navbar {
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

.profile-page {
    min-height: calc(100vh - 70px);

    padding: 45px 20px 70px;
}

.profile-container {
    max-width: 1050px;

    margin: auto;
}


/* =========================
   HEADER
========================= */

.profile-header {
    margin-bottom: 28px;
}

.profile-header h1 {
    margin: 0 0 8px;

    font-size: 34px;

    font-weight: 800;
}

.profile-header p {
    margin: 0;

    color: #64748b;

    font-size: 15px;
}


/* =========================
   PROFILE HERO
========================= */

.profile-hero {
    background:
        linear-gradient(
            135deg,
            #2563eb,
            #1d4ed8
        );

    color: white;

    border-radius: 20px;

    padding: 32px;

    display: flex;

    align-items: center;

    gap: 25px;

    margin-bottom: 22px;

    box-shadow:
        0 15px 35px rgba(37,99,235,0.20);
}

.profile-avatar {
    width: 90px;

    height: 90px;

    flex-shrink: 0;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: rgba(255,255,255,0.18);

    border: 2px solid rgba(255,255,255,0.4);

    font-size: 42px;
}

.profile-hero h2 {
    margin: 0 0 7px;

    font-size: 25px;

    font-weight: 800;
}

.profile-hero p {
    margin: 0;

    opacity: 0.88;

    font-size: 14px;
}


/* =========================
   STATS
========================= */

.stats-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;

    margin-bottom: 22px;
}

.stat-card {
    background: white;

    border: 1px solid #e7ebf2;

    border-radius: 16px;

    padding: 22px;

    box-shadow:
        0 6px 22px rgba(15,23,42,0.05);
}

.stat-icon {
    font-size: 25px;

    margin-bottom: 12px;
}

.stat-label {
    color: #64748b;

    font-size: 12px;

    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    margin-bottom: 6px;
}

.stat-value {
    font-size: 24px;

    font-weight: 800;

    color: #172033;
}


/* =========================
   CONTENT GRID
========================= */

.content-grid {
    display: grid;

    grid-template-columns:
        1.4fr 0.8fr;

    gap: 22px;
}


/* =========================
   CARD
========================= */

.profile-card {
    background: white;

    border: 1px solid #e7ebf2;

    border-radius: 18px;

    padding: 28px;

    box-shadow:
        0 7px 25px rgba(15,23,42,0.05);
}

.card-title {
    margin: 0 0 20px;

    font-size: 19px;

    font-weight: 800;
}


/* =========================
   PROFILE INFO
========================= */

.profile-row {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    padding: 17px 0;

    border-bottom: 1px solid #edf0f4;
}

.profile-row:last-child {
    border-bottom: none;
}

.profile-label {
    color: #64748b;

    font-size: 13px;

    font-weight: 600;
}

.profile-value {
    color: #172033;

    font-size: 14px;

    font-weight: 700;

    text-align: right;

    word-break: break-word;
}


/* =========================
   ACTIONS
========================= */

.action-list {
    display: flex;

    flex-direction: column;

    gap: 11px;
}

.action-btn {
    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 13px 15px;

    border-radius: 10px;

    border: 1px solid #e2e8f0;

    background: #f8fafc;

    color: #334155;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    transition: 0.2s ease;
}

.action-btn:hover {
    border-color: #bfdbfe;

    background: #eff6ff;

    color: #2563eb;

    transform: translateY(-1px);
}

.action-primary {
    background: #2563eb;

    color: white;

    border-color: #2563eb;
}

.action-primary:hover {
    background: #1d4ed8;

    color: white;
}

.action-danger {
    color: #dc2626;
}

.action-danger:hover {
    background: #fef2f2;

    border-color: #fecaca;

    color: #dc2626;
}


/* =========================
   FOOTER
========================= */

.profile-footer {
    text-align: center;

    margin-top: 35px;

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

    .content-grid {
        grid-template-columns: 1fr;
    }
}


@media (max-width: 600px) {

    .profile-page {
        padding: 30px 14px 50px;
    }

    .profile-header h1 {
        font-size: 27px;
    }

    .profile-hero {
        padding: 25px;

        flex-direction: column;

        align-items: flex-start;
    }

    .profile-avatar {
        width: 75px;

        height: 75px;

        font-size: 34px;
    }

    .profile-hero h2 {
        font-size: 21px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .profile-card {
        padding: 21px;
    }

    .profile-row {
        flex-direction: column;

        align-items: flex-start;

        gap: 5px;
    }

    .profile-value {
        text-align: left;
    }
}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="profile-navbar">

    <div class="nav-container">

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

            <a href="budget.php">
                Budget
            </a>

            <a href="plan-trip.php" class="nav-plan">
                + Plan Trip
            </a>

        </div>

    </div>

</nav>


<!-- =========================
     MAIN
========================= -->

<main class="profile-page">

<div class="profile-container">


    <div class="profile-header">

        <h1>
            My Profile 👤
        </h1>

        <p>
            View your TripGenie account and travel activity.
        </p>

    </div>


    <!-- PROFILE HERO -->

    <div class="profile-hero">

        <div class="profile-avatar">
            👤
        </div>

        <div>

            <h2>

                <?php

                echo htmlspecialchars(
                    $user["name"]
                );

                ?>

            </h2>

            <p>

                <?php

                echo htmlspecialchars(
                    $user["email"]
                );

                ?>

            </p>

        </div>

    </div>


    <!-- STATISTICS -->

    <div class="stats-grid">


        <div class="stat-card">

            <div class="stat-icon">
                🧳
            </div>

            <div class="stat-label">
                Total Trips
            </div>

            <div class="stat-value">

                <?php
                echo $total_trips;
                ?>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                💰
            </div>

            <div class="stat-label">
                Planned Budget
            </div>

            <div class="stat-value">

                ₹<?php

                echo number_format(
                    $total_budget,
                    2
                );

                ?>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                📅
            </div>

            <div class="stat-label">
                Member Since
            </div>

            <div class="stat-value"
                 style="font-size:18px;">

                <?php

                echo date(
                    "d M Y",
                    strtotime(
                        $user["created_at"]
                    )
                );

                ?>

            </div>

        </div>


    </div>


    <!-- CONTENT -->

    <div class="content-grid">


        <!-- ACCOUNT INFORMATION -->

        <div class="profile-card">

            <h2 class="card-title">
                Account Information
            </h2>


            <div class="profile-row">

                <div class="profile-label">
                    Full Name
                </div>

                <div class="profile-value">

                    <?php

                    echo htmlspecialchars(
                        $user["name"]
                    );

                    ?>

                </div>

            </div>


            <div class="profile-row">

                <div class="profile-label">
                    Email Address
                </div>

                <div class="profile-value">

                    <?php

                    echo htmlspecialchars(
                        $user["email"]
                    );

                    ?>

                </div>

            </div>


            <div class="profile-row">

                <div class="profile-label">
                    Account ID
                </div>

                <div class="profile-value">

                    #<?php

                    echo htmlspecialchars(
                        $user["id"]
                    );

                    ?>

                </div>

            </div>


            <div class="profile-row">

                <div class="profile-label">
                    Member Since
                </div>

                <div class="profile-value">

                    <?php

                    echo date(
                        "d M Y",
                        strtotime(
                            $user["created_at"]
                        )
                    );

                    ?>

                </div>

            </div>


        </div>


        <!-- QUICK ACTIONS -->

        <div class="profile-card">

            <h2 class="card-title">
                Quick Actions
            </h2>


            <div class="action-list">


                <a
                    href="dashboard.php"
                    class="action-btn action-primary"
                >

                    <span>
                        📊 Dashboard
                    </span>

                    <span>
                        →
                    </span>

                </a>


                <a
                    href="my-trips.php"
                    class="action-btn"
                >

                    <span>
                        🧳 My Trips
                    </span>

                    <span>
                        →
                    </span>

                </a>


                <a
                    href="budget.php"
                    class="action-btn"
                >

                    <span>
                        💰 Budget Planner
                    </span>

                    <span>
                        →
                    </span>

                </a>


                <a
                    href="destinations.php"
                    class="action-btn"
                >

                    <span>
                        🌍 Explore Destinations
                    </span>

                    <span>
                        →
                    </span>

                </a>


                <a
                    href="plan-trip.php"
                    class="action-btn"
                >

                    <span>
                        ✈️ Plan New Trip
                    </span>

                    <span>
                        →
                    </span>

                </a>


                <a
                    href="logout.php"
                    class="action-btn action-danger"
                >

                    <span>
                        🚪 Logout
                    </span>

                    <span>
                        →
                    </span>

                </a>


            </div>

        </div>


    </div>


    <div class="profile-footer">

        © 2026 TripGenie · Smart Travel Planner

    </div>


</div>

</main>


</body>

</html>