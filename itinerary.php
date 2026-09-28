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
   CREATE ITINERARIES TABLE
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
   DELETE ACTIVITY
========================= */

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);
    $selected_delete_trip = isset($_GET["trip_id"])
        ? intval($_GET["trip_id"])
        : 0;

    $stmt = $conn->prepare(
        "DELETE FROM itineraries
         WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        $message = "Activity deleted successfully.";
    } else {
        $error = "Unable to delete activity.";
    }

    $stmt->close();

    if ($selected_delete_trip > 0) {
        $redirect_trip = $selected_delete_trip;
    }
}


/* =========================
   ADD ITINERARY
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $trip_id = intval($_POST["trip_id"]);
    $day = intval($_POST["day"]);
    $activity = trim($_POST["activity"]);
    $location = trim($_POST["location"]);
    $notes = trim($_POST["notes"]);

    if ($trip_id <= 0 || $day <= 0 || empty($activity)) {

        $error = "Please fill all required fields.";

    } else {

        /* Check trip ownership */

        $check = $conn->prepare(
            "SELECT id
             FROM trips
             WHERE id = ? AND user_id = ?"
        );

        $check->bind_param("ii", $trip_id, $user_id);
        $check->execute();

        $trip_result = $check->get_result();

        if ($trip_result->num_rows == 0) {

            $error = "Invalid trip selected.";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO itineraries
                (user_id, trip_id, day, activity, location, notes)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiisss",
                $user_id,
                $trip_id,
                $day,
                $activity,
                $location,
                $notes
            );

            if ($stmt->execute()) {
                $message = "Activity added to your itinerary.";
            } else {
                $error = "Unable to add activity.";
            }

            $stmt->close();
        }

        $check->close();
    }

    $selected_trip_id = $trip_id;
}


/* =========================
   GET USER TRIPS
========================= */

$stmt = $conn->prepare(
    "SELECT id, destination, start_date, end_date
     FROM trips
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$trips_result = $stmt->get_result();

$trips = [];

while ($trip = $trips_result->fetch_assoc()) {
    $trips[] = $trip;
}

$stmt->close();


/* =========================
   SELECT TRIP
========================= */

if (!isset($selected_trip_id)) {
    $selected_trip_id = 0;
}

if (isset($_GET["trip_id"])) {
    $selected_trip_id = intval($_GET["trip_id"]);
}


/* =========================
   VERIFY SELECTED TRIP
========================= */

$selected_trip = null;

foreach ($trips as $trip) {

    if ($trip["id"] == $selected_trip_id) {

        $selected_trip = $trip;

        break;
    }
}


/* =========================
   GET ITINERARY
========================= */

$itinerary = [];

if ($selected_trip) {

    $stmt = $conn->prepare(
        "SELECT id, day, activity, location, notes, created_at
         FROM itineraries
         WHERE trip_id = ? AND user_id = ?
         ORDER BY day ASC, id ASC"
    );

    $stmt->bind_param(
        "ii",
        $selected_trip_id,
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $itinerary[] = $item;
    }

    $stmt->close();
}


/* =========================
   COUNT ACTIVITIES
========================= */

$activity_count = count($itinerary);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Itinerary - TripGenie</title>


    <link rel="preconnect"
          href="https://fonts.googleapis.com">

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
            color: #2563eb;

            font-size: 24px;

            font-weight: 800;

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

            color: white !important;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {
            max-width: 1150px;

            margin: auto;

            padding: 38px 20px 60px;
        }


        /* =========================
           PAGE HEADER
        ========================= */

        .page-header {
            margin-bottom: 25px;
        }


        .breadcrumb {
            font-size: 13px;

            color: #64748b;

            margin-bottom: 12px;
        }


        .breadcrumb a {
            color: #2563eb;

            text-decoration: none;

            font-weight: 600;
        }


        .page-header h1 {
            font-size: 34px;

            color: #111827;

            margin-bottom: 8px;
        }


        .page-header p {
            color: #64748b;

            font-size: 15px;
        }


        /* =========================
           ALERTS
        ========================= */

        .message,
        .error {
            padding: 14px 17px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            font-weight: 600;
        }


        .message {
            background: #dcfce7;

            color: #166534;

            border: 1px solid #bbf7d0;
        }


        .error {
            background: #fee2e2;

            color: #b91c1c;

            border: 1px solid #fecaca;
        }


        /* =========================
           MAIN GRID
        ========================= */

        .main-grid {
            display: grid;

            grid-template-columns: 330px 1fr;

            gap: 25px;

            align-items: start;
        }


        /* =========================
           CARD
        ========================= */

        .card {
            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 18px;

            padding: 25px;

            box-shadow:
                0 6px 20px rgba(15,23,42,0.05);

            margin-bottom: 20px;
        }


        .card h2 {
            font-size: 19px;

            color: #111827;

            margin-bottom: 20px;
        }


        /* =========================
           FORM
        ========================= */

        label {
            display: block;

            font-size: 13px;

            font-weight: 700;

            color: #374151;

            margin-bottom: 7px;
        }


        select,
        input,
        textarea {
            width: 100%;

            padding: 12px 13px;

            border: 1px solid #d1d5db;

            border-radius: 9px;

            background: white;

            color: #111827;

            font-family: inherit;

            font-size: 14px;

            margin-bottom: 18px;

            transition: 0.2s;
        }


        select:focus,
        input:focus,
        textarea:focus {
            outline: none;

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37,99,235,0.10);
        }


        textarea {
            min-height: 100px;

            resize: vertical;
        }


        .add-btn {
            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 10px;

            background: #2563eb;

            color: white;

            font-family: inherit;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s;
        }


        .add-btn:hover {
            background: #1d4ed8;

            transform: translateY(-1px);
        }


        /* =========================
           TRIP SUMMARY
        ========================= */

        .trip-summary {
            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1e3a8a
                );

            color: white;

            border-radius: 18px;

            padding: 25px;

            margin-bottom: 20px;

            box-shadow:
                0 12px 25px rgba(37,99,235,0.18);
        }


        .trip-summary small {
            display: block;

            color: rgba(255,255,255,0.75);

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 0.8px;

            font-weight: 700;

            margin-bottom: 8px;
        }


        .trip-summary h2 {
            font-size: 24px;

            margin-bottom: 12px;
        }


        .trip-date {
            color: rgba(255,255,255,0.9);

            font-size: 13px;

            line-height: 1.6;
        }


        .summary-stats {
            display: flex;

            gap: 10px;

            margin-top: 18px;
        }


        .summary-stat {
            background: rgba(255,255,255,0.12);

            padding: 10px 12px;

            border-radius: 9px;

            flex: 1;
        }


        .summary-stat strong {
            display: block;

            font-size: 17px;
        }


        .summary-stat span {
            font-size: 10px;

            color: rgba(255,255,255,0.75);
        }


        /* =========================
           ITINERARY HEADER
        ========================= */

        .itinerary-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            margin-bottom: 20px;
        }


        .itinerary-header h2 {
            margin: 0;
        }


        .activity-count {
            background: #eff6ff;

            color: #2563eb;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;
        }


        /* =========================
           DAY SECTION
        ========================= */

        .day-section {
            margin-bottom: 25px;
        }


        .day-section:last-child {
            margin-bottom: 0;
        }


        .day-title {
            background: #2563eb;

            color: white;

            padding: 13px 16px;

            border-radius: 11px 11px 0 0;

            font-size: 14px;

            font-weight: 700;
        }


        .activity {
            background: #f8fafc;

            border: 1px solid #e5e7eb;

            border-top: none;

            padding: 18px;

            transition: 0.2s;
        }


        .activity:last-child {
            border-radius: 0 0 11px 11px;
        }


        .activity:hover {
            background: #f1f5f9;
        }


        .activity h3 {
            color: #111827;

            font-size: 16px;

            margin-bottom: 9px;
        }


        .activity p {
            color: #64748b;

            font-size: 13px;

            line-height: 1.6;

            margin-top: 6px;
        }


        .activity strong {
            color: #374151;
        }


        .activity-actions {
            margin-top: 13px;
        }


        .delete-btn {
            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 7px 11px;

            background: #fee2e2;

            color: #dc2626;

            text-decoration: none;

            border-radius: 7px;

            font-size: 12px;

            font-weight: 700;

            transition: 0.2s;
        }


        .delete-btn:hover {
            background: #fecaca;
        }


        /* =========================
           EMPTY
        ========================= */

        .empty {
            text-align: center;

            padding: 45px 20px;

            background: #f8fafc;

            border: 1px dashed #cbd5e1;

            border-radius: 13px;

            color: #64748b;
        }


        .empty-icon {
            font-size: 42px;

            margin-bottom: 12px;
        }


        .empty h3 {
            color: #1f2937;

            margin-bottom: 7px;
        }


        .empty p {
            font-size: 13px;

            margin-bottom: 18px;
        }


        .empty-btn {
            display: inline-flex;

            padding: 10px 15px;

            background: #2563eb;

            color: white;

            text-decoration: none;

            border-radius: 8px;

            font-size: 13px;

            font-weight: 700;
        }


        /* =========================
           FOOTER LINK
        ========================= */

        .back-link {
            display: inline-flex;

            margin-top: 5px;

            color: #2563eb;

            text-decoration: none;

            font-size: 14px;

            font-weight: 700;
        }


        .back-link:hover {
            text-decoration: underline;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 850px) {

            .main-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 650px) {

            .navbar {
                padding: 14px 18px;
            }


            .nav-links {
                gap: 10px;
            }


            .nav-links a:not(.nav-button) {
                display: none;
            }


            .logo {
                font-size: 21px;
            }


            .container {
                padding: 28px 15px 45px;
            }


            .page-header h1 {
                font-size: 28px;
            }


            .card {
                padding: 20px;
            }


            .itinerary-header {
                align-items: flex-start;

                flex-direction: column;
            }


            .summary-stats {
                flex-direction: row;
            }

        }


        @media (max-width: 400px) {

            .summary-stats {
                flex-direction: column;
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
         PAGE HEADER
    ========================= -->

    <div class="page-header">

        <div class="breadcrumb">

            <a href="dashboard.php">
                Dashboard
            </a>

            &nbsp; / &nbsp;

            My Itinerary

        </div>


        <h1>
            🗓️ My Itinerary
        </h1>


        <p>
            Build and organize your travel plans day by day.
        </p>

    </div>



    <!-- =========================
         ALERTS
    ========================= -->

    <?php if (!empty($message)): ?>

        <div class="message">

            ✅
            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="error">

            ⚠️
            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>



    <?php if (count($trips) > 0): ?>


        <div class="main-grid">


            <!-- =========================
                 LEFT COLUMN
            ========================= -->

            <div>


                <!-- SELECT TRIP -->

                <div class="card">

                    <h2>
                        ✈️ Choose Your Trip
                    </h2>


                    <form method="GET">

                        <label>
                            Select Trip
                        </label>


                        <select
                            name="trip_id"
                            onchange="this.form.submit()"
                            required
                        >

                            <option value="">
                                -- Select a Trip --
                            </option>


                            <?php foreach ($trips as $trip): ?>

                                <option
                                    value="<?php echo $trip["id"]; ?>"
                                    <?php
                                    if (
                                        $selected_trip_id
                                        == $trip["id"]
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $trip["destination"]
                                    );
                                    ?>

                                    -
                                    <?php
                                    echo htmlspecialchars(
                                        $trip["start_date"]
                                    );
                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </form>

                </div>



                <?php if ($selected_trip): ?>


                    <!-- ADD ACTIVITY -->

                    <div class="card">

                        <h2>
                            ➕ Add Activity
                        </h2>


                        <form method="POST">

                            <input
                                type="hidden"
                                name="trip_id"
                                value="<?php
                                echo $selected_trip_id;
                                ?>"
                            >


                            <label>
                                Day
                            </label>

                            <input
                                type="number"
                                name="day"
                                min="1"
                                placeholder="Example: 1"
                                required
                            >


                            <label>
                                Activity
                            </label>

                            <input
                                type="text"
                                name="activity"
                                placeholder="Example: Visit Baga Beach"
                                required
                            >


                            <label>
                                Location
                            </label>

                            <input
                                type="text"
                                name="location"
                                placeholder="Example: Baga, Goa"
                            >


                            <label>
                                Notes
                            </label>

                            <textarea
                                name="notes"
                                placeholder="Example: Visit in the evening"
                            ></textarea>


                            <button
                                type="submit"
                                class="add-btn"
                            >
                                + Add to Itinerary
                            </button>

                        </form>

                    </div>


                <?php endif; ?>


            </div>



            <!-- =========================
                 RIGHT COLUMN
            ========================= -->

            <div>


                <?php if ($selected_trip): ?>


                    <!-- TRIP SUMMARY -->

                    <div class="trip-summary">

                        <small>
                            Selected Trip
                        </small>


                        <h2>

                            🌍

                            <?php
                            echo htmlspecialchars(
                                $selected_trip["destination"]
                            );
                            ?>

                        </h2>


                        <div class="trip-date">

                            📅

                            <?php
                            echo date(
                                "d M Y",
                                strtotime(
                                    $selected_trip["start_date"]
                                )
                            );
                            ?>

                            →

                            <?php
                            echo date(
                                "d M Y",
                                strtotime(
                                    $selected_trip["end_date"]
                                )
                            );
                            ?>

                        </div>


                        <div class="summary-stats">

                            <div class="summary-stat">

                                <strong>
                                    <?php
                                    echo $activity_count;
                                    ?>
                                </strong>

                                <span>
                                    Activities
                                </span>

                            </div>


                            <div class="summary-stat">

                                <strong>
                                    <?php
                                    echo count(
                                        array_unique(
                                            array_column(
                                                $itinerary,
                                                "day"
                                            )
                                        )
                                    );
                                    ?>
                                </strong>

                                <span>
                                    Planned Days
                                </span>

                            </div>

                        </div>

                    </div>



                    <!-- ITINERARY -->

                    <div class="card">

                        <div class="itinerary-header">

                            <h2>
                                📋 Day-by-Day Plan
                            </h2>


                            <div class="activity-count">

                                <?php
                                echo $activity_count;
                                ?>

                                <?php
                                echo (
                                    $activity_count == 1
                                )
                                    ? " Activity"
                                    : " Activities";
                                ?>

                            </div>

                        </div>



                        <?php if (count($itinerary) > 0): ?>


                            <?php

                            $current_day = 0;

                            foreach (
                                $itinerary as $item
                            ):

                                if (
                                    $current_day
                                    != $item["day"]
                                ):

                                    if (
                                        $current_day != 0
                                    ) {
                                        echo "</div>";
                                    }

                                    $current_day =
                                        $item["day"];

                            ?>

                                    <div class="day-section">


                                        <div class="day-title">

                                            📅 Day

                                            <?php
                                            echo $item["day"];
                                            ?>

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



                                            <?php
                                            if (
                                                !empty(
                                                    $item["location"]
                                                )
                                            ):
                                            ?>

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



                                            <?php
                                            if (
                                                !empty(
                                                    $item["notes"]
                                                )
                                            ):
                                            ?>

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



                                            <div
                                                class="activity-actions"
                                            >

                                                <a
                                                    href="itinerary.php?trip_id=<?php echo $selected_trip_id; ?>&delete=<?php echo $item["id"]; ?>"
                                                    class="delete-btn"

                                                    onclick="return confirm('Are you sure you want to delete this activity?');"
                                                >

                                                    🗑️ Delete

                                                </a>

                                            </div>


                                        </div>


                            <?php endforeach; ?>


                                    </div>


                        <?php else: ?>


                            <div class="empty">

                                <div class="empty-icon">
                                    🗓️
                                </div>


                                <h3>
                                    No activities yet
                                </h3>


                                <p>
                                    Add your first activity using the form.
                                </p>

                            </div>


                        <?php endif; ?>


                    </div>


                <?php else: ?>


                    <!-- NO TRIP SELECTED -->

                    <div class="card">

                        <div class="empty">

                            <div class="empty-icon">
                                ✈️
                            </div>


                            <h3>
                                Select a trip to begin
                            </h3>


                            <p>
                                Choose one of your trips from the left to create your itinerary.
                            </p>

                        </div>

                    </div>


                <?php endif; ?>


            </div>


        </div>


    <?php else: ?>


        <!-- NO TRIPS -->

        <div class="card">

            <div class="empty">

                <div class="empty-icon">
                    🧳
                </div>


                <h3>
                    No trips available
                </h3>


                <p>
                    Create your first trip before building an itinerary.
                </p>


                <a
                    href="plan-trip.php"
                    class="empty-btn"
                >
                    ✈️ Plan a Trip
                </a>

            </div>

        </div>


    <?php endif; ?>


    <a
        href="dashboard.php"
        class="back-link"
    >
        ← Back to Dashboard
    </a>


</div>


</body>

</html>