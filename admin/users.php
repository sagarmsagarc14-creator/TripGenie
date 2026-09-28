<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../includes/db.php";

$message = "";
$error = "";


/* =========================
   DELETE USER
========================= */

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    // Prevent deleting yourself if somehow admin id matches
    if ($id > 0) {

        // Delete user's itineraries first
        $stmt = $conn->prepare(
            "DELETE FROM itineraries WHERE user_id = ?"
        );

        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }


        // Delete user's trips
        $stmt = $conn->prepare(
            "DELETE FROM trips WHERE user_id = ?"
        );

        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }


        // Delete user
        $stmt = $conn->prepare(
            "DELETE FROM users WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $message = "User deleted successfully.";
            } else {
                $error = "Unable to delete user.";
            }

            $stmt->close();
        }

    }
}


/* =========================
   GET USERS
========================= */

$result = $conn->query(
    "SELECT id, name, email, created_at
     FROM users
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Users - TripGenie</title>

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
            background: #1e40af;
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

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 25px;
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

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th {
            background: #eff6ff;
            color: #1e40af;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:hover {
            background: #f9fafb;
        }

        .user-id {
            font-weight: bold;
            color: #2563eb;
        }

        .delete-btn {
            display: inline-block;
            padding: 8px 13px;
            background: #fee2e2;
            color: #dc2626;
            text-decoration: none;
            border-radius: 7px;
            font-weight: bold;
        }

        .delete-btn:hover {
            background: #fecaca;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        @media (max-width: 700px) {

            .navbar {
                padding: 15px 20px;
            }

            .navbar a {
                margin-left: 8px;
            }

            .container {
                margin-top: 25px;
            }

        }

    </style>

</head>


<body>


<div class="navbar">

    <div class="logo">
        TripGenie Admin
    </div>

    <div>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<div class="container">


    <h1>👥 Manage Users</h1>

    <p class="subtitle">
        View registered TripGenie users and manage their accounts.
    </p>


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


    <div class="table-container">


        <?php if ($result && $result->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Registered On</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                    <?php while ($user = $result->fetch_assoc()): ?>

                        <tr>

                            <td class="user-id">
                                #<?php echo $user["id"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["email"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["created_at"]); ?>
                            </td>

                            <td>

                                <a
                                    href="users.php?delete=<?php echo $user["id"]; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this user? Their trips and itinerary items will also be deleted.');"
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

                <h3>No registered users</h3>

                <p>
                    Users will appear here after they register.
                </p>

            </div>

        <?php endif; ?>


    </div>


    <a href="dashboard.php" class="back">
        ← Back to Admin Dashboard
    </a>


</div>


</body>

</html>