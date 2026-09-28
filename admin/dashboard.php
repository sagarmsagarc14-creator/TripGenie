<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

include "../includes/db.php";


// Admin name
$admin_name = $_SESSION["admin_name"];


// Count users
$result = $conn->query("SELECT COUNT(*) AS total FROM users");
$user_data = $result->fetch_assoc();
$total_users = $user_data["total"];


// Count trips
$result = $conn->query("SELECT COUNT(*) AS total FROM trips");
$trip_data = $result->fetch_assoc();
$total_trips = $trip_data["total"];


// Count destinations
$result = $conn->query("SELECT COUNT(*) AS total FROM destinations");
$destination_data = $result->fetch_assoc();
$total_destinations = $destination_data["total"];


// Total budget
$result = $conn->query("
    SELECT COALESCE(SUM(budget), 0) AS total
    FROM trips
");

$budget_data = $result->fetch_assoc();
$total_budget = $budget_data["total"];


// Recent users
$recent_users = $conn->query("
    SELECT name, email, created_at
    FROM users
    ORDER BY id DESC
    LIMIT 5
");


// Recent trips
$recent_trips = $conn->query("
    SELECT 
        trips.destination,
        trips.start_date,
        trips.end_date,
        trips.budget,
        users.name
    FROM trips
    INNER JOIN users
        ON trips.user_id = users.id
    ORDER BY trips.id DESC
    LIMIT 5
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard - TripGenie</title>


<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f7fb;
    color: #222;
}


/* NAVBAR */

.navbar {
    background: white;
    padding: 18px 6%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.logo {
    font-size: 27px;
    font-weight: bold;
}

.logo span {
    color: #007bff;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 15px;
}

.nav-links a {
    text-decoration: none;
    color: #333;
}

.user-name {
    font-weight: bold;
}

.logout-btn {
    background: #dc3545;
    color: white !important;
    padding: 10px 16px;
    border-radius: 7px;
}


/* CONTAINER */

.container {
    width: 88%;
    max-width: 1200px;
    margin: 35px auto;
}


/* WELCOME */

.welcome {
    background: linear-gradient(135deg, #007bff, #0056d6);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
}

.welcome h1 {
    font-size: 29px;
    margin-bottom: 8px;
}

.welcome p {
    opacity: 0.9;
}


/* STATISTICS */

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 35px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 13px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.07);
}

.stat-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.stat-card h2 {
    font-size: 27px;
    margin-bottom: 5px;
}

.stat-card p {
    color: #777;
}


/* MANAGEMENT */

.section-title {
    margin-bottom: 18px;
}

.management-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.management-card {
    background: white;
    padding: 25px;
    border-radius: 13px;
    text-decoration: none;
    color: #222;
    box-shadow: 0 3px 15px rgba(0,0,0,0.07);
    transition: 0.3s;
}

.management-card:hover {
    transform: translateY(-4px);
}

.management-card .icon {
    font-size: 35px;
    margin-bottom: 12px;
}

.management-card h3 {
    margin-bottom: 8px;
}

.management-card p {
    color: #777;
    line-height: 1.5;
}


/* RECENT SECTION */

.recent-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.recent-card {
    background: white;
    padding: 25px;
    border-radius: 13px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.07);
}

.recent-card h2 {
    margin-bottom: 20px;
}


/* USER ITEM */

.user-item {
    padding: 13px 0;
    border-bottom: 1px solid #eee;
}

.user-item:last-child {
    border-bottom: none;
}

.user-item strong {
    display: block;
    margin-bottom: 5px;
}

.user-item span {
    color: #777;
    font-size: 14px;
}


/* TRIP ITEM */

.trip-item {
    padding: 13px 0;
    border-bottom: 1px solid #eee;
}

.trip-item:last-child {
    border-bottom: none;
}

.trip-item strong {
    display: block;
    margin-bottom: 5px;
}

.trip-item span {
    display: block;
    color: #777;
    font-size: 14px;
    margin-bottom: 3px;
}


/* EMPTY */

.empty {
    color: #777;
}


/* FOOTER */

footer {
    text-align: center;
    padding: 30px;
    color: #777;
}


/* RESPONSIVE */

@media (max-width: 950px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .management-grid {
        grid-template-columns: 1fr;
    }

    .recent-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 600px) {

    .nav-links a:not(.logout-btn) {
        display: none;
    }

    .container {
        width: 92%;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .welcome h1 {
        font-size: 24px;
    }

}

</style>

</head>


<body>


<!-- NAVBAR -->

<header class="navbar">

    <div class="logo">
        Trip<span>Genie</span>
    </div>


    <nav class="nav-links">

        <span class="user-name">
            👤 <?php echo htmlspecialchars($admin_name); ?>
        </span>

        <a href="logout.php" class="logout-btn">
            Logout
        </a>

    </nav>

</header>


<div class="container">


    <!-- WELCOME -->

    <section class="welcome">

        <h1>
            👨‍💼 Welcome, <?php echo htmlspecialchars($admin_name); ?>
        </h1>

        <p>
            Manage TripGenie and monitor travel activity from your admin dashboard.
        </p>

    </section>


    <!-- STATISTICS -->

    <section class="stats">


        <div class="stat-card">

            <div class="stat-icon">
                👥
            </div>

            <h2>
                <?php echo $total_users; ?>
            </h2>

            <p>
                Registered Users
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🧳
            </div>

            <h2>
                <?php echo $total_trips; ?>
            </h2>

            <p>
                Total Trips
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🌍
            </div>

            <h2>
                <?php echo $total_destinations; ?>
            </h2>

            <p>
                Destinations
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                💰
            </div>

            <h2>
                ₹<?php echo number_format($total_budget, 2); ?>
            </h2>

            <p>
                Planned Budget
            </p>

        </div>


    </section>


    <!-- MANAGEMENT -->

    <h2 class="section-title">
        ⚡ Management
    </h2>


    <section class="management-grid">


        <a href="users.php" class="management-card">

            <div class="icon">
                👥
            </div>

            <h3>
                Manage Users
            </h3>

            <p>
                View all registered TripGenie users.
            </p>

        </a>


        <a href="destinations.php" class="management-card">

            <div class="icon">
                🌍
            </div>

            <h3>
                Manage Destinations
            </h3>

            <p>
                Add or remove available destinations.
            </p>

        </a>


        <a href="trips.php" class="management-card">

            <div class="icon">
                🧳
            </div>

            <h3>
                View Trips
            </h3>

            <p>
                View travel plans created by users.
            </p>

        </a>


    </section>


    <!-- RECENT DATA -->

    <div class="recent-grid">


        <!-- RECENT USERS -->

        <section class="recent-card">

            <h2>
                👥 Recent Users
            </h2>


            <?php if ($recent_users->num_rows > 0): ?>

                <?php while ($user = $recent_users->fetch_assoc()): ?>

                    <div class="user-item">

                        <strong>
                            <?php echo htmlspecialchars($user["name"]); ?>
                        </strong>

                        <span>
                            <?php echo htmlspecialchars($user["email"]); ?>
                        </span>

                        <span>
                            Joined:
                            <?php echo htmlspecialchars($user["created_at"]); ?>
                        </span>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="empty">
                    No users registered yet.
                </p>

            <?php endif; ?>


        </section>


        <!-- RECENT TRIPS -->

        <section class="recent-card">

            <h2>
                🧳 Recent Trips
            </h2>


            <?php if ($recent_trips->num_rows > 0): ?>

                <?php while ($trip = $recent_trips->fetch_assoc()): ?>

                    <div class="trip-item">

                        <strong>
                            <?php echo htmlspecialchars($trip["destination"]); ?>
                        </strong>

                        <span>
                            👤
                            <?php echo htmlspecialchars($trip["name"]); ?>
                        </span>

                        <span>
                            📅
                            <?php echo htmlspecialchars($trip["start_date"]); ?>
                            -
                            <?php echo htmlspecialchars($trip["end_date"]); ?>
                        </span>

                        <span>
                            💰 ₹<?php echo number_format($trip["budget"], 2); ?>
                        </span>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="empty">
                    No trips created yet.
                </p>

            <?php endif; ?>


        </section>


    </div>


</div>


<footer>

    <p>
        © 2026 TripGenie Admin Panel
    </p>

</footer>


</body>

</html>