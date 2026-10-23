// Listings now come from the database (api/listings.php).
// The example array below is only used if this is set back to null.
window.CAMPUS_CONNECT_LISTINGS_ENDPOINT = "api/listings.php";

window.CampusConnectListings = [
    {
        "id": 1,
        "name": "Windhoek West Studios",
        "location": "Windhoek",
        "area": "Windhoek West",
        "street": "Florence Nightingale Street",
        "type": "single",
        "price": 4500,
        "deposit": 4500,
        "distanceKm": 0.9,
        "availableFrom": "2027-01-01",
        "minimumStay": "1 semester",
        "roomsAvailable": 2,
        "bedrooms": 1,
        "guests": 1,
        "status": "active",
        "description": "Self-contained studios with a private bathroom and kitchenette, suitable for students wanting to stay close to NUST.",
        "amenities": [
            "Wi-Fi",
            "Furnished",
            "Private bathroom",
            "Kitchenette",
            "Secure parking"
        ],
        "images": []
    },
    {
        "id": 2,
        "name": "Pionierspark Student House",
        "location": "Windhoek",
        "area": "Pionierspark",
        "street": "Main Road",
        "type": "double",
        "price": 2800,
        "deposit": 2800,
        "distanceKm": 1.8,
        "availableFrom": "2027-01-10",
        "minimumStay": "1 semester",
        "roomsAvailable": 3,
        "bedrooms": 3,
        "guests": 6,
        "status": "active",
        "description": "Shared student rooms with furnished communal spaces and access to public transport.",
        "amenities": [
            "Wi-Fi",
            "Shared kitchen",
            "Laundry"
        ],
        "images": []
    },
    {
        "id": 3,
        "name": "Eros Garden Rooms",
        "location": "Windhoek",
        "area": "Eros",
        "street": "Eros Road",
        "type": "single",
        "price": 3600,
        "deposit": 3600,
        "distanceKm": 2.2,
        "availableFrom": "2027-01-05",
        "minimumStay": "3 months",
        "roomsAvailable": 5,
        "bedrooms": 5,
        "guests": 5,
        "status": "active",
        "description": "Quiet single rooms in a shared home with an outdoor garden.",
        "amenities": [
            "Wi-Fi",
            "Furnished",
            "Garden"
        ],
        "images": []
    },
    {
        "id": 4,
        "name": "Hochland Park Residence",
        "location": "Windhoek",
        "area": "Hochland Park",
        "street": "Deibieb Street",
        "type": "single",
        "price": 3200,
        "deposit": 3200,
        "distanceKm": 3.8,
        "availableFrom": "2027-01-01",
        "minimumStay": "1 semester",
        "roomsAvailable": 1,
        "bedrooms": 1,
        "guests": 1,
        "status": "active",
        "description": "A single room in a family home with shared facilities.",
        "amenities": [
            "Wi-Fi",
            "Laundry",
            "Shared kitchen"
        ],
        "images": []
    },
    {
        "id": 5,
        "name": "Academia Flats",
        "location": "Windhoek",
        "area": "Academia",
        "street": "Academia Street",
        "type": "double",
        "price": 2400,
        "deposit": 2400,
        "distanceKm": 4,
        "availableFrom": "2027-02-01",
        "minimumStay": "6 months",
        "roomsAvailable": 4,
        "bedrooms": 2,
        "guests": 4,
        "status": "active",
        "description": "Affordable flat-sharing option for students, with a communal study area.",
        "amenities": [
            "Wi-Fi",
            "Study area",
            "Kitchen"
        ],
        "images": []
    },
    {
        "id": 6,
        "name": "Klein Windhoek Loft",
        "location": "Windhoek",
        "area": "Klein Windhoek",
        "street": "Nelson Mandela Avenue",
        "type": "single",
        "price": 3900,
        "deposit": 3900,
        "distanceKm": 1.5,
        "availableFrom": "2027-01-01",
        "minimumStay": "3 months",
        "roomsAvailable": 0,
        "bedrooms": 1,
        "guests": 1,
        "status": "active",
        "description": "Compact private loft with its own kitchenette. Demo record marked fully booked.",
        "amenities": [
            "Wi-Fi",
            "Kitchenette",
            "Furnished"
        ],
        "images": []
    }
];

window.loadCampusConnectListings = async function () {
    let data = window.CampusConnectListings;
    const endpoint = window.CAMPUS_CONNECT_LISTINGS_ENDPOINT;

    if (endpoint) {
        const response = await fetch(endpoint, {
            headers: { Accept: "application/json" }
        });

        if (!response.ok) {
            throw new Error("Unable to retrieve accommodation.");
        }

        data = await response.json();
    }

    let listings = data;

    if (!Array.isArray(data)) {
        listings = data.accommodations || data.listings;
    }

    if (!Array.isArray(listings)) {
        throw new Error("Accommodation data must be a list.");
    }

    const cleaned = listings.map(function (item) {
        return {
            id: String(item.id ?? ""),
            name: String(item.name ?? ""),
            location: String(item.location ?? "Windhoek"),
            area: String(item.area ?? ""),
            street: String(item.street ?? ""),
            type: String(item.type ?? ""),
            types: Array.isArray(item.types) ? item.types : [String(item.type ?? "")],
            price: Number(item.price ?? 0),
            deposit: Number(item.deposit ?? 0),
            distanceKm: Number(item.distanceKm ?? item.distance_km ?? 0),
            availableFrom: String(item.availableFrom ?? item.available_from ?? ""),
            minimumStay: String(item.minimumStay ?? item.minimum_stay ?? ""),
            roomsAvailable: Number(item.roomsAvailable ?? item.rooms_available ?? 0),
            bedrooms: Number(item.bedrooms ?? 0),
            guests: Number(item.guests ?? 0),
            status: String(item.status ?? "active").toLowerCase(),
            description: String(item.description ?? ""),
            amenities: Array.isArray(item.amenities) ? item.amenities : [],
            images: Array.isArray(item.images) ? item.images : (item.image ? [item.image] : [])
        };
    });

    return cleaned.filter(function (item) {
        return item.id !== "" && item.location.toLowerCase() === "windhoek" && item.status === "active";
    });
};
