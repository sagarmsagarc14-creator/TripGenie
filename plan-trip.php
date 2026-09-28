<?php

session_start();

require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$messageType = "";

$destinations = $conn->query(
    "SELECT name, location FROM destinations ORDER BY name ASC"
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION["user_id"];

    $destination = trim($_POST["destination"] ?? "");
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    $budget = $_POST["budget"] ?? "";

    if (
        empty($destination) ||
        empty($start_date) ||
        empty($end_date) ||
        empty($budget)
    ) {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif ($end_date < $start_date) {

        $message = "End date cannot be before start date.";
        $messageType = "error";

    } elseif ($budget <= 0) {

        $message = "Please enter a valid budget.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO trips
            (user_id, destination, start_date, end_date, budget)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isssd",
            $user_id,
            $destination,
            $start_date,
            $end_date,
            $budget
        );

        if ($stmt->execute()) {

            $message = "Trip planned successfully! 🎉";
            $messageType = "success";

        } else {

            $message = "Unable to save your trip.";
            $messageType = "error";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Plan Your Trip - TripGenie</title>

    <link rel="stylesheet" href="css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

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
            gap: 20px;
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
           PAGE
        ========================= */

        .plan-page {
            min-height: calc(100vh - 70px);
            padding: 55px 20px 70px;
        }

        .plan-container {
            max-width: 1050px;
            margin: auto;

            display: grid;
            grid-template-columns: 0.9fr 1.1fr;

            background: white;
            border-radius: 24px;
            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(15, 23, 42, 0.10);

            border: 1px solid #eef2f7;
        }

        /* =========================
           LEFT SIDE
        ========================= */

        .plan-intro {
            padding: 45px 38px;

            background:
                linear-gradient(
                    145deg,
                    #2563eb,
                    #1d4ed8 55%,
                    #1e40af
                );

            color: white;

            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .intro-icon {
            width: 60px;
            height: 60px;

            border-radius: 16px;

            background: rgba(255,255,255,0.16);

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 29px;

            margin-bottom: 25px;
        }

        .plan-intro h1 {
            margin: 0 0 15px;

            font-size: 38px;
            line-height: 1.15;
            font-weight: 800;
        }

        .plan-intro p {
            margin: 0;

            color: #dbeafe;

            font-size: 15px;
            line-height: 1.7;
        }

        .benefits {
            margin-top: 30px;
            display: grid;
            gap: 15px;
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 12px;

            font-size: 14px;
            font-weight: 600;
        }

        .benefit-icon {
            width: 34px;
            height: 34px;

            background: rgba(255,255,255,0.15);

            border-radius: 9px;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* =========================
           FORM SIDE
        ========================= */

        .plan-form-area {
            padding: 45px 40px;
        }

        .form-heading {
            margin-bottom: 28px;
        }

        .form-heading h2 {
            margin: 0 0 7px;

            font-size: 25px;
            font-weight: 800;
        }

        .form-heading p {
            margin: 0;

            color: #64748b;
            font-size: 14px;
        }

        /* =========================
           MESSAGE
        ========================= */

        .plan-message {
            padding: 13px 16px;

            border-radius: 10px;

            margin-bottom: 22px;

            font-size: 14px;
            font-weight: 600;

            text-align: center;
        }

        .plan-message.success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .plan-message.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* =========================
           FORM
        ========================= */

        .trip-form {
            display: grid;
            gap: 20px;
        }

        .form-group label {
            display: block;

            margin-bottom: 8px;

            color: #334155;

            font-size: 14px;
            font-weight: 700;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;

            left: 14px;
            top: 50%;

            transform: translateY(-50%);

            font-size: 17px;

            pointer-events: none;
        }

        .form-group input,
        .form-group select {

            width: 100%;

            padding: 14px 15px;

            border: 1px solid #dbe1ea;

            border-radius: 11px;

            background: #f8fafc;

            color: #1e293b;

            font-size: 14px;

            outline: none;

            transition: 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {

            background: white;

            border-color: #2563eb;

            box-shadow:
                0 0 0 4px rgba(37, 99, 235, 0.10);
        }

        .date-row {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 16px;
        }

        .budget-hint {
            margin-top: 7px;

            color: #64748b;

            font-size: 12px;
        }

        /* =========================
           BUTTON
        ========================= */

        .save-trip-btn {

            width: 100%;

            border: none;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );

            color: white;

            padding: 15px;

            border-radius: 11px;

            font-size: 15px;

            font-weight: 800;

            cursor: pointer;

            box-shadow:
                0 8px 20px rgba(37,99,235,0.22);

            transition: 0.25s;
        }

        .save-trip-btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 25px rgba(37,99,235,0.28);
        }

        /* =========================
           FOOTER
        ========================= */

        footer {

            background: white;

            border-top: 1px solid #e5e7eb;

            padding: 20px;

            text-align: center;

            color: #64748b;

            font-size: 13px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 850px) {

            .plan-container {
                grid-template-columns: 1fr;
            }

            .plan-intro {
                padding: 35px;
            }

            .plan-intro h1 {
                font-size: 32px;
            }

        }

        @media (max-width: 650px) {

            .nav-links a:nth-child(1),
            .nav-links a:nth-child(2),
            .nav-links a:nth-child(4) {
                display: none;
            }

            .nav-links {
                gap: 10px;
            }

            .nav-plan {
                padding: 9px 12px;
            }

            .plan-page {
                padding: 25px 15px 45px;
            }

            .plan-intro {
                padding: 30px 25px;
            }

            .plan-intro h1 {
                font-size: 29px;
            }

            .plan-form-area {
                padding: 30px 22px;
            }

            .date-row {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 420px) {

            .logo {
                font-size: 21px;
            }

            .plan-intro h1 {
                font-size: 26px;
            }

            .benefits {
                margin-top: 22px;
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

            <a href="plan-trip.php" class="nav-plan">
                + Plan Trip
            </a>

        </div>

    </div>

</nav>


<!-- =========================
     MAIN
========================= -->

<main class="plan-page">

    <div class="plan-container">

        <!-- LEFT INTRO -->

        <section class="plan-intro">

            <div class="intro-icon">
                ✈️
            </div>

            <h1>
                Plan your next adventure.
            </h1>

            <p>
                Choose your destination, travel dates and budget.
                TripGenie will save your travel plan so you can
                manage it anytime.
            </p>

            <div class="benefits">

                <div class="benefit">

                    <div class="benefit-icon">
                        🌍
                    </div>

                    <span>
                        Choose from amazing destinations
                    </span>

                </div>


                <div class="benefit">

                    <div class="benefit-icon">
                        📅
                    </div>

                    <span>
                        Set your travel dates
                    </span>

                </div>


                <div class="benefit">

                    <div class="benefit-icon">
                        💰
                    </div>

                    <span>
                        Set your travel budget
                    </span>

                </div>


                <div class="benefit">

                    <div class="benefit-icon">
                        💾
                    </div>

                    <span>
                        Save and manage your trip
                    </span>

                </div>

            </div>

        </section>


        <!-- FORM -->

        <section class="plan-form-area">

            <div class="form-heading">

                <h2>
                    Create Your Trip
                </h2>

                <p>
                    Enter your travel details below.
                </p>

            </div>


            <?php if (!empty($message)): ?>

                <div class="plan-message <?php echo $messageType; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="trip-form"
            >

                <!-- DESTINATION -->

                <div class="form-group">

                    <label for="destination">
                        🌍 Destination
                    </label>

                    <select
                        id="destination"
                        name="destination"
                        required
                    >

                        <option value="">
                            Select your destination
                        </option>

                        <?php if ($destinations): ?>

                            <?php while ($place = $destinations->fetch_assoc()): ?>

                                <option
                                    value="<?php echo htmlspecialchars($place["name"]); ?>"
                                    <?php
                                    if (
                                        isset($_POST["destination"]) &&
                                        $_POST["destination"] === $place["name"]
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars($place["name"]);
                                    ?>

                                    -

                                    <?php
                                    echo htmlspecialchars($place["location"]);
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                </div>


                <!-- DATES -->

                <div class="date-row">

                    <div class="form-group">

                        <label for="start_date">
                            📅 Start Date
                        </label>

                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="<?php echo htmlspecialchars($_POST["start_date"] ?? ""); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="end_date">
                            📅 End Date
                        </label>

                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="<?php echo htmlspecialchars($_POST["end_date"] ?? ""); ?>"
                            required
                        >

                    </div>

                </div>


                <!-- BUDGET -->

                <div class="form-group">

                    <label for="budget">
                        💰 Travel Budget
                    </label>

                    <input
                        type="number"
                        id="budget"
                        name="budget"
                        placeholder="Example: 25000"
                        min="1"
                        step="0.01"
                        value="<?php echo htmlspecialchars($_POST["budget"] ?? ""); ?>"
                        required
                    >

                    <div class="budget-hint">
                        Enter the approximate total amount you want to spend.
                    </div>

                </div>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="save-trip-btn"
                >
                    ✈️ Create My Trip
                </button>

            </form>

        </section>

    </div>

</main>


<footer>

    <p>
        © 2026 TripGenie. Smart Travel Planner.
    </p>

</footer>


<script>

    const startDate = document.getElementById("start_date");
    const endDate = document.getElementById("end_date");

    function updateEndDate() {

        if (startDate.value) {

            endDate.min = startDate.value;

        }

    }

    startDate.addEventListener("change", updateEndDate);

    updateEndDate();

</script>

</body>

</html>