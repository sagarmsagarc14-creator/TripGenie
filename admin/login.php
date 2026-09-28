<?php

session_start();

include "../includes/db.php";

$error = "";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email == "" || $password == "") {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare("
            SELECT id, name, email, password
            FROM admins
            WHERE email = ?
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin["password"])) {

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_name"] = $admin["name"];
                $_SESSION["admin_email"] = $admin["email"];

                header("Location: dashboard.php");
                exit();

            } else {

                $error = "Incorrect password.";

            }

        } else {

            $error = "Admin account not found.";

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

<title>Admin Login | TripGenie</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {

    font-family: Arial, sans-serif;

    min-height: 100vh;

    background:
        linear-gradient(
            135deg,
            rgba(0, 74, 173, 0.95),
            rgba(0, 123, 255, 0.85)
        );

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 25px;

}


/* MAIN CONTAINER */

.login-container {

    width: 100%;

    max-width: 950px;

    min-height: 560px;

    display: grid;

    grid-template-columns: 1fr 1fr;

    background: white;

    border-radius: 22px;

    overflow: hidden;

    box-shadow:
        0 25px 60px rgba(0, 0, 0, 0.25);

}


/* LEFT SIDE */

.left-side {

    background:
        linear-gradient(
            145deg,
            #005bea,
            #00c6fb
        );

    color: white;

    padding: 55px;

    display: flex;

    flex-direction: column;

    justify-content: center;

    position: relative;

    overflow: hidden;

}


/* DECORATIVE CIRCLES */

.circle {

    position: absolute;

    border-radius: 50%;

    background: rgba(255,255,255,0.10);

}

.circle-one {

    width: 220px;

    height: 220px;

    top: -70px;

    right: -70px;

}

.circle-two {

    width: 160px;

    height: 160px;

    bottom: -50px;

    left: -50px;

}


.logo {

    font-size: 34px;

    font-weight: bold;

    margin-bottom: 35px;

    position: relative;

    z-index: 2;

}

.logo span {

    color: #dff5ff;

}


.admin-icon {

    width: 75px;

    height: 75px;

    border-radius: 18px;

    background: rgba(255,255,255,0.18);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 38px;

    margin-bottom: 25px;

    position: relative;

    z-index: 2;

}


.left-side h1 {

    font-size: 38px;

    line-height: 1.2;

    margin-bottom: 18px;

    position: relative;

    z-index: 2;

}


.left-side p {

    font-size: 16px;

    line-height: 1.7;

    opacity: 0.92;

    max-width: 390px;

    position: relative;

    z-index: 2;

}


/* FEATURES */

.features {

    margin-top: 30px;

    position: relative;

    z-index: 2;

}

.feature {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom: 14px;

    font-size: 14px;

}


/* RIGHT SIDE */

.right-side {

    padding: 55px;

    display: flex;

    flex-direction: column;

    justify-content: center;

}


.login-heading {

    margin-bottom: 30px;

}

.login-heading h2 {

    font-size: 30px;

    margin-bottom: 8px;

}

.login-heading p {

    color: #777;

}


/* ERROR */

.error {

    background: #fff0f0;

    color: #c62828;

    border-left: 4px solid #dc3545;

    padding: 13px;

    border-radius: 7px;

    margin-bottom: 20px;

    font-size: 14px;

}


/* FORM */

.form-group {

    margin-bottom: 20px;

}

.form-group label {

    display: block;

    font-weight: bold;

    margin-bottom: 8px;

    font-size: 14px;

}

.input-box {

    position: relative;

}

.input-box .input-icon {

    position: absolute;

    left: 14px;

    top: 50%;

    transform: translateY(-50%);

    font-size: 18px;

}

.form-group input {

    width: 100%;

    padding: 14px 45px;

    border: 1px solid #ddd;

    border-radius: 9px;

    font-size: 15px;

    outline: none;

    transition: 0.3s;

}

.form-group input:focus {

    border-color: #007bff;

    box-shadow:
        0 0 0 3px rgba(0,123,255,0.10);

}


/* PASSWORD */

.password-toggle {

    position: absolute;

    right: 14px;

    top: 50%;

    transform: translateY(-50%);

    border: none;

    background: none;

    cursor: pointer;

    font-size: 17px;

}


/* LOGIN BUTTON */

.login-btn {

    width: 100%;

    border: none;

    padding: 15px;

    border-radius: 9px;

    background:
        linear-gradient(
            135deg,
            #007bff,
            #0056d6
        );

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;

    box-shadow:
        0 8px 18px rgba(0,123,255,0.25);

}

.login-btn:hover {

    transform: translateY(-2px);

    box-shadow:
        0 12px 25px rgba(0,123,255,0.30);

}


/* SECURITY */

.security {

    text-align: center;

    margin-top: 20px;

    color: #777;

    font-size: 13px;

}


/* BACK LINK */

.back-home {

    display: block;

    text-align: center;

    margin-top: 20px;

    color: #007bff;

    text-decoration: none;

    font-size: 14px;

}


/* RESPONSIVE */

@media (max-width: 800px) {

    .login-container {

        grid-template-columns: 1fr;

        max-width: 500px;

    }

    .left-side {

        padding: 40px;

    }

    .left-side h1 {

        font-size: 30px;

    }

    .features {

        display: none;

    }

    .right-side {

        padding: 40px;

    }

}


@media (max-width: 500px) {

    body {

        padding: 12px;

    }

    .left-side {

        padding: 30px;

    }

    .right-side {

        padding: 30px;

    }

    .left-side h1 {

        font-size: 27px;

    }

}

</style>

</head>


<body>


<div class="login-container">


    <!-- LEFT SIDE -->

    <div class="left-side">


        <div class="circle circle-one"></div>

        <div class="circle circle-two"></div>


        <div class="logo">

            Trip<span>Genie</span>

        </div>


        <div class="admin-icon">

            🔐

        </div>


        <h1>

            Welcome Back,<br>

            Administrator

        </h1>


        <p>

            Manage your travel platform, users and destinations
            from one secure administration panel.

        </p>


        <div class="features">

            <div class="feature">

                <span>✓</span>

                <span>Manage registered users</span>

            </div>


            <div class="feature">

                <span>✓</span>

                <span>Manage travel destinations</span>

            </div>


            <div class="feature">

                <span>✓</span>

                <span>Monitor trip activity</span>

            </div>


        </div>


    </div>


    <!-- RIGHT SIDE -->

    <div class="right-side">


        <div class="login-heading">

            <h2>
                Admin Login
            </h2>

            <p>
                Sign in to access your dashboard
            </p>

        </div>


        <?php if ($error != ""): ?>

            <div class="error">

                ⚠️
                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- EMAIL -->

            <div class="form-group">

                <label>
                    Email Address
                </label>


                <div class="input-box">

                    <span class="input-icon">
                        ✉️
                    </span>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter admin email"
                        required
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>
                    Password
                </label>


                <div class="input-box">

                    <span class="input-icon">
                        🔑
                    </span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >


                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword()"
                        id="toggleButton"
                    >
                        👁️
                    </button>

                </div>

            </div>


            <button
                type="submit"
                class="login-btn"
            >

                🔐 Secure Admin Login

            </button>


        </form>


        <div class="security">

            🛡️ Secure administrator access

        </div>


        <a
            href="../index.php"
            class="back-home"
        >

            ← Back to TripGenie

        </a>


    </div>


</div>


<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const button =
        document.getElementById("toggleButton");


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