<?php

require_once "includes/db.php";

$result = $conn->query("
    SELECT *
    FROM destinations
    ORDER BY name ASC
");

$destinations = [];

while ($row = $result->fetch_assoc()) {
    $destinations[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Explore Destinations | TripGenie</title>

    <link rel="stylesheet" href="css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

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
            background: #f7f9fc;
            color: #172033;
        }

        /* ================= NAVBAR ================= */

        .dest-navbar {
            position: sticky;
            top: 0;
            z-index: 1000;

            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(14px);

            border-bottom: 1px solid #e8edf5;

            padding: 16px 0;
        }

        .dest-nav-container {
            width: 90%;
            max-width: 1200px;
            margin: auto;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dest-logo {
            text-decoration: none;

            font-size: 25px;
            font-weight: 800;

            color: #172033;
        }

        .dest-logo span {
            color: #2563eb;
        }

        .dest-nav-links {
            display: flex;
            gap: 28px;
            align-items: center;
        }

        .dest-nav-links a {
            text-decoration: none;
            color: #475569;

            font-size: 14px;
            font-weight: 600;

            transition: 0.2s;
        }

        .dest-nav-links a:hover,
        .dest-nav-links .active {
            color: #2563eb;
        }

        .dest-nav-button {
            text-decoration: none;

            background: #2563eb;
            color: white;

            padding: 11px 19px;

            border-radius: 10px;

            font-size: 13px;
            font-weight: 700;

            transition: 0.2s;
        }

        .dest-nav-button:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        /* ================= PAGE HERO ================= */

        .destination-page {
            min-height: 100vh;
        }

        .destination-hero {
            position: relative;
            overflow: hidden;

            padding: 75px 20px 120px;

            background:
                radial-gradient(
                    circle at 80% 20%,
                    rgba(96,165,250,0.28),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #0f2f63,
                    #1557a5 55%,
                    #2563eb
                );

            color: white;
        }

        .destination-hero::before {
            content: "";

            position: absolute;

            width: 330px;
            height: 330px;

            right: -100px;
            top: -150px;

            border-radius: 50%;

            background: rgba(255,255,255,0.08);
        }

        .destination-hero-content {
            position: relative;
            z-index: 2;

            max-width: 850px;

            margin: auto;

            text-align: center;
        }

        .hero-badge {
            display: inline-block;

            padding: 8px 14px;

            border-radius: 30px;

            background: rgba(255,255,255,0.12);

            border: 1px solid rgba(255,255,255,0.2);

            font-size: 12px;
            font-weight: 700;

            margin-bottom: 18px;
        }

        .destination-hero h1 {
            margin: 0;

            font-size: clamp(38px, 5vw, 58px);

            line-height: 1.1;

            letter-spacing: -1.5px;
        }

        .destination-hero h1 span {
            color: #93c5fd;
        }

        .destination-hero p {
            max-width: 650px;

            margin: 18px auto 0;

            color: rgba(255,255,255,0.82);

            font-size: 16px;

            line-height: 1.7;
        }

        /* ================= SEARCH ================= */

        .search-wrapper {
            position: relative;

            max-width: 760px;

            margin: -42px auto 55px;

            z-index: 10;

            padding: 0 20px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 13px;

            background: white;

            padding: 8px 9px 8px 20px;

            border-radius: 16px;

            box-shadow: 0 18px 45px rgba(15,23,42,0.14);
        }

        .search-icon {
            font-size: 21px;
        }

        .search-box input {
            flex: 1;

            border: none;
            outline: none;

            font-size: 14px;

            color: #172033;

            background: transparent;
        }

        .search-box input::placeholder {
            color: #94a3b8;
        }

        .search-label {
            padding: 11px 15px;

            border-radius: 10px;

            background: #eff6ff;

            color: #2563eb;

            font-size: 12px;
            font-weight: 700;
        }

        /* ================= DESTINATIONS ================= */

        .destination-container {
            width: 90%;
            max-width: 1200px;

            margin: auto;

            padding-bottom: 80px;
        }

        .result-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 25px;
        }

        .result-heading h2 {
            margin: 0;

            font-size: 24px;
        }

        .result-heading span {
            color: #64748b;

            font-size: 13px;
        }

        .destination-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;
        }

        /* ================= CARD ================= */

        .destination-card {
            background: white;

            border-radius: 20px;

            overflow: hidden;

            border: 1px solid #edf1f7;

            box-shadow:
                0 8px 25px rgba(15,23,42,0.06);

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;

            animation: cardAppear 0.5s ease;
        }

        .destination-card:hover {
            transform: translateY(-8px);

            box-shadow:
                0 20px 45px rgba(15,23,42,0.13);
        }

        @keyframes cardAppear {

            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        .destination-image {
            position: relative;
            overflow: hidden;
        }

        .destination-image img {
            width: 100%;

            height: 220px;

            object-fit: cover;

            display: block;

            transition: transform 0.5s ease;
        }

        .destination-card:hover .destination-image img {
            transform: scale(1.06);
        }

        .destination-tag {
            position: absolute;

            top: 15px;
            left: 15px;

            padding: 7px 11px;

            border-radius: 20px;

            background: rgba(255,255,255,0.94);

            color: #2563eb;

            font-size: 10px;

            font-weight: 800;

            letter-spacing: 0.5px;
        }

        .destination-content {
            padding: 21px;
        }

        .destination-content h2 {
            margin: 0 0 7px;

            font-size: 21px;
        }

        .location {
            color: #2563eb;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 12px;
        }

        .description {
            color: #64748b;

            line-height: 1.65;

            font-size: 13px;

            min-height: 65px;
        }

        .destination-bottom {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin-top: 18px;

            padding-top: 16px;

            border-top: 1px solid #edf1f7;
        }

        .explore-label {
            color: #94a3b8;

            font-size: 11px;
        }

        .plan-btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            padding: 10px 15px;

            background: #2563eb;

            color: white;

            text-decoration: none;

            border-radius: 9px;

            font-size: 12px;

            font-weight: 700;

            transition: 0.2s;
        }

        .plan-btn:hover {
            background: #1d4ed8;

            transform: translateY(-2px);
        }

        /* ================= NO RESULTS ================= */

        .no-results {
            display: none;

            text-align: center;

            padding: 65px 20px;

            background: white;

            border-radius: 18px;

            border: 1px solid #edf1f7;
        }

        .no-results-icon {
            font-size: 45px;

            margin-bottom: 12px;
        }

        .no-results h2 {
            margin: 0 0 8px;
        }

        .no-results p {
            color: #64748b;
        }

        /* ================= BACK ================= */

        .back-link {
            text-align: center;

            margin-top: 40px;
        }

        .back-link a {
            color: #2563eb;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* ================= MOBILE ================= */

        @media (max-width: 900px) {

            .dest-nav-links {
                display: none;
            }

            .destination-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .destination-hero {
                padding: 55px 20px 100px;
            }

            .destination-hero h1 {
                font-size: 38px;
            }

            .search-box {
                padding: 8px 12px;
            }

            .search-label {
                display: none;
            }

            .destination-grid {
                grid-template-columns: 1fr;
            }

            .result-heading {
                flex-direction: column;

                align-items: flex-start;

                gap: 5px;
            }

            .destination-image img {
                height: 230px;
            }

        }

    </style>

</head>


<body>


<!-- ================= NAVBAR ================= -->

<header class="dest-navbar">

    <div class="dest-nav-container">

        <a href="index.php" class="dest-logo">
            ✈️ Trip<span>Genie</span>
        </a>


        <nav class="dest-nav-links">

            <a href="index.php">
                Home
            </a>

            <a href="destinations.php" class="active">
                Destinations
            </a>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="my-trips.php">
                My Trips
            </a>

            <a href="profile.php">
                Profile
            </a>

        </nav>


        <a href="plan-trip.php" class="dest-nav-button">
            + Plan Trip
        </a>

    </div>

</header>


<!-- ================= HERO ================= -->

<section class="destination-hero">

    <div class="destination-hero-content">

        <div class="hero-badge">
            🌎 Discover • Explore • Experience
        </div>

        <h1>
            Explore your next
            <span>adventure.</span>
        </h1>

        <p>
            Discover amazing destinations, find places that inspire you,
            and start planning your next unforgettable journey with TripGenie.
        </p>

    </div>

</section>


<!-- ================= SEARCH ================= -->

<div class="search-wrapper">

    <div class="search-box">

        <div class="search-icon">
            🔎
        </div>

        <input
            type="text"
            id="searchInput"
            placeholder="Search by destination, location or description..."
            autocomplete="off"
        >

        <div class="search-label">
            SEARCH
        </div>

    </div>

</div>


<!-- ================= DESTINATION CONTENT ================= -->

<main class="destination-container">


    <div class="result-heading">

        <h2>
            Popular destinations
        </h2>

        <span id="resultCount">
            <?php echo count($destinations); ?> destinations available
        </span>

    </div>


    <div
        class="destination-grid"
        id="destinationGrid"
    >


        <?php foreach ($destinations as $destination): ?>


            <div
                class="destination-card"
                data-search="<?php
                    echo htmlspecialchars(
                        strtolower(
                            $destination["name"]
                            . " "
                            . $destination["location"]
                            . " "
                            . $destination["description"]
                        )
                    );
                ?>"
            >


                <div class="destination-image">


                    <?php if (!empty($destination["image"])): ?>

                        <img
                            src="<?php
                                echo htmlspecialchars(
                                    $destination["image"]
                                );
                            ?>"
                            alt="<?php
                                echo htmlspecialchars(
                                    $destination["name"]
                                );
                            ?>"
                        >

                    <?php else: ?>

                        <img
                            src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80"
                            alt="Travel destination"
                        >

                    <?php endif; ?>


                    <div class="destination-tag">
                        ✈️ DESTINATION
                    </div>


                </div>


                <div class="destination-content">


                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $destination["name"]
                        );
                        ?>
                    </h2>


                    <div class="location">

                        📍
                        <?php
                        echo htmlspecialchars(
                            $destination["location"]
                        );
                        ?>

                    </div>


                    <div class="description">

                        <?php
                        echo htmlspecialchars(
                            $destination["description"]
                        );
                        ?>

                    </div>


                    <div class="destination-bottom">

                        <span class="explore-label">
                            Ready to explore?
                        </span>


                        <a
                            href="destination-details.php?id=<?php echo $destination["id"]; ?>"
                            class="plan-btn"
                        >
                            👁️ View Details
                        </a>

                    </div>


                </div>


            </div>


        <?php endforeach; ?>


    </div>


    <!-- NO RESULTS -->

    <div
        class="no-results"
        id="noResults"
    >

        <div class="no-results-icon">
            🧭
        </div>

        <h2>
            No destination found
        </h2>

        <p>
            Try searching for another destination.
        </p>

    </div>


    <div class="back-link">

        <a href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>


</main>


<script>

const searchInput =
    document.getElementById("searchInput");

const cards =
    document.querySelectorAll(".destination-card");

const noResults =
    document.getElementById("noResults");

const resultCount =
    document.getElementById("resultCount");


searchInput.addEventListener("input", function () {

    const searchText =
        this.value.toLowerCase().trim();

    let visibleCount = 0;


    cards.forEach(function (card) {

        const searchData =
            card.dataset.search;

        if (searchData.includes(searchText)) {

            card.style.display = "";

            visibleCount++;

        } else {

            card.style.display = "none";

        }

    });


    if (visibleCount === 0) {

        noResults.style.display = "block";

        resultCount.textContent =
            "0 destinations found";

    } else {

        noResults.style.display = "none";

        resultCount.textContent =
            visibleCount +
            (visibleCount === 1
                ? " destination found"
                : " destinations found");

    }

});

</script>


</body>

</html>