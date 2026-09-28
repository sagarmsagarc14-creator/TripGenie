<?php
include "includes/db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($name) || empty($email) || empty($password)) {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $messageType = "error";

    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already registered. Please login.";
            $messageType = "error";

        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {

                $message = "Account created successfully! You can now login.";
                $messageType = "success";

            } else {

                $message = "Something went wrong. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account - TripGenie</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<a href="index.php" class="back-home">
    ← Back to Home
</a>

<div class="register-page">

    <!-- LEFT SIDE -->

    <div class="register-left">

        <div class="travel-content">

            <div class="travel-logo">
                ✈️ Trip<span>Genie</span>
            </div>

            <h1>
                Your next adventure
                starts here.
            </h1>

            <p>
                Create your TripGenie account and start discovering
                amazing destinations, planning unforgettable journeys
                and managing your trips in one place.
            </p>

            <div class="travel-features">

                <div>
                    🌍
                    <span>Explore destinations</span>
                </div>

                <div>
                    🗺️
                    <span>Create smart itineraries</span>
                </div>

                <div>
                    💰
                    <span>Plan your travel budget</span>
                </div>

            </div>

        </div>

    </div>


    <!-- RIGHT SIDE -->

    <div class="register-right">

        <div class="register-card">

            <div class="mobile-logo">
                ✈️ Trip<span>Genie</span>
            </div>

            <h2>Create your account</h2>

            <p class="register-subtitle">
                Start planning your next adventure
            </p>


            <?php if (!empty($message)): ?>

                <div class="register-message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST" action="">

                <!-- NAME -->

                <div class="input-group">

                    <label for="name">
                        Full Name
                    </label>

                    <div class="input-box">

                        <span>👤</span>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your full name"
                            required
                        >

                    </div>

                </div>


                <!-- EMAIL -->

                <div class="input-group">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="input-box">

                        <span>✉️</span>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email address"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="input-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-box">

                        <span>🔒</span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            required
                        >

                        <button
                            type="button"
                            class="show-password"
                            onclick="togglePassword()"
                        >
                            👁️
                        </button>

                    </div>

                </div>


                <p class="password-info">
                    Password must be at least 6 characters.
                </p>


                <!-- REGISTER BUTTON -->

                <button
                    type="submit"
                    class="register-btn"
                >
                    Create Account
                    <span>→</span>
                </button>

            </form>


            <p class="login-text">

                Already have an account?

                <a href="login.php">
                    Login
                </a>

            </p>


            <p class="secure-text">
                🔒 Your information is securely stored.
            </p>

        </div>

    </div>

</div>


<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const button =
        document.querySelector(".show-password");

    if (password.type === "password") {

        password.type = "text";

        button.textContent = "🙈";

    } else {

        password.type = "password";

        button.textContent = "👁️";
    }
}

</script>

</body>
</html>