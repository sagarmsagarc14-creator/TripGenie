<?php
session_start();

include "includes/db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Incorrect password.";
                $messageType = "error";
            }

        } else {

            $message = "No account found with this email.";
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - TripGenie</title>

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
                Welcome back,
                traveler.
            </h1>

            <p>
                Login to continue planning your trips,
                discovering beautiful destinations and
                managing your travel adventures.
            </p>

            <div class="travel-features">

                <div>
                    🌍
                    <span>Explore destinations</span>
                </div>

                <div>
                    🗺️
                    <span>Manage your itineraries</span>
                </div>

                <div>
                    💰
                    <span>Track your travel budget</span>
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

            <h2>Welcome back</h2>

            <p class="register-subtitle">
                Login to your TripGenie account
            </p>


            <?php if (!empty($message)): ?>

                <div class="register-message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST" action="">

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
                            placeholder="Enter your password"
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


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    class="register-btn"
                >
                    Login to TripGenie
                    <span>→</span>
                </button>

            </form>


            <p class="login-text">

                Don't have an account?

                <a href="register.php">
                    Create Account
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