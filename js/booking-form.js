// booking-form.js - client-side validation and live summary for book.php (Member 3)
// The server checks everything again (includes/booking_rules.php); this just gives instant feedback.

const MIN_DAYS = 28;      // minimum stay, about one month
const MAX_MONTHS = 12;    // maximum stay
const NOTES_MAX = 500;    // characters

const form = document.getElementById("bookingForm");

if (form) {
    const roomSelect = document.getElementById("room_id");
    const startInput = document.getElementById("start_date");
    const endInput = document.getElementById("end_date");
    const notesInput = document.getElementById("notes");
    const agreeInput = document.getElementById("agree");
    const notesCount = document.getElementById("notes-count");

    // ---------- small date helpers ----------

    // "2027-02-01" -> Date at local midnight (avoids timezone surprises)
    function toDate(value) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return null;
        }
        const parts = value.split("-").map(Number);
        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        // reject impossible dates like 2027-02-31
        return date.getMonth() === parts[1] - 1 ? date : null;
    }

    // Date -> "2027-02-01"
    function toValue(date) {
        const m = String(date.getMonth() + 1).padStart(2, "0");
        const d = String(date.getDate()).padStart(2, "0");
        return date.getFullYear() + "-" + m + "-" + d;
    }

    function addMonths(date, months) {
        const copy = new Date(date);
        copy.setMonth(copy.getMonth() + months);
        return copy;
    }

    function daysBetween(a, b) {
        return Math.round((b - a) / 86400000);
    }

    // Same rule as months_between() in PHP: a part-month at the end counts as a month
    function monthsBetween(start, end) {
        let months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
        if (end.getDate() > start.getDate()) {
            months++;
        }
        return Math.max(1, months);
    }

    function today() {
        const now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), now.getDate());
    }

    function formatMoney(amount) {
        return "N$" + Math.round(amount).toLocaleString("en-US");
    }

    // ---------- showing and clearing errors ----------

    function showError(input, message) {
        const box = document.getElementById(input.id + "-error");
        input.setAttribute("aria-invalid", "true");
        box.textContent = message;
        box.hidden = false;
    }

    function clearError(input) {
        const box = document.getElementById(input.id + "-error");
        input.removeAttribute("aria-invalid");
        box.textContent = "";
        box.hidden = true;
    }

    // ---------- one check per field; each returns true when the field is fine ----------

    function checkRoom() {
        if (roomSelect.value === "") {
            showError(roomSelect, "Choose a room from the list.");
            return false;
        }
        clearError(roomSelect);
        return true;
    }

    function checkStart() {
        const start = toDate(startInput.value);
        const latest = addMonths(today(), 12);

        if (startInput.value === "") {
            showError(startInput, "Choose a move-in date.");
            return false;
        }
        if (!start) {
            showError(startInput, "Enter a valid move-in date.");
            return false;
        }
        if (start < today()) {
            showError(startInput, "Move-in date cannot be in the past.");
            return false;
        }
        if (start > latest) {
            showError(startInput, "You can only book up to a year ahead.");
            return false;
        }
        clearError(startInput);
        return true;
    }

    function checkEnd() {
        const start = toDate(startInput.value);
        const end = toDate(endInput.value);

        if (endInput.value === "") {
            showError(endInput, "Choose a move-out date.");
            return false;
        }
        if (!end) {
            showError(endInput, "Enter a valid move-out date.");
            return false;
        }
        if (start && end <= start) {
            showError(endInput, "Move-out must be after move-in.");
            return false;
        }
        if (start && daysBetween(start, end) < MIN_DAYS) {
            showError(endInput, "The minimum stay is one month.");
            return false;
        }
        if (start && end > addMonths(start, MAX_MONTHS)) {
            showError(endInput, "The maximum stay is " + MAX_MONTHS + " months.");
            return false;
        }
        clearError(endInput);
        return true;
    }

    function checkNotes() {
        if (notesInput.value.trim().length > NOTES_MAX) {
            showError(notesInput, "Keep your note under " + NOTES_MAX + " characters.");
            return false;
        }
        clearError(notesInput);
        return true;
    }

    function checkAgree() {
        if (!agreeInput.checked) {
            showError(agreeInput, "Please confirm you understand this is a request.");
            return false;
        }
        clearError(agreeInput);
        return true;
    }

    // ---------- live summary panel ----------

    function updateSummary() {
        const option = roomSelect.options[roomSelect.selectedIndex];
        const hasRoom = roomSelect.value !== "";

        document.getElementById("summaryEmpty").hidden = hasRoom;
        document.getElementById("summaryBody").hidden = !hasRoom;
        if (!hasRoom) {
            return;
        }

        const price = Number(option.dataset.price);
        const type = option.dataset.type;

        document.getElementById("sumProperty").textContent = option.dataset.property;
        document.getElementById("sumArea").textContent =
            option.dataset.area + " · " + option.dataset.distance + " km to NUST";
        document.getElementById("sumType").textContent =
            type.charAt(0).toUpperCase() + type.slice(1) + " (sleeps " + option.dataset.capacity + ")";
        document.getElementById("sumPrice").textContent = formatMoney(price);

        const start = toDate(startInput.value);
        const end = toDate(endInput.value);

        if (start && end && end > start) {
            const months = monthsBetween(start, end);
            document.getElementById("sumStay").textContent = months + (months === 1 ? " month" : " months");
            document.getElementById("sumTotal").textContent = formatMoney(price * months);
        } else {
            document.getElementById("sumStay").textContent = "–";
            document.getElementById("sumTotal").textContent = "–";
        }
    }

    // ---------- keep the move-out picker sensible ----------

    function syncEndMin() {
        const start = toDate(startInput.value);
        if (start) {
            const earliest = new Date(start);
            earliest.setDate(earliest.getDate() + MIN_DAYS);
            endInput.min = toValue(earliest);
            endInput.max = toValue(addMonths(start, MAX_MONTHS));
        }
    }

    // ---------- events ----------

    roomSelect.addEventListener("change", function () {
        checkRoom();
        updateSummary();
    });

    startInput.addEventListener("change", function () {
        checkStart();
        syncEndMin();
        if (endInput.value !== "") {
            checkEnd();
        }
        updateSummary();
    });

    endInput.addEventListener("change", function () {
        checkEnd();
        updateSummary();
    });

    notesInput.addEventListener("input", function () {
        const length = notesInput.value.length;
        notesCount.textContent = length + " / " + NOTES_MAX;
        notesCount.classList.toggle("near-limit", length > NOTES_MAX - 50);
        checkNotes();
    });

    agreeInput.addEventListener("change", checkAgree);

    // Quick-pick buttons fill the move-out date (and move-in, if empty)
    const chips = document.querySelectorAll(".quick-dates .chip");
    for (const chip of chips) {
        chip.addEventListener("click", function () {
            if (startInput.value === "") {
                // default move-in: the 1st of next month
                const t = today();
                startInput.value = toValue(new Date(t.getFullYear(), t.getMonth() + 1, 1));
            }
            const start = toDate(startInput.value);
            if (!start) {
                checkStart();
                return;
            }
            const end = addMonths(start, Number(chip.dataset.months));
            end.setDate(end.getDate() - 1);           // e.g. 1 Feb -> 30 Jun
            endInput.value = toValue(end);
            checkStart();
            syncEndMin();
            checkEnd();
            updateSummary();
        });
    }

    // On submit, run every check; stop and focus the first problem if any fail
    form.addEventListener("submit", function (event) {
        const results = [checkRoom(), checkStart(), checkEnd(), checkNotes(), checkAgree()];
        const fields = [roomSelect, startInput, endInput, notesInput, agreeInput];

        if (results.includes(false)) {
            event.preventDefault();
            const firstBad = fields[results.indexOf(false)];
            firstBad.focus();
            return;
        }

        // Prevent double-clicks sending two requests
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = "Sending…";
    });

    // Fill the summary on page load (e.g. when coming from a listing)
    syncEndMin();
    updateSummary();
}
