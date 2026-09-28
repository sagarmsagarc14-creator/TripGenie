<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $trip_id = intval($_POST["trip_id"]);

    $hotel = floatval($_POST["hotel"]);
    $transport = floatval($_POST["transport"]);
    $food = floatval($_POST["food"]);
    $activities = floatval($_POST["activities"]);

    // Check that this trip belongs to the logged-in user
    $check = $conn->prepare("
        SELECT id
        FROM trips
        WHERE id = ? AND user_id = ?
    ");

    $check->bind_param("ii", $trip_id, $user_id);
    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows === 0) {
        die("Invalid trip selected.");
    }

    $check->close();

    // Check if expense already exists for this trip
    $checkExpense = $conn->prepare("
        SELECT id
        FROM expenses
        WHERE trip_id = ? AND user_id = ?
    ");

    $checkExpense->bind_param("ii", $trip_id, $user_id);
    $checkExpense->execute();

    $expenseResult = $checkExpense->get_result();

    if ($expenseResult->num_rows > 0) {

        // Update existing expense
        $expense = $expenseResult->fetch_assoc();

        $expense_id = $expense["id"];

        $update = $conn->prepare("
            UPDATE expenses
            SET hotel = ?,
                transport = ?,
                food = ?,
                activities = ?
            WHERE id = ?
              AND user_id = ?
        ");

        $update->bind_param(
            "ddddii",
            $hotel,
            $transport,
            $food,
            $activities,
            $expense_id,
            $user_id
        );

        $update->execute();

        $update->close();

    } else {

        // Insert new expense
        $insert = $conn->prepare("
            INSERT INTO expenses
            (user_id, trip_id, hotel, transport, food, activities)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $insert->bind_param(
            "iidddd",
            $user_id,
            $trip_id,
            $hotel,
            $transport,
            $food,
            $activities
        );

        $insert->execute();

        $insert->close();
    }

    $checkExpense->close();

    header("Location: budget.php?saved=1");
    exit();
}

header("Location: budget.php");
exit();

?>