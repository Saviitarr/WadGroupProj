<?php
$pageTitle = "Home";

require "includes/header.php";
?>

<main>
    <section class="hero">

        <p class="hero-label">Student accommodation made simple</p>

        <h1>Your room near NUST, sorted before the semester starts.</h1>

        <p>
            Discover affordable student accommodation near campus.
            Browse available rooms and request a booking.
        </p>

        <form class="search-bar" action="listings.php" method="GET">

            <div class="search-field">
                <label for="location">Where</label>
                <select id="location" name="location">
                    <option value="">Anywhere near NUST</option>
                    <option value="Windhoek West">Windhoek West</option>
                    <option value="Pioneerspark">Pioneerspark</option>
                    <option value="Eros">Eros</option>
                    <option value="Hochland Park">Hochland Park</option>
                </select>
            </div>

            <div class="search-field">
                <label for="move_in">Move in</label>
                <input type="date" id="move_in" name="move_in">
            </div>

            <div class="search-field">
                <label for="max_price">Budget</label>
                <select id="max_price" name="max_price">
                    <option value="">Any budget</option>
                    <option value="3000">Under N$3,000</option>
                    <option value="4000">Under N$4,000</option>
                    <option value="5000">Under N$5,000</option>
                    <option value="6000">Under N$6,000</option>
                </select>
            </div>

            <div class="search-field">
                <label for="room_type">Room type</label>
                <select id="room_type" name="room_type">
                    <option value="">Any type</option>
                    <option value="single">Single</option>
                    <option value="double">Double</option>
                </select>
            </div>

            <button type="submit" class="search-button">
                Search
            </button>

        </form>

    </section>


<?php
// Featured listings now come from the database: the 4 properties closest to campus.
// Each row is turned into the same keys the cards below already use.
$featuredRows = $pdo->query(
    "SELECT p.id, p.name, p.distance_to_campus_km, p.has_wifi, p.has_parking,
            MIN(r.price_per_month) AS price,
            SUM(r.is_available)    AS rooms_left,
            GROUP_CONCAT(DISTINCT r.room_type ORDER BY r.room_type) AS types
       FROM properties p
       JOIN rooms r ON r.property_id = p.id
      GROUP BY p.id
      ORDER BY p.distance_to_campus_km
      LIMIT 4"
)->fetchAll();

$featuredListings = [];
foreach ($featuredRows as $i => $row) {
    $features = [];
    if ($row["has_wifi"]) { $features[] = "Wi-Fi"; }
    if ($row["has_parking"]) { $features[] = "Parking"; }

    $badge = "";
    if ($i === 0) {
        $badge = "Closest to campus";
    } elseif ((int) $row["rooms_left"] === 1) {
        $badge = "1 room left";
    }

    $featuredListings[] = [
        "id" => (int) $row["id"],
        "name" => $row["name"],
        "distance" => rtrim(rtrim(number_format($row["distance_to_campus_km"], 1), "0"), ".") . " km",
        "type" => ucwords(str_replace(",", " & ", $row["types"])),
        "features" => $features ? implode(" · ", $features) : "Student rooms",
        "price" => (float) $row["price"],
        "badge" => $badge
    ];
}
?>

<section class="featured-section">

    <div class="featured-header">
        <h2>Available this semester</h2>
        <a href="listings.php">Show all</a>
    </div>

    <div class="listings-grid">

        <?php foreach ($featuredListings as $listing): ?>

            <article class="listing-card">

                <a href="listing-details.php?id=<?php echo (int) $listing["id"]; ?>" class="listing-card-link">

                    <div class="listing-image">

                        <?php if ($listing["badge"] !== ""): ?>
                            <span class="listing-badge">
                                <?php echo htmlspecialchars($listing["badge"]); ?>
                            </span>
                        <?php endif; ?>

                        <span class="listing-placeholder" aria-hidden="true">
                            &#8962;
                        </span>

                    </div>

                    <div class="listing-title-row">
                        <h3>
                            <?php echo htmlspecialchars($listing["name"]); ?>
                        </h3>

                        <span class="listing-distance">
                            <?php echo htmlspecialchars($listing["distance"]); ?>
                        </span>
                    </div>

                    <p class="listing-details">
                        <?php echo htmlspecialchars($listing["type"]); ?> room
                        &middot;
                        <?php echo htmlspecialchars($listing["features"]); ?>
                    </p>

                    <p class="listing-price">
                        <strong>
                            N$<?php echo number_format($listing["price"]); ?>
                        </strong>
                        /month
                    </p>

                </a>

            </article>

        <?php endforeach; ?>

    </div>


</section>


<!-- How booking works -->
<section class="booking-process">

    <div class="booking-container">

        <h2>How booking works</h2>

        <div class="booking-steps">

            <div class="step-card">
                <span class="step-number">1</span>

                <h3>Find your room</h3>

                <p>
                    Filter by area, budget and room type.
                    Every listing shows how far it is from campus.
                </p>
            </div>

            <div class="step-card">
                <span class="step-number">2</span>

                <h3>Request to book</h3>

                <p>
                    Pick your dates and send a note.
                    You won't be charged anything online.
                </p>
            </div>

            <div class="step-card">
                <span class="step-number">3</span>

                <h3>Get approved</h3>

                <p>
                    Follow your request on your dashboard
                    and receive updates when the administrator responds.
                </p>
            </div>

        </div>

    </div>

</section>


<!-- Property listing banner -->
<section class="property-banner">

    <div class="property-banner-content">

        <h2>Have a room to rent near campus?</h2>

        <p>
            Tell us about it and help students find
            their next home near NUST.
        </p>

        <a href="contact.php" class="property-banner-btn">
            List your property
        </a>

    </div>

    <div class="property-banner-image">
        <span aria-hidden="true">&#8962;</span>
    </div>

</section>


</main>

<?php
require "includes/footer.php";
?>
