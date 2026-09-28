<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

include "../includes/db.php";


// DELETE TRIP

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare("
        DELETE FROM trips
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: trips.php");
    exit();
}


// GET ALL TRIPS

$result = $conn->query("
    SELECT
        trips.id,
        trips.destination,
        trips.start_date,
        trips.end_date,
        trips.budget,
        trips.created_at,
        users.name,
        users.email
    FROM trips
    INNER JOIN users
        ON trips.user_id = users.id
    ORDER BY trips.id DESC
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Trips - TripGenie</title>

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

.back-btn {
    background: #007bff;
    color: white !important;
    padding: 10px 16px;
    border-radius: 7px;
}


/* CONTAINER */

.container {
    width: 92%;
    max-width: 1250px;
    margin: 35px auto;
}


/* HEADER */

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    font-size: 30px;
    margin-bottom: 8px;
}

.page-header p {
    color: #777;
}


/* TABLE */

.table-card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.07);
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

th {
    background: #007bff;
    color: white;
    padding: 14px;
    text-align: left;
    white-space: nowrap;
}

td {
    padding: 14px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

tr:hover {
    background: #f8faff;
}


/* USER */

.user-name {
    font-weight: bold;
}

.user-email {
    display: block;
    color: #777;
    font-size: 13px;
    margin-top: 4px;
}


/* DESTINATION */

.destination {
    font-weight: bold;
    color: #007bff;
}


/* BUDGET */

.budget {
    font-weight: bold;
}


/* DELETE */

.delete-btn {
    display: inline-block;
    background: #dc3545;
    color: white;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
}

.delete-btn:hover {
    background: #c82333;
}


/* EMPTY */

.empty {
    text-align: center;
    padding: 40px;
    color: #777;
}


/* FOOTER */

footer {
    text-align: center;
    padding: 30px;
    color: #777;
}


/* MOBILE */

@media (max-width: 700px) {

    .nav-links {
        display: none;
    }

    .container {
        width: 94%;
    }

    .page-header h1 {
        font-size: 25px;
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

        <a href="dashboard.php">
            Admin Dashboard
        </a>

        <a href="../dashboard.php" class="back-btn">
            User Dashboard
        </a>

    </nav>

</header>


<div class="container">


    <!-- HEADER -->

    <div class="page-header">

        <h1>
            🧳 Manage Trips
        </h1>

        <p>
            View and manage travel plans created by TripGenie users.
        </p>

    </div>


    <!-- TABLE -->

    <div class="table-card">

        <?php if ($result->num_rows > 0): ?>

        <table>

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        User
                    </th>

                    <th>
                        Destination
                    </th>

                    <th>
                        Start Date
                    </th>

                    <th>
                        End Date
                    </th>

                    <th>
                        Budget
                    </th>

                    <th>
                        Created
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php while ($trip = $result->fetch_assoc()): ?>


                <tr>


                    <td>
                        #<?php echo $trip["id"]; ?>
                    </td>


                    <td>

                        <span class="user-name">

                            <?php
                            echo htmlspecialchars($trip["name"]);
                            ?>

                        </span>

                        <span class="user-email">

                            <?php
                            echo htmlspecialchars($trip["email"]);
                            ?>

                        </span>

                    </td>


                    <td class="destination">

                        📍
                        <?php
                        echo htmlspecialchars($trip["destination"]);
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars($trip["start_date"]);
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars($trip["end_date"]);
                        ?>

                    </td>


                    <td class="budget">

                        ₹<?php
                        echo number_format($trip["budget"], 2);
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars($trip["created_at"]);
                        ?>

                    </td>


                    <td>

                        <a
                            href="trips.php?delete=<?php echo $trip["id"]; ?>"
                            class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete this trip?');"
                        >
                            🗑️ Delete
                        </a>

                    </td>


                </tr>


            <?php endwhile; ?>


            </tbody>

        </table>


        <?php else: ?>


            <div class="empty">

                <h3>
                    🧳 No trips found
                </h3>

                <p>
                    Users have not created any trips yet.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


<footer>

    <p>
        © 2026 TripGenie Admin Panel
    </p>

</footer>


</body>

</html>