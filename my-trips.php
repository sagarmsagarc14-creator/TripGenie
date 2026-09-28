<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

$message = "";
$error = "";


/* =========================
   DELETE TRIP
========================= */

if (isset($_GET["delete"])) {

    $trip_id = intval($_GET["delete"]);

    // Make sure this trip belongs to the logged-in user
    $stmt = $conn->prepare(
        "SELECT id FROM trips WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param("ii", $trip_id, $user_id);
    $stmt->execute();

    $check = $stmt->get_result();

    if ($check->num_rows == 1) {

        $stmt->close();


        // Delete itinerary items connected to this trip
        $stmt = $conn->prepare(
            "DELETE FROM itineraries WHERE trip_id = ? AND user_id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("ii", $trip_id, $user_id);
            $stmt->execute();

            $stmt->close();
        }


        // Delete trip
        $stmt = $conn->prepare(
            "DELETE FROM trips WHERE id = ? AND user_id = ?"
        );

        $stmt->bind_param("ii", $trip_id, $user_id);

        if ($stmt->execute()) {

            $message = "Trip deleted successfully.";

        } else {

            $error = "Unable to delete trip.";
        }

        $stmt->close();

    } else {

        $stmt->close();

        $error = "Trip not found.";
    }
}


/* =========================
   GET USER TRIPS
========================= */

$stmt = $conn->prepare(
    "SELECT id, destination, start_date, end_date, budget, created_at
     FROM trips
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$total_trips = $result->num_rows;

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Trips - TripGenie</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .navbar {
            background: #2563eb;
            color: white;
            padding: 18px 40px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 25px;
            font-weight: bold;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin-bottom: 7px;
        }

        .subtitle {
            color: #6b7280;
        }

        .trip-count {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: bold;
        }

        .message {
            background: #dcfce7;
            color: #166534;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .trips {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .trip-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .trip-icon {
            font-size: 35px;
            margin-bottom: 15px;
        }

        .trip-card h2 {
            margin-bottom: 15px;
            color: #1f2937;
        }

        .trip-info {
            margin-bottom: 10px;
            color: #4b5563;
        }

        .trip-info strong {
            color: #1f2937;
        }

        .budget {
            font-size: 20px;
            font-weight: bold;
            color: #16a34a;
            margin: 15px 0;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .itinerary-btn,
        .delete-btn {
            flex: 1;
            padding: 10px;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .itinerary-btn {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
        }

        .itinerary-btn:hover {
            background: #bfdbfe;
        }

        .delete-btn:hover {
            background: #fecaca;
        }

        .empty {
            background: white;
            padding: 60px 30px;
            text-align: center;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .empty-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty h2 {
            margin-bottom: 10px;
        }

        .empty p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        .plan-btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .back {
            display: inline-block;
            margin-top: 30px;
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 900px) {

            .trips {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 650px) {

            .navbar {
                padding: 15px 20px;
            }

            .nav-links a {
                margin-left: 8px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .trips {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        TripGenie
    </div>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="plan-trip.php">
            Plan Trip
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<div class="container">


    <div class="header">

        <div>

            <h1>🧳 My Trips</h1>

            <p class="subtitle">
                View and manage your planned trips.
            </p>

        </div>

        <div class="trip-count">

            <?php echo $total_trips; ?>

            <?php echo ($total_trips == 1) ? " Trip" : " Trips"; ?>

        </div>

    </div>


    <?php if (!empty($message)): ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if ($total_trips > 0): ?>


        <div class="trips">


            <?php while ($trip = $result->fetch_assoc()): ?>


                <div class="trip-card">


                    <div class="trip-icon">
                        🌍
                    </div>


                    <h2>
                        <?php echo htmlspecialchars($trip["destination"]); ?>
                    </h2>


                    <div class="trip-info">

                        📅

                        <strong>Start:</strong>

                        <?php echo htmlspecialchars($trip["start_date"]); ?>

                    </div>


                    <div class="trip-info">

                        📅

                        <strong>End:</strong>

                        <?php echo htmlspecialchars($trip["end_date"]); ?>

                    </div>


                    <div class="budget">

                        💰 ₹<?php echo number_format($trip["budget"], 2); ?>

                    </div>


                    <div class="actions">
<a
    href="trip-details.php?id=<?php echo $trip["id"]; ?>"
    class="itinerary-btn"
>
    👁️ Details
</a>

                        <a
                            href="itinerary.php?trip_id=<?php echo $trip["id"]; ?>"
                            class="itinerary-btn"
                        >
                            🗓️ Itinerary
                        </a>


                        <a
                            href="my-trips.php?delete=<?php echo $trip["id"]; ?>"
                            class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete this trip? Its itinerary will also be deleted.');"
                        >
                            🗑️ Delete
                        </a>


                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="empty">

            <div class="empty-icon">
                🧳
            </div>

            <h2>
                No Trips Yet
            </h2>

            <p>
                Start planning your next adventure with TripGenie.
            </p>

            <a href="plan-trip.php" class="plan-btn">
                ✈️ Plan Your First Trip
            </a>

        </div>


    <?php endif; ?>


    <a href="dashboard.php" class="back">
        ← Back to Dashboard
    </a>


</div>


</body>

</html>

<?php
$stmt->close();
?>