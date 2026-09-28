<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../includes/db.php";

$message = "";
$message_type = "";


/* =========================
   DELETE DESTINATION
========================= */

if (isset($_GET["delete"]) && is_numeric($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare("
        DELETE FROM destinations
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Destination deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete destination.";
        $message_type = "error";
    }

    $stmt->close();
}


/* =========================
   ADD DESTINATION
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_destination"])
) {

    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $description = trim($_POST["description"]);
    $image = trim($_POST["image"]);


    if (
        empty($name)
        || empty($location)
        || empty($description)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO destinations
            (name, location, description, image)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssss",
            $name,
            $location,
            $description,
            $image
        );

        if ($stmt->execute()) {

            $message =
                "Destination added successfully.";

            $message_type = "success";

        } else {

            $message =
                "Unable to add destination.";

            $message_type = "error";
        }

        $stmt->close();
    }
}


/* =========================
   UPDATE DESTINATION
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_destination"])
) {

    $id = intval($_POST["id"]);

    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $description = trim($_POST["description"]);
    $image = trim($_POST["image"]);


    if (
        empty($name)
        || empty($location)
        || empty($description)
    ) {

        $message =
            "Please fill in all required fields.";

        $message_type = "error";

    } else {

        $stmt = $conn->prepare("
            UPDATE destinations
            SET
                name = ?,
                location = ?,
                description = ?,
                image = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssssi",
            $name,
            $location,
            $description,
            $image,
            $id
        );

        if ($stmt->execute()) {

            $message =
                "Destination updated successfully.";

            $message_type = "success";

        } else {

            $message =
                "Unable to update destination.";

            $message_type = "error";
        }

        $stmt->close();
    }
}


/* =========================
   EDIT DESTINATION
========================= */

$edit_destination = null;

if (
    isset($_GET["edit"])
    && is_numeric($_GET["edit"])
) {

    $edit_id = intval($_GET["edit"]);

    $stmt = $conn->prepare("
        SELECT *
        FROM destinations
        WHERE id = ?
    ");

    $stmt->bind_param("i", $edit_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $edit_destination =
            $result->fetch_assoc();
    }

    $stmt->close();
}


/* =========================
   GET DESTINATIONS
========================= */

$result = $conn->query("
    SELECT *
    FROM destinations
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Destinations - TripGenie</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family:
                Arial,
                sans-serif;

            background: #f4f6f9;

            color: #222;
        }


        .topbar {

            background: #111827;

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            flex-wrap: wrap;

            gap: 15px;
        }


        .topbar h2 {

            margin: 0;
        }


        .topbar-links {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;
        }


        .topbar a {

            color: white;

            text-decoration: none;

            padding: 9px 14px;

            border-radius: 8px;

            background: #374151;
        }


        .topbar a:hover {

            background: #4b5563;
        }


        .container {

            max-width: 1200px;

            margin: 35px auto;

            padding: 0 20px;
        }


        .page-title {

            margin-bottom: 25px;
        }


        .page-title h1 {

            margin-bottom: 8px;
        }


        .page-title p {

            color: #666;

            margin: 0;
        }


        .message {

            padding: 15px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-weight: 600;
        }


        .success {

            background: #dcfce7;

            color: #166534;
        }


        .error {

            background: #fee2e2;

            color: #991b1b;
        }


        .form-card {

            background: white;

            padding: 25px;

            border-radius: 15px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.06);

            margin-bottom: 30px;
        }


        .form-card h2 {

            margin-top: 0;

            margin-bottom: 20px;
        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;
        }


        .form-group {

            display: flex;

            flex-direction: column;
        }


        .form-group.full {

            grid-column: 1 / -1;
        }


        label {

            font-weight: 600;

            margin-bottom: 7px;
        }


        input,
        textarea {

            width: 100%;

            padding: 12px;

            border: 1px solid #d1d5db;

            border-radius: 9px;

            font-size: 15px;
        }


        textarea {

            min-height: 110px;

            resize: vertical;
        }


        input:focus,
        textarea:focus {

            outline: none;

            border-color: #2563eb;
        }


        .button-row {

            margin-top: 20px;

            display: flex;

            gap: 10px;

            flex-wrap: wrap;
        }


        button,
        .btn {

            border: none;

            padding: 11px 18px;

            border-radius: 9px;

            text-decoration: none;

            cursor: pointer;

            font-weight: 600;

            display: inline-block;
        }


        .primary {

            background: #2563eb;

            color: white;
        }


        .primary:hover {

            background: #1d4ed8;
        }


        .secondary {

            background: #6b7280;

            color: white;
        }


        .cancel {

            background: #e5e7eb;

            color: #333;
        }


        .table-card {

            background: white;

            padding: 25px;

            border-radius: 15px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.06);

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 850px;
        }


        th,
        td {

            padding: 14px;

            border-bottom:
                1px solid #e5e7eb;

            text-align: left;

            vertical-align: middle;
        }


        th {

            background: #f9fafb;

            font-size: 14px;
        }


        .destination-image {

            width: 90px;

            height: 60px;

            object-fit: cover;

            border-radius: 8px;
        }


        .action-buttons {

            display: flex;

            gap: 8px;

            flex-wrap: wrap;
        }


        .edit-btn {

            background: #f59e0b;

            color: white;
        }


        .delete-btn {

            background: #dc2626;

            color: white;
        }


        .edit-btn:hover {

            background: #d97706;
        }


        .delete-btn:hover {

            background: #b91c1c;
        }


        @media (max-width: 700px) {

            .form-grid {

                grid-template-columns: 1fr;
            }

            .form-group.full {

                grid-column: auto;
            }

            .topbar {

                padding: 15px 20px;
            }

            .container {

                margin-top: 25px;
            }
        }

    </style>

</head>


<body>


<div class="topbar">

    <h2>🌍 TripGenie Admin</h2>

    <div class="topbar-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="users.php">
            Users
        </a>

        <a href="trips.php">
            Trips
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<div class="container">


    <div class="page-title">

        <h1>🌍 Manage Destinations</h1>

        <p>
            Add, edit and remove travel destinations.
        </p>

    </div>


    <?php if (!empty($message)): ?>

        <div
            class="message
            <?php echo $message_type; ?>"
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- ADD / EDIT FORM -->

    <div class="form-card">

        <?php if ($edit_destination): ?>

            <h2>✏️ Edit Destination</h2>

            <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?php
                    echo $edit_destination["id"];
                    ?>"
                >

                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Destination Name *
                        </label>

                        <input
                            type="text"
                            name="name"
                            required
                            value="<?php
                            echo htmlspecialchars(
                                $edit_destination["name"]
                            );
                            ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Location *
                        </label>

                        <input
                            type="text"
                            name="location"
                            required
                            value="<?php
                            echo htmlspecialchars(
                                $edit_destination["location"]
                            );
                            ?>"
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Description *
                        </label>

                        <textarea
                            name="description"
                            required
                        ><?php
                        echo htmlspecialchars(
                            $edit_destination["description"]
                        );
                        ?></textarea>

                    </div>


                    <div class="form-group full">

                        <label>
                            Image URL
                        </label>

                        <input
                            type="url"
                            name="image"
                            value="<?php
                            echo htmlspecialchars(
                                $edit_destination["image"]
                            );
                            ?>"
                            placeholder="https://..."
                        >

                    </div>


                </div>


                <div class="button-row">

                    <button
                        type="submit"
                        name="update_destination"
                        class="primary"
                    >
                        💾 Update Destination
                    </button>


                    <a
                        href="destinations.php"
                        class="btn cancel"
                    >
                        Cancel
                    </a>

                </div>

            </form>


        <?php else: ?>


            <h2>➕ Add New Destination</h2>

            <form method="POST">

                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Destination Name *
                        </label>

                        <input
                            type="text"
                            name="name"
                            required
                            placeholder="Example: Ooty"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Location *
                        </label>

                        <input
                            type="text"
                            name="location"
                            required
                            placeholder="Example: Tamil Nadu"
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Description *
                        </label>

                        <textarea
                            name="description"
                            required
                            placeholder="Enter destination description..."
                        ></textarea>

                    </div>


                    <div class="form-group full">

                        <label>
                            Image URL
                        </label>

                        <input
                            type="url"
                            name="image"
                            placeholder="https://..."
                        >

                    </div>


                </div>


                <div class="button-row">

                    <button
                        type="submit"
                        name="add_destination"
                        class="primary"
                    >
                        ➕ Add Destination
                    </button>

                </div>

            </form>


        <?php endif; ?>

    </div>


    <!-- DESTINATION LIST -->

    <div class="table-card">

        <h2>📋 All Destinations</h2>

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Image</th>

                    <th>Name</th>

                    <th>Location</th>

                    <th>Description</th>

                    <th>Actions</th>

                </tr>

            </thead>


            <tbody>


                <?php if ($result->num_rows > 0): ?>


                    <?php while ($destination =
                        $result->fetch_assoc()): ?>


                        <tr>

                            <td>
                                <?php
                                echo $destination["id"];
                                ?>
                            </td>


                            <td>

                                <?php if (
                                    !empty(
                                        $destination["image"]
                                    )
                                ): ?>

                                    <img
                                        src="<?php
                                        echo htmlspecialchars(
                                            $destination["image"]
                                        );
                                        ?>"
                                        class="destination-image"
                                        alt="Destination"
                                    >

                                <?php else: ?>

                                    No image

                                <?php endif; ?>

                            </td>


                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $destination["name"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $destination["location"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                $description =
                                    $destination[
                                        "description"
                                    ];

                                echo htmlspecialchars(
                                    strlen($description) > 80
                                    ? substr(
                                        $description,
                                        0,
                                        80
                                    ) . "..."
                                    : $description
                                );
                                ?>

                            </td>


                            <td>

                                <div
                                    class="action-buttons"
                                >

                                    <a
                                        href="destinations.php?edit=<?php
                                        echo $destination["id"];
                                        ?>"
                                        class="btn edit-btn"
                                    >
                                        ✏️ Edit
                                    </a>


                                    <a
                                        href="destinations.php?delete=<?php
                                        echo $destination["id"];
                                        ?>"
                                        class="btn delete-btn"
                                        onclick="
                                        return confirm(
                                            'Are you sure you want to delete this destination?'
                                        );
                                        "
                                    >
                                        🗑️ Delete
                                    </a>

                                </div>

                            </td>

                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="6"
                            style="text-align:center;"
                        >
                            No destinations found.
                        </td>

                    </tr>


                <?php endif; ?>


            </tbody>

        </table>

    </div>


</div>


</body>

</html>