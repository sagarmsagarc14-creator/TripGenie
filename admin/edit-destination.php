<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../includes/db.php";

if (!isset($_GET["id"])) {
    header("Location: destinations.php");
    exit();
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: destinations.php");
    exit();
}

$destination = $result->fetch_assoc();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $description = trim($_POST["description"]);
    $image = trim($_POST["image"]);

    if (empty($name) || empty($location) || empty($description)) {

        $error = "Please fill all required fields.";

    } else {

        $update = $conn->prepare(
            "UPDATE destinations 
             SET name = ?, location = ?, description = ?, image = ?
             WHERE id = ?"
        );

        $update->bind_param(
            "ssssi",
            $name,
            $location,
            $description,
            $image,
            $id
        );

        if ($update->execute()) {

            $success = "Destination updated successfully!";

            $destination["name"] = $name;
            $destination["location"] = $location;
            $destination["description"] = $description;
            $destination["image"] = $image;

        } else {

            $error = "Failed to update destination.";
        }

        $update->close();
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Destination - TripGenie</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            padding: 30px;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        h1 {
            margin-bottom: 10px;
            color: #1f2937;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #374151;
        }

        input,
        textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .update-btn {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .update-btn:hover {
            background: #1d4ed8;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 15px;
            padding: 12px;
            background: #e5e7eb;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>✏️ Edit Destination</h1>

    <p class="subtitle">
        Update the destination information below.
    </p>

    <?php if (!empty($success)): ?>

        <div class="success">
            <?php echo htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>Destination Name</label>

        <input
            type="text"
            name="name"
            value="<?php echo htmlspecialchars($destination["name"]); ?>"
            required
        >


        <label>Location</label>

        <input
            type="text"
            name="location"
            value="<?php echo htmlspecialchars($destination["location"]); ?>"
            required
        >


        <label>Description</label>

        <textarea
            name="description"
            required
        ><?php echo htmlspecialchars($destination["description"]); ?></textarea>


        <label>Image URL</label>

        <input
            type="text"
            name="image"
            value="<?php echo htmlspecialchars($destination["image"]); ?>"
            placeholder="Enter image URL"
        >


        <button type="submit" class="update-btn">
            Update Destination
        </button>

    </form>


    <a href="destinations.php" class="back-btn">
        ← Back to Destinations
    </a>

</div>

</body>

</html>