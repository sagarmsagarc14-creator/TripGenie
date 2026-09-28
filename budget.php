<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

/* =========================
   GET USER TRIPS
========================= */

$stmt = $conn->prepare("
    SELECT id, destination, start_date, end_date, budget
    FROM trips
    WHERE user_id = ?
    ORDER BY start_date ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$trips = [];

while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}

$stmt->close();


/* =========================
   SELECTED TRIP
========================= */

$selected_trip_id = isset($_GET["trip_id"])
    ? intval($_GET["trip_id"])
    : 0;

if ($selected_trip_id === 0 && !empty($trips)) {
    $selected_trip_id = $trips[0]["id"];
}


/* =========================
   FIND SELECTED TRIP
========================= */

$selected_trip = null;

foreach ($trips as $trip) {

    if ($trip["id"] == $selected_trip_id) {
        $selected_trip = $trip;
        break;
    }
}


/* =========================
   DEFAULT EXPENSES
========================= */

$hotel = 0;
$transport = 0;
$food = 0;
$activities = 0;


/* =========================
   LOAD SAVED EXPENSES
========================= */

if ($selected_trip) {

    $expenseStmt = $conn->prepare("
        SELECT hotel, transport, food, activities
        FROM expenses
        WHERE trip_id = ? AND user_id = ?
        LIMIT 1
    ");

    $expenseStmt->bind_param(
        "ii",
        $selected_trip_id,
        $user_id
    );

    $expenseStmt->execute();

    $expenseResult = $expenseStmt->get_result();

    if ($expenseResult->num_rows > 0) {

        $expense = $expenseResult->fetch_assoc();

        $hotel = $expense["hotel"];
        $transport = $expense["transport"];
        $food = $expense["food"];
        $activities = $expense["activities"];
    }

    $expenseStmt->close();
}


/* =========================
   INITIAL TOTALS
========================= */

$total_budget = $selected_trip
    ? (float)$selected_trip["budget"]
    : 0;

$total_spent =
    (float)$hotel +
    (float)$transport +
    (float)$food +
    (float)$activities;

$remaining_budget =
    $total_budget - $total_spent;

$usage_percentage =
    $total_budget > 0
        ? ($total_spent / $total_budget) * 100
        : 0;

$progress_percentage =
    min($usage_percentage, 100);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Budget Planner | TripGenie</title>

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

.budget-navbar {
    position: sticky;
    top: 0;
    z-index: 1000;

    background: rgba(255,255,255,0.96);

    backdrop-filter: blur(12px);

    border-bottom: 1px solid #e8edf5;
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
   PAGE HEADER
========================= */

.budget-page {
    min-height: calc(100vh - 70px);
    padding: 45px 20px 70px;
}

.budget-container {
    max-width: 1100px;
    margin: auto;
}

.page-header {
    margin-bottom: 28px;
}

.page-header h1 {
    margin: 0 0 8px;

    font-size: 34px;
    font-weight: 800;
}

.page-header p {
    margin: 0;

    color: #64748b;
    font-size: 15px;
}


/* =========================
   SUCCESS MESSAGE
========================= */

.success-message {
    background: #ecfdf5;
    color: #047857;

    border: 1px solid #a7f3d0;

    padding: 14px 18px;

    border-radius: 12px;

    margin-bottom: 22px;

    font-size: 14px;
    font-weight: 600;
}


/* =========================
   CARD
========================= */

.budget-card {
    background: white;

    border: 1px solid #e7ebf2;

    border-radius: 18px;

    padding: 28px;

    margin-bottom: 22px;

    box-shadow:
        0 8px 30px rgba(15, 23, 42, 0.06);
}

.card-title {
    margin: 0 0 18px;

    font-size: 20px;
    font-weight: 750;
}


/* =========================
   TRIP SELECT
========================= */

.select-label {
    display: block;

    font-size: 13px;
    font-weight: 700;

    color: #475569;

    margin-bottom: 8px;
}

.trip-select {
    width: 100%;

    padding: 14px 15px;

    border: 1px solid #dbe2ea;

    border-radius: 11px;

    background: white;

    font-size: 15px;

    color: #172033;

    outline: none;
}

.trip-select:focus {
    border-color: #2563eb;

    box-shadow:
        0 0 0 3px rgba(37,99,235,0.1);
}


/* =========================
   TRIP INFO
========================= */

.trip-info {
    margin-top: 20px;

    padding: 18px;

    border-radius: 14px;

    background: linear-gradient(
        135deg,
        #eff6ff,
        #f8fbff
    );

    border: 1px solid #dbeafe;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;
}

.trip-name {
    font-size: 20px;
    font-weight: 800;

    margin-bottom: 6px;
}

.trip-dates {
    color: #64748b;
    font-size: 13px;
}

.trip-budget {
    text-align: right;
}

.trip-budget span {
    display: block;

    color: #64748b;

    font-size: 12px;

    margin-bottom: 4px;
}

.trip-budget strong {
    font-size: 23px;

    color: #2563eb;
}


/* =========================
   EXPENSE GRID
========================= */

.expense-grid {
    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;

    margin-top: 20px;
}

.expense-box {
    padding: 20px;

    border: 1px solid #e5eaf1;

    border-radius: 14px;

    background: #fbfcfe;

    transition: 0.2s ease;
}

.expense-box:hover {
    border-color: #bfdbfe;

    transform: translateY(-2px);
}

.expense-icon {
    font-size: 25px;

    margin-bottom: 12px;
}

.expense-box label {
    display: block;

    font-weight: 700;

    font-size: 14px;

    margin-bottom: 8px;
}

.expense-box small {
    display: block;

    color: #94a3b8;

    font-size: 12px;

    margin-bottom: 10px;
}

.expense-box input {
    width: 100%;

    padding: 13px;

    border: 1px solid #dbe2ea;

    border-radius: 9px;

    background: white;

    font-size: 15px;

    outline: none;
}

.expense-box input:focus {
    border-color: #2563eb;

    box-shadow:
        0 0 0 3px rgba(37,99,235,0.08);
}


/* =========================
   SUMMARY
========================= */

.summary-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;

    margin-top: 25px;
}

.summary-box {
    padding: 21px;

    border-radius: 14px;

    background: #f8fafc;

    border: 1px solid #e7ebf2;
}

.summary-box h3 {
    margin: 0 0 8px;

    color: #64748b;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: 0.5px;
}

.summary-box p {
    margin: 0;

    font-size: 25px;

    font-weight: 800;

    color: #172033;
}

.remaining-positive {
    color: #059669 !important;
}

.remaining-negative {
    color: #dc2626 !important;
}


/* =========================
   PROGRESS
========================= */

.progress-section {
    margin-top: 27px;
}

.progress-top {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 9px;

    font-size: 13px;

    font-weight: 700;
}

.progress-track {
    width: 100%;

    height: 12px;

    background: #e8edf3;

    border-radius: 20px;

    overflow: hidden;
}

.progress-bar {
    height: 100%;

    width: <?php echo $progress_percentage; ?>%;

    background: linear-gradient(
        90deg,
        #2563eb,
        #3b82f6
    );

    border-radius: 20px;

    transition: width 0.3s ease;
}


/* =========================
   WARNING
========================= */

.warning {
    display: <?php echo $remaining_budget < 0 ? "block" : "none"; ?>;

    margin-top: 18px;

    padding: 14px 16px;

    border-radius: 11px;

    background: #fef2f2;

    border: 1px solid #fecaca;

    color: #b91c1c;

    font-size: 13px;

    font-weight: 600;
}


/* =========================
   SAVE BUTTON
========================= */

.save-button {
    width: 100%;

    margin-top: 25px;

    padding: 15px;

    border: none;

    border-radius: 11px;

    background: #2563eb;

    color: white;

    font-size: 15px;

    font-weight: 700;

    cursor: pointer;

    transition: 0.2s ease;
}

.save-button:hover {
    background: #1d4ed8;

    transform: translateY(-1px);
}


/* =========================
   EMPTY STATE
========================= */

.no-trips {
    text-align: center;

    padding: 55px 25px;
}

.empty-icon {
    font-size: 55px;

    margin-bottom: 12px;
}

.no-trips h2 {
    margin: 0 0 8px;

    font-size: 23px;
}

.no-trips p {
    color: #64748b;

    margin: 0 auto 22px;

    max-width: 480px;

    line-height: 1.6;
}

.btn {
    display: inline-block;

    padding: 12px 20px;

    border-radius: 10px;

    text-decoration: none;

    background: #2563eb;

    color: white;

    font-weight: 700;

    font-size: 14px;
}


/* =========================
   BOTTOM LINKS
========================= */

.bottom-links {
    display: flex;

    gap: 12px;

    flex-wrap: wrap;

    margin-top: 25px;
}

.bottom-links a {
    text-decoration: none;

    padding: 10px 14px;

    border-radius: 9px;

    background: white;

    border: 1px solid #e2e8f0;

    color: #475569;

    font-size: 13px;

    font-weight: 600;
}

.bottom-links a:hover {
    border-color: #bfdbfe;

    color: #2563eb;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 800px) {

    .nav-links {
        display: none;
    }

    .expense-grid,
    .summary-grid {
        grid-template-columns: 1fr;
    }

    .trip-info {
        flex-direction: column;

        align-items: flex-start;
    }

    .trip-budget {
        text-align: left;
    }
}


@media (max-width: 550px) {

    .budget-page {
        padding: 30px 14px 50px;
    }

    .budget-card {
        padding: 20px;
    }

    .page-header h1 {
        font-size: 27px;
    }

    .trip-name {
        font-size: 18px;
    }

    .summary-box p {
        font-size: 22px;
    }
}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="budget-navbar">

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
     PAGE
========================= -->

<div class="budget-page">

<div class="budget-container">


    <div class="page-header">

        <h1>💰 Budget Planner</h1>

        <p>
            Track your travel expenses and stay within your budget.
        </p>

    </div>


    <?php if (isset($_GET["saved"])): ?>

        <div class="success-message">

            ✅ Your budget expenses have been saved successfully.

        </div>

    <?php endif; ?>


    <?php if (empty($trips)): ?>


        <div class="budget-card no-trips">

            <div class="empty-icon">
                🧳
            </div>

            <h2>
                No Trips Found
            </h2>

            <p>
                Create your first trip before using the
                TripGenie budget planner.
            </p>

            <a href="plan-trip.php" class="btn">
                ✈️ Plan a Trip
            </a>

        </div>


    <?php else: ?>


        <!-- TRIP SELECTION -->

        <div class="budget-card">

            <h2 class="card-title">
                🧳 Select Your Trip
            </h2>

            <label class="select-label"
                   for="tripSelect">

                Choose a trip to manage its expenses

            </label>

            <select
                id="tripSelect"
                class="trip-select"
            >

                <?php foreach ($trips as $trip): ?>

                    <option
                        value="<?php echo $trip["id"]; ?>"
                        data-budget="<?php echo $trip["budget"]; ?>"

                        <?php

                        if (
                            $trip["id"]
                            == $selected_trip_id
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

                        —

                        ₹<?php

                        echo number_format(
                            $trip["budget"],
                            2
                        );

                        ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <?php if ($selected_trip): ?>

                <div class="trip-info">

                    <div>

                        <div class="trip-name">

                            <?php

                            echo htmlspecialchars(
                                $selected_trip["destination"]
                            );

                            ?>

                        </div>

                        <div class="trip-dates">

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

                    </div>


                    <div class="trip-budget">

                        <span>
                            Trip Budget
                        </span>

                        <strong>

                            ₹<?php

                            echo number_format(
                                $selected_trip["budget"],
                                2
                            );

                            ?>

                        </strong>

                    </div>

                </div>

            <?php endif; ?>

        </div>


        <?php if ($selected_trip): ?>


        <!-- EXPENSE CARD -->

        <div class="budget-card">

            <h2 class="card-title">
                💸 Manage Your Expenses
            </h2>

            <p style="
                margin-top:-8px;
                color:#64748b;
                font-size:13px;
            ">

                Enter the estimated or actual amount
                for each category.

            </p>


            <form
                method="POST"
                action="save-expense.php"
                id="budgetForm"
            >


                <input
                    type="hidden"
                    name="trip_id"
                    value="<?php
                    echo $selected_trip_id;
                    ?>"
                >


                <div class="expense-grid">


                    <div class="expense-box">

                        <div class="expense-icon">
                            🏨
                        </div>

                        <label for="hotel">
                            Hotel / Stay
                        </label>

                        <small>
                            Accommodation expenses
                        </small>

                        <input
                            type="number"
                            id="hotel"
                            name="hotel"
                            min="0"
                            step="0.01"
                            value="<?php
                            echo $hotel;
                            ?>"
                            placeholder="₹ 0.00"
                        >

                    </div>


                    <div class="expense-box">

                        <div class="expense-icon">
                            🚗
                        </div>

                        <label for="transport">
                            Transport
                        </label>

                        <small>
                            Bus, train, taxi, fuel etc.
                        </small>

                        <input
                            type="number"
                            id="transport"
                            name="transport"
                            min="0"
                            step="0.01"
                            value="<?php
                            echo $transport;
                            ?>"
                            placeholder="₹ 0.00"
                        >

                    </div>


                    <div class="expense-box">

                        <div class="expense-icon">
                            🍴
                        </div>

                        <label for="food">
                            Food
                        </label>

                        <small>
                            Meals and refreshments
                        </small>

                        <input
                            type="number"
                            id="food"
                            name="food"
                            min="0"
                            step="0.01"
                            value="<?php
                            echo $food;
                            ?>"
                            placeholder="₹ 0.00"
                        >

                    </div>


                    <div class="expense-box">

                        <div class="expense-icon">
                            🎟️
                        </div>

                        <label for="activities">
                            Activities
                        </label>

                        <small>
                            Tickets and entertainment
                        </small>

                        <input
                            type="number"
                            id="activities"
                            name="activities"
                            min="0"
                            step="0.01"
                            value="<?php
                            echo $activities;
                            ?>"
                            placeholder="₹ 0.00"
                        >

                    </div>


                </div>


                <!-- SUMMARY -->

                <div class="summary-grid">


                    <div class="summary-box">

                        <h3>
                            Total Budget
                        </h3>

                        <p id="totalBudget">

                            ₹<?php

                            echo number_format(
                                $total_budget,
                                2
                            );

                            ?>

                        </p>

                    </div>


                    <div class="summary-box">

                        <h3>
                            Total Spent
                        </h3>

                        <p id="totalSpent">

                            ₹<?php

                            echo number_format(
                                $total_spent,
                                2
                            );

                            ?>

                        </p>

                    </div>


                    <div class="summary-box">

                        <h3>
                            Remaining
                        </h3>

                        <p
                            id="remainingBudget"
                            class="<?php

                            echo $remaining_budget < 0
                                ? "remaining-negative"
                                : "remaining-positive";

                            ?>"
                        >

                            ₹<?php

                            echo number_format(
                                $remaining_budget,
                                2
                            );

                            ?>

                        </p>

                    </div>


                </div>


                <!-- PROGRESS -->

                <div class="progress-section">

                    <div class="progress-top">

                        <span>
                            Budget Usage
                        </span>

                        <span id="usagePercentage">

                            <?php

                            echo number_format(
                                $usage_percentage,
                                1
                            );

                            ?>%

                        </span>

                    </div>


                    <div class="progress-track">

                        <div
                            class="progress-bar"
                            id="progressBar"
                        ></div>

                    </div>

                </div>


                <!-- WARNING -->

                <div
                    class="warning"
                    id="budgetWarning"
                >

                    ⚠️ Your spending is higher than
                    your planned trip budget.

                </div>


                <button
                    type="submit"
                    class="save-button"
                >

                    💾 Save Budget Expenses

                </button>


            </form>

        </div>


        <?php endif; ?>


    <?php endif; ?>


    <!-- BOTTOM LINKS -->

    <div class="bottom-links">

        <a href="dashboard.php">
            ← Dashboard
        </a>

        <a href="my-trips.php">
            🧳 My Trips
        </a>

        <a href="plan-trip.php">
            ✈️ Plan New Trip
        </a>

    </div>


</div>

</div>


<script>

/* =========================
   ELEMENTS
========================= */

const tripSelect =
    document.getElementById("tripSelect");

const hotel =
    document.getElementById("hotel");

const transport =
    document.getElementById("transport");

const food =
    document.getElementById("food");

const activities =
    document.getElementById("activities");

const totalBudget =
    document.getElementById("totalBudget");

const totalSpent =
    document.getElementById("totalSpent");

const remainingBudget =
    document.getElementById("remainingBudget");

const usagePercentage =
    document.getElementById("usagePercentage");

const progressBar =
    document.getElementById("progressBar");

const budgetWarning =
    document.getElementById("budgetWarning");


/* =========================
   CALCULATE BUDGET
========================= */

function calculateBudget() {

    if (
        !tripSelect ||
        !totalBudget ||
        !hotel
    ) {
        return;
    }


    const selectedOption =
        tripSelect.options[
            tripSelect.selectedIndex
        ];


    const budget =
        parseFloat(
            selectedOption.dataset.budget
        ) || 0;


    const hotelAmount =
        parseFloat(hotel.value) || 0;

    const transportAmount =
        parseFloat(transport.value) || 0;

    const foodAmount =
        parseFloat(food.value) || 0;

    const activitiesAmount =
        parseFloat(activities.value) || 0;


    const spent =
        hotelAmount +
        transportAmount +
        foodAmount +
        activitiesAmount;


    const remaining =
        budget - spent;


    let percentage = 0;

    if (budget > 0) {

        percentage =
            (spent / budget) * 100;

    }


    /* Update values */

    totalBudget.textContent =
        "₹" + budget.toFixed(2);

    totalSpent.textContent =
        "₹" + spent.toFixed(2);

    remainingBudget.textContent =
        "₹" + remaining.toFixed(2);


    /* Remaining color */

    remainingBudget.classList.remove(
        "remaining-positive",
        "remaining-negative"
    );


    if (remaining < 0) {

        remainingBudget.classList.add(
            "remaining-negative"
        );

        budgetWarning.style.display =
            "block";

    } else {

        remainingBudget.classList.add(
            "remaining-positive"
        );

        budgetWarning.style.display =
            "none";
    }


    /* Percentage */

    usagePercentage.textContent =
        percentage.toFixed(1) + "%";


    /* Progress */

    progressBar.style.width =
        Math.min(percentage, 100) + "%";

}


/* =========================
   CHANGE TRIP
========================= */

if (tripSelect) {

    tripSelect.addEventListener(
        "change",
        function () {

            window.location.href =
                "budget.php?trip_id="
                + this.value;

        }
    );

}


/* =========================
   LIVE CALCULATION
========================= */

if (hotel) {

    hotel.addEventListener(
        "input",
        calculateBudget
    );

}

if (transport) {

    transport.addEventListener(
        "input",
        calculateBudget
    );

}

if (food) {

    food.addEventListener(
        "input",
        calculateBudget
    );

}

if (activities) {

    activities.addEventListener(
        "input",
        calculateBudget
    );
}


/* =========================
   INITIAL CALCULATION
========================= */

calculateBudget();

</script>


</body>

</html>