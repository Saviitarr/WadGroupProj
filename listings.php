<?php
// listings.php - Member 2's page, now using the shared header/footer.
// The page content is wrapped in .cc-part2 so Member 2's listings.css styles still apply.
$pageTitle = 'Browse Windhoek rooms';
$pageCss   = ['css/listings.css'];
require __DIR__ . '/includes/header.php';
?>
<div class="cc-part2">
<main class="cc-wrap">
   <div class="cc-page-heading">
    <h1>
     Find your place near NUST
    </h1>
    <p>
     Browse student accommodation in Windhoek.
    </p>
   </div>
   <div class="cc-listings-layout">
    <aside aria-label="Accommodation search and filters" class="cc-filter-panel">
     <h2>
      Search and filter
     </h2>
     <form id="filtersForm">
      <div class="cc-field">
       <label for="search">
        Name or street
       </label>
       <input id="search" placeholder="Search rooms" type="search"/>
      </div>
      <div class="cc-field">
       <label for="area">
        Area
       </label>
       <select id="area">
        <option value="">
         Any area
        </option>
       </select>
      </div>
      <fieldset class="cc-rent-fields">
       <legend>
        Monthly rent (N$)
       </legend>
       <div class="cc-price-row">
        <div>
         <label for="minPrice">
          Min
         </label>
         <input id="minPrice" min="0" placeholder="2000" type="number"/>
        </div>
        <div>
         <label for="maxPrice">
          Max
         </label>
         <input id="maxPrice" min="0" placeholder="4500" type="number"/>
        </div>
       </div>
      </fieldset>
      <fieldset>
       <legend>
        Room type
       </legend>
       <div class="cc-types">
        <label class="cc-check">
         <input checked name="roomType" type="checkbox" value="Single room"/>
         Single
        </label>
        <label class="cc-check">
         <input checked name="roomType" type="checkbox" value="Double room"/>
         Double
        </label>
       </div>
      </fieldset>
      <div class="cc-field">
       <label class="cc-check">
        <input id="availableOnly" type="checkbox"/>
        Available only
       </label>
      </div>
      <div class="cc-filter-actions">
       <button class="cc-button" type="submit">
        Search
       </button>
       <button class="cc-button cc-outline" id="clearFilters" type="button">
        Reset
       </button>
      </div>
     </form>
    </aside>
    <div class="cc-results-layout">
     <section aria-label="Accommodation results" class="cc-results">
      <div class="cc-results-top">
       <p aria-live="polite" id="resultCount">
        Loading listings&hellip;
       </p>
       <div class="cc-sort-control">
        <label for="sort">
         Sort
        </label>
        <select id="sort">
         <option value="distance-asc">
          Closest to campus
         </option>
         <option value="price-asc">
          Price: low to high
         </option>
         <option value="price-desc">
          Price: high to low
         </option>
         <option value="name-asc">
          Name: A&ndash;Z
         </option>
        </select>
       </div>
      </div>
      <div class="cc-grid" id="listingsGrid">
      </div>
      <p class="cc-notice" hidden="" id="emptyState">
       No rooms match these filters.
      </p>
      <p class="cc-notice cc-error" hidden="" id="loadError">
       Unable to load listings. Please try again.
      </p>
      <nav aria-label="Listing pages" class="cc-pagination" id="pagination">
      </nav>
     </section>
     <aside aria-label="Illustrative Windhoek area map">
      <p class="cc-map-intro">
       Explore rooms around campus
      </p>
      <div class="cc-map-panel">
       <span class="cc-map-caption">
        Windhoek &middot; NUST main campus area
       </span>
       <span class="cc-map-pin">
        N$4,500
       </span>
       <span class="cc-map-pin">
        N$2,800
       </span>
       <span class="cc-map-pin">
        N$3,600
       </span>
       <span class="cc-map-pin">
        N$3,200
       </span>
       <span class="cc-map-campus">
        ⌂ NUST
       </span>
       <span class="cc-map-disclaimer">
        Illustrative map only &middot; markers are not real coordinates
       </span>
      </div>
     </aside>
    </div>
   </div>
  </main>
</div>
<script src="js/data.js"></script>
<script src="js/listings.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
