
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
</main>

<?php
require "includes/footer.php";
?>
