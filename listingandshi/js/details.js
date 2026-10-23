const detailContent = document.getElementById("detailContent");
const detailError = document.getElementById("detailError");

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

function createImageBox(imagePath, className) {
    const box = createElement("div", className);

    if (imagePath) {
        const image = createElement("img");
        image.src = imagePath;
        image.alt = "Accommodation photograph";
        box.appendChild(image);
    } else {
        box.appendChild(createElement("span", "", "⌂"));
    }

    return box;
}

function createInfoRow(label, value) {
    const row = createElement("div", "cc-stat-row");
    row.appendChild(createElement("span", "", label));
    row.appendChild(createElement("strong", "", value));
    return row;
}

function displayDetails(property) {
    document.title = property.name + " | Campus Connect";
    document.getElementById("crumbName").textContent = property.name;

    const gallery = createElement("div", "cc-detail-gallery");
    const smallImages = createElement("div", "cc-gallery-side");

    for (let index = 1; index <= 4; index++) {
        smallImages.appendChild(createImageBox(property.images[index], "cc-small-image"));
    }

    gallery.appendChild(createImageBox(property.images[0], "cc-featured-image"));
    gallery.appendChild(smallImages);

    const columns = createElement("div", "cc-detail-columns");
    const main = createElement("section", "cc-detail-main");
    const bookingPanel = createElement("aside", "cc-booking-panel");

    const availability = property.roomsAvailable > 0
        ? property.roomsAvailable + " rooms available"
        : "Fully booked";

    main.appendChild(createElement("p", "cc-tag", property.type + " · " + availability));
    main.appendChild(createElement("h1", "", property.name));
    main.appendChild(createElement(
        "p", "", property.street + ", " + property.area + ", Windhoek · " + property.distanceKm + " km to NUST main campus"
    ));
    main.appendChild(createElement("h2", "", "About this place"));
    main.appendChild(createElement("p", "", property.description));
    main.appendChild(createElement("h2", "", "Amenities"));

    const amenities = createElement("ul", "cc-amenities");
    for (const amenity of property.amenities) {
        amenities.appendChild(createElement("li", "", String(amenity)));
    }
    main.appendChild(amenities);
    main.appendChild(createElement("h2", "", "Where you’ll be"));
    main.appendChild(createElement("div", "cc-map", "Illustrative location · " + property.area + ", Windhoek (not a live map)"));

    const rent = createElement("p", "cc-price", formatPrice(property.price));
    rent.appendChild(createElement("small", "", " / month"));
    bookingPanel.appendChild(rent);
    bookingPanel.appendChild(createInfoRow("Deposit", formatPrice(property.deposit)));
    bookingPanel.appendChild(createInfoRow("Available from", property.availableFrom || "Contact admin"));
    bookingPanel.appendChild(createInfoRow("Minimum stay", property.minimumStay || "Contact admin"));
    bookingPanel.appendChild(createInfoRow("Rooms left", String(property.roomsAvailable)));

    if (property.roomsAvailable > 0) {
        const bookLink = createElement("a", "cc-button", "Request to book");
        bookLink.href = "../book.php?listing_id=" + encodeURIComponent(property.id);
        bookingPanel.appendChild(bookLink);
        bookingPanel.appendChild(createElement("p", "cc-fineprint", "You will need to log in first. Booking is a request until approved."));
    } else {
        bookingPanel.appendChild(createElement("p", "cc-notice", "Currently fully booked."));
    }

    columns.appendChild(main);
    columns.appendChild(bookingPanel);
    detailContent.replaceChildren(gallery, columns);
}

const listingId = new URLSearchParams(window.location.search).get("id");

window.loadCampusConnectListings("id=" + encodeURIComponent(listingId || ""))
    .then(function (listings) {
        const property = listings.find(function (item) {
            return item.id === listingId;
        });

        if (!property) {
            throw new Error("Listing not found");
        }

        displayDetails(property);
    })
    .catch(function (error) {
        console.error(error);
        detailContent.replaceChildren();
        detailError.hidden = false;
    });
