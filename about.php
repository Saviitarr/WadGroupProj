<?php
$pageTitle = "About";

require "includes/header.php";
?>

<main class="about-page">

    <!-- About introduction -->
    <section class="about-hero">

        <div class="about-container about-hero-layout">

            <div class="about-intro">

                <span class="about-label">ABOUT US</span>

                <h1>
                    Making the room hunt one less thing
                    to worry about.
                </h1>

                <p>
                    Campus Connect brings student rooms around
                    NUST into one place, so you can compare them
                    and book without chasing adverts, phone
                    numbers and group chats.
                </p>

            </div>

            <div class="about-visual" aria-hidden="true">
                <div class="about-visual-large"></div>
                <div class="about-visual-small"></div>

                <div class="about-visual-red">
                    <p>Built by NUST students, for NUST students.</p>
                </div>
            </div>

        </div>

    </section>

    <!-- Problem and solution -->
    <section class="about-problem-section">

        <div class="about-container about-info-grid">

            <article class="about-info-card">

                <h2>The problem</h2>

                <p>
                    Every semester, students spend weeks searching
                    scattered posts for a room they can afford
                    and reach. Listings go out of date, prices
                    are unclear, and there's no record of who
                    asked for what.
                </p>

            </article>

            <article class="about-info-card solution-card">

                <h2>Our solution</h2>

                <p>
                    One searchable list of rooms with clear
                    prices, distance to campus and availability.
                    Students request bookings online, and an
                    admin team keeps listings and bookings
                    up to date.
                </p>

            </article>

        </div>

    </section>

    <!-- Website features -->
    <section class="about-features-section">

        <div class="about-container">

            <h2>What you can do</h2>

            <div class="about-features-grid">

                <article class="about-feature">

                    <span class="about-feature-icon" aria-hidden="true">
                        &#9906;
                    </span>

                    <h3>Search and filter</h3>

                    <p>
                        By area, budget, room type and availability.
                    </p>

                </article>

                <article class="about-feature">

                    <span class="about-feature-icon" aria-hidden="true">
                        &#9633;
                    </span>

                    <h3>Request a booking</h3>

                    <p>
                        Choose dates and a room in one short form.
                    </p>

                </article>

                <article class="about-feature">

                    <span class="about-feature-icon" aria-hidden="true">
                        &#10003;
                    </span>

                    <h3>Track your status</h3>

                    <p>
                        Pending, approved or declined,
                        on your dashboard.
                    </p>

                </article>

                <article class="about-feature">

                    <span class="about-feature-icon" aria-hidden="true">
                        &#9671;
                    </span>

                    <h3>Secure accounts</h3>

                    <p>
                        Hashed passwords and separate
                        student and admin access.
                    </p>

                </article>

            </div>

        </div>

    </section>


</main>

<?php
require "includes/footer.php";
?>
