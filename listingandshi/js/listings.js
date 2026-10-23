const searchInput = document.getElementById("search");
const areaInput = document.getElementById("area");
const minPriceInput = document.getElementById("minPrice");
const maxPriceInput = document.getElementById("maxPrice");
const availableInput = document.getElementById("availableOnly");
const sortInput = document.getElementById("sort");
const filtersForm = document.getElementById("filtersForm");
const listingsGrid = document.getElementById("listingsGrid");
const resultCount = document.getElementById("resultCount");
const emptyState = document.getElementById("emptyState");
const loadError = document.getElementById("loadError");
const pagination = document.getElementById("pagination");

let accommodations = [];
let currentPage = 1;
const pageSize = 6;

function formatPrice(price) {
    return "N$" + Number(price).toLocaleString("en-NA");
}

function createElement(tag, className, text) {
    const element = document.createElement(tag);

    if (className) {
        element.className = className;
    }

    if (text !== undefined) {
        element.textContent = text;
    }

    return element;
}

function createCard(property) {
    const card = createElement("article", "cc-card");
    const photo = createElement("div", "cc-card-image");

    if (property.images.length > 0) {
        const image = createElement("img");
        image.src = property.images[0];
        image.alt = property.name;
        image.loading = "lazy";
        photo.appendChild(image);
    } else {
        photo.appendChild(createElement("span", "cc-img-icon", "⌂"));
    }

    const availability = property.roomsAvailable > 0
        ? property.roomsAvailable + " left"
        : "Fully booked";
    photo.appendChild(createElement("span", "cc-image-badge", availability));

    const body = createElement("div", "cc-card-body");
    body.appendChild(createElement("p", "cc-tag", property.type));
    body.appendChild(createElement("h3", "", property.name));
    body.appendChild(createElement(
        "p", "cc-card-sub", property.area + " · " + property.distanceKm + " km to NUST"
    ));

    const bottom = createElement("div", "cc-card-bottom");
    const price = createElement("p", "cc-price", formatPrice(property.price));
    price.appendChild(createElement("small", "", " /mo"));

    const link = createElement("a", "cc-view", "View");
    link.href = "listing-details.php?id=" + encodeURIComponent(property.id);

    bottom.appendChild(price);
    bottom.appendChild(link);
    body.appendChild(bottom);
    card.appendChild(photo);
    card.appendChild(body);

    return card;
}

function getSelectedTypes() {
    const checkboxes = filtersForm.querySelectorAll('input[name="roomType"]:checked');
    return Array.from(checkboxes).map(function (checkbox) {
        return checkbox.value;
    });
}

function getFilteredListings() {
    const search = searchInput.value.trim().toLowerCase();
    const area = areaInput.value;
    const minPrice = minPriceInput.value === "" ? 0 : Number(minPriceInput.value);
    const maxPrice = maxPriceInput.value === "" ? Infinity : Number(maxPriceInput.value);
    const selectedTypes = getSelectedTypes();

    const filtered = accommodations.filter(function (property) {
        const searchableText = (property.name + " " + property.area + " " + property.street).toLowerCase();
        const matchesSearch = searchableText.includes(search);
        const matchesArea = area === "" || property.area === area;
        const matchesPrice = property.price >= minPrice && property.price <= maxPrice;
        const matchesType = selectedTypes.includes(property.type);
        const matchesAvailability = !availableInput.checked || property.roomsAvailable > 0;

        return matchesSearch && matchesArea && matchesPrice && matchesType && matchesAvailability;
    });

    if (sortInput.value === "distance-asc") {
        filtered.sort(function (a, b) { return a.distanceKm - b.distanceKm; });
    } else if (sortInput.value === "price-asc") {
        filtered.sort(function (a, b) { return a.price - b.price; });
    } else if (sortInput.value === "price-desc") {
        filtered.sort(function (a, b) { return b.price - a.price; });
    } else if (sortInput.value === "name-asc") {
        filtered.sort(function (a, b) { return a.name.localeCompare(b.name); });
    }

    return filtered;
}

function updateAddressBar() {
    if (window.location.protocol === "file:") {
        return;
    }

    const params = new URLSearchParams();
    const fields = ["search", "area", "minPrice", "maxPrice"];

    for (const id of fields) {
        const value = document.getElementById(id).value.trim();
        if (value !== "") {
            params.set(id, value);
        }
    }

    const types = getSelectedTypes();
    if (types.length !== 2) {
        params.set("types", types.join(","));
    }

    if (availableInput.checked) {
        params.set("available", "1");
    }

    if (sortInput.value !== "distance-asc") {
        params.set("sort", sortInput.value);
    }

    if (currentPage > 1) {
        params.set("page", String(currentPage));
    }

    const query = params.toString();
    const address = window.location.pathname + (query ? "?" + query : "");
    window.history.replaceState(null, "", address);
}

function addPageButton(label, pageNumber, disabled, selected) {
    const button = createElement("button", "", label);
    button.type = "button";
    button.disabled = disabled;

    if (selected) {
        button.setAttribute("aria-current", "page");
    }

    button.addEventListener("click", function () {
        currentPage = pageNumber;
        displayListings();
    });

    pagination.appendChild(button);
}

function displayListings() {
    const filtered = getFilteredListings();
    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
    currentPage = Math.min(Math.max(currentPage, 1), totalPages);

    const start = (currentPage - 1) * pageSize;
    const visibleListings = filtered.slice(start, start + pageSize);

    listingsGrid.replaceChildren();
    for (const property of visibleListings) {
        listingsGrid.appendChild(createCard(property));
    }

    resultCount.textContent = filtered.length + (filtered.length === 1 ? " listing found" : " listings found");
    emptyState.hidden = filtered.length !== 0;
    pagination.replaceChildren();

    if (totalPages > 1) {
        addPageButton("Previous", currentPage - 1, currentPage === 1, false);

        for (let page = 1; page <= totalPages; page++) {
            addPageButton(String(page), page, false, page === currentPage);
        }

        addPageButton("Next", currentPage + 1, currentPage === totalPages, false);
    }

    updateAddressBar();
}

function restoreFiltersFromURL() {
    const params = new URLSearchParams(window.location.search);

    for (const id of ["search", "area", "minPrice", "maxPrice"]) {
        if (params.has(id)) {
            document.getElementById(id).value = params.get(id);
        }
    }

    if (params.has("types")) {
        const types = params.get("types").split(",");
        const checkboxes = filtersForm.querySelectorAll('input[name="roomType"]');

        for (const checkbox of checkboxes) {
            checkbox.checked = types.includes(checkbox.value);
        }
    }

    availableInput.checked = params.get("available") === "1";

    const allowedSorts = ["distance-asc", "price-asc", "price-desc", "name-asc"];
    if (allowedSorts.includes(params.get("sort"))) {
        sortInput.value = params.get("sort");
    }

    currentPage = Math.max(1, parseInt(params.get("page") || "1", 10) || 1);
}
// Turns the filter form into a query string for the PHP endpoint
function buildQuery() {
    const params = new URLSearchParams();

    if (searchInput.value.trim() !== "") params.set("search", searchInput.value.trim());
    if (areaInput.value !== "") params.set("area", areaInput.value);
    if (minPriceInput.value !== "") params.set("minPrice", minPriceInput.value);
    if (maxPriceInput.value !== "") params.set("maxPrice", maxPriceInput.value);
    params.set("types", getSelectedTypes().join(","));
    if (availableInput.checked) params.set("available", "1");

    return params.toString();
}

// Asks the server for matching rooms, then redraws
function loadAndDisplay() {
    window.loadCampusConnectListings(buildQuery())
        .then(function (items) {
            accommodations = items;
            loadError.hidden = true;
            displayListings();
        })
        .catch(function (error) {
            console.error(error);
            resultCount.textContent = "";
            loadError.hidden = false;
        });
}

function resetFilters() {
    filtersForm.reset();
    const checkboxes = filtersForm.querySelectorAll('input[name="roomType"]');

    for (const checkbox of checkboxes) {
        checkbox.checked = true;
    }

    sortInput.value = "distance-asc";
    currentPage = 1;
        loadAndDisplay();
}

function refreshResults(event) {
    if (event) {
        event.preventDefault();
    }
    currentPage = 1;
        loadAndDisplay();
}

filtersForm.addEventListener("submit", refreshResults);
sortInput.addEventListener("change", refreshResults);
document.getElementById("clearFilters").addEventListener("click", resetFilters);

const automaticFilters = filtersForm.querySelectorAll("select, input[type=checkbox]");
for (const input of automaticFilters) {
    input.addEventListener("change", refreshResults);
}

window.loadCampusConnectListings()
    .then(function (items) {
        accommodations = items;
        const areas = Array.from(new Set(items.map(function (item) {
            return item.area;
        }))).filter(Boolean).sort();

        for (const area of areas) {
            areaInput.add(new Option(area, area));
        }

        restoreFiltersFromURL();
            loadAndDisplay();
    })
    .catch(function (error) {
        console.error(error);
        resultCount.textContent = "";
        loadError.hidden = false;
    });
