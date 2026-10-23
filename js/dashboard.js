// dashboard.js - status filter tabs and confirm-before-cancel on dashboard.php (Member 3)

// ---------- filter tabs ----------
const tabs = document.querySelectorAll(".tabs .tab");
const rows = document.querySelectorAll(".bookings-table tbody tr");
const emptyFilter = document.getElementById("emptyFilter");

// Which statuses each tab shows
const tabGroups = {
    all: ["pending", "approved", "rejected", "cancelled"],
    pending: ["pending"],
    approved: ["approved"],
    closed: ["rejected", "cancelled"]
};

function applyFilter(filter) {
    let visible = 0;

    for (const row of rows) {
        const show = tabGroups[filter].includes(row.dataset.status);
        row.hidden = !show;
        if (show) {
            visible++;
        }
    }

    for (const tab of tabs) {
        tab.setAttribute("aria-pressed", tab.dataset.filter === filter ? "true" : "false");
    }

    if (emptyFilter) {
        emptyFilter.hidden = visible > 0;
    }
}

for (const tab of tabs) {
    tab.addEventListener("click", function () {
        applyFilter(tab.dataset.filter);
    });
}

// ---------- ask before cancelling or removing ----------
const confirmForms = document.querySelectorAll("form[data-confirm]");

for (const form of confirmForms) {
    form.addEventListener("submit", function (event) {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
}

// ---------- scroll to a booking that was just created or edited ----------
const highlighted = document.querySelector(".bookings-table tr.highlight");
if (highlighted) {
    highlighted.scrollIntoView({ behavior: "smooth", block: "center" });
}
