<?php
// listing-details.php - Member 2's page, now using the shared header/footer.
// The page content is wrapped in .cc-part2 so Member 2's listings.css styles still apply.
$pageTitle = 'Accommodation details';
$pageCss   = ['css/listings.css'];
require __DIR__ . '/includes/header.php';
?>
<div class="cc-part2">
<main class="cc-wrap cc-detail-page">
   <p class="cc-breadcrumb">
    <a href="listings.php">
     Listings
    </a>
    /
    <span id="crumbName">
     Listing details
    </span>
   </p>
   <div id="detailContent">
    <p>
     Loading details&hellip;
    </p>
   </div>
   <p class="cc-notice cc-error" hidden="" id="detailError">
    Listing unavailable.
    <a href="listings.php">
     Back to listings
    </a>
    .
   </p>
  </main>
</div>
<script src="js/data.js"></script>
<script src="js/details.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
