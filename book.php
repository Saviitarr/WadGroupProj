<?php
// book.php - booking request form (Member 3)
//   book.php?listing_id=3   -> new request, first free room in property 3 pre-selected
//   book.php?room_id=5      -> new request for room 5
//   book.php?edit=12        -> edit pending booking 12 (only the owner, only while pending)
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/booking_rules.php';

require_login();

// Admins manage bookings in the admin area; only students make requests
if (($_SESSION['role'] ?? '') === 'admin') {
    flash('error', 'Admins cannot make booking requests. Use the admin area to manage bookings.');
    header('Location: dashboard.php');
    exit;
}

$user   = current_user($pdo);
$userId = (int) $_SESSION['user_id'];
$editId = (int) ($_GET['edit'] ?? $_POST['booking_id'] ?? 0);
$booking = null;

// ---- Editing? Load the booking and make sure it belongs to this student and is still pending ----
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$editId, $userId]);
    $booking = $stmt->fetch();
    if (!$booking) {
        flash('error', 'That booking cannot be edited. Only your own pending requests can be changed.');
        header('Location: dashboard.php');
        exit;
    }
}

$rooms = bookable_rooms($pdo, $booking['room_id'] ?? 0);

// ---- Starting values for the form ----
$data = [
    'room_id'    => 0,
    'start_date' => '',
    'end_date'   => '',
    'notes'      => '',
    'agree'      => false,
];

if ($booking) {
    $data = [
        'room_id'    => (int) $booking['room_id'],
        'start_date' => $booking['start_date'],
        'end_date'   => $booking['end_date'],
        'notes'      => (string) $booking['notes'],
        'agree'      => true,
    ];
} elseif (isset($_GET['room_id'])) {
    $data['room_id'] = (int) $_GET['room_id'];
} elseif (isset($_GET['listing_id'])) {
    // Pick the first free room in that property
    foreach ($rooms as $r) {
        if ((int) $r['property_id'] === (int) $_GET['listing_id']) {
            $data['room_id'] = (int) $r['id'];
            break;
        }
    }
}

$errors = [];

// ---- Form submitted ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    } else {
        [$data, $errors] = validate_booking($pdo, $_POST, $userId, $rooms, $editId);
    }

    if (!$errors) {
        $notes = $data['notes'] === '' ? null : $data['notes'];

        if ($booking) {
            // UPDATE - still only touch it if it is ours and pending (it could have been approved meanwhile)
            $stmt = $pdo->prepare(
                "UPDATE bookings SET room_id = ?, start_date = ?, end_date = ?, notes = ?
                  WHERE id = ? AND user_id = ? AND status = 'pending'"
            );
            $stmt->execute([$data['room_id'], $data['start_date'], $data['end_date'], $notes, $editId, $userId]);
            flash('success', 'Your booking request #' . $editId . ' was updated.');
            $newId = $editId;
        } else {
            // INSERT - every new request starts as 'pending' until an admin decides
            $stmt = $pdo->prepare(
                "INSERT INTO bookings (user_id, room_id, start_date, end_date, status, notes)
                 VALUES (?, ?, ?, ?, 'pending', ?)"
            );
            $stmt->execute([$userId, $data['room_id'], $data['start_date'], $data['end_date'], $notes]);
            $newId = (int) $pdo->lastInsertId();
            flash('success', 'Request sent! Booking #' . $newId . ' is pending until an admin approves it.');
        }

        // Post/Redirect/Get: stops a refresh from submitting the form twice
        header('Location: dashboard.php?highlight=' . $newId);
        exit;
    }
}

// Find the selected room (for the summary panel when the page first loads)
$selected = null;
foreach ($rooms as $r) {
    if ((int) $r['id'] === (int) $data['room_id']) {
        $selected = $r;
    }
}

// Group rooms by property for <optgroup>
$byProperty = [];
foreach ($rooms as $r) {
    $byProperty[$r['property_name']][] = $r;
}

// Helper: print the error message for a field (and mark it for screen readers)
function field_error($errors, $name)
{
    $msg = $errors[$name] ?? '';
    return '<p class="field-error" id="' . $name . '-error"' . ($msg ? '' : ' hidden') . '>' . e($msg) . '</p>';
}
function invalid_attr($errors, $name)
{
    return isset($errors[$name]) ? ' aria-invalid="true"' : '';
}

$today   = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+1 year'));

$pageTitle = $booking ? 'Edit booking #' . $editId : 'Request to book';
$pageCss   = ['css/forms.css'];   // styles for the Member 3 pages
require __DIR__ . '/includes/header.php';   // Member 1's shared header
?>
<main class="m3 wrap page">
<?php show_flash(); ?>

<p class="breadcrumb">
    <?php if ($booking): ?>
        <a href="dashboard.php">My bookings</a> / Edit #<?= e($editId) ?>
    <?php else: ?>
        <a href="<?= e($listingsUrl) ?>">Browse rooms</a> / Request to book
    <?php endif; ?>
</p>

<h1><?= $booking ? 'Edit booking request' : 'Request to book' ?></h1>
<p class="lead">
    <?= $booking
        ? 'You can change a request while it is still pending.'
        : 'Choose a room and your dates. The admin will approve or decline your request, usually within 2 working days.' ?>
</p>

<?php if (!$rooms): ?>
    <div class="alert alert-error">No rooms are available right now. Please check back later.</div>
<?php else: ?>

<div class="book-layout">
    <form id="bookingForm" class="card form" method="post" action="book.php<?= $booking ? '?edit=' . e($editId) : '' ?>" novalidate>
        <?= csrf_field() ?>
        <?php if ($booking): ?>
            <input type="hidden" name="booking_id" value="<?= e($editId) ?>">
        <?php endif; ?>

        <?php if (isset($errors['form'])): ?>
            <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
        <?php elseif ($errors): ?>
            <div class="alert alert-error" role="alert">Please fix the highlighted fields below.</div>
        <?php endif; ?>

        <fieldset>
            <legend>1. Your room</legend>
            <div class="field">
                <label for="room_id">Room <span class="req" aria-hidden="true">*</span></label>
                <select id="room_id" name="room_id" required aria-describedby="room_id-error"<?= invalid_attr($errors, 'room_id') ?>>
                    <option value="">Choose a room…</option>
                    <?php foreach ($byProperty as $propertyName => $propertyRooms): ?>
                        <optgroup label="<?= e($propertyName) ?>">
                            <?php foreach ($propertyRooms as $r): ?>
                                <option value="<?= e($r['id']) ?>"
                                        data-property="<?= e($r['property_name']) ?>"
                                        data-area="<?= e($r['area']) ?>"
                                        data-distance="<?= e($r['distance_to_campus_km']) ?>"
                                        data-type="<?= e($r['room_type']) ?>"
                                        data-capacity="<?= e($r['capacity']) ?>"
                                        data-price="<?= e($r['price_per_month']) ?>"
                                    <?= (int) $r['id'] === (int) $data['room_id'] ? 'selected' : '' ?>>
                                    Room <?= e($r['room_number']) ?> · <?= e(ucfirst($r['room_type'])) ?> · <?= money($r['price_per_month']) ?>/month
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'room_id') ?>
            </div>
        </fieldset>

        <fieldset>
            <legend>2. Your dates</legend>
            <div class="field-row">
                <div class="field">
                    <label for="start_date">Move-in <span class="req" aria-hidden="true">*</span></label>
                    <input type="date" id="start_date" name="start_date" required
                           min="<?= e($today) ?>" max="<?= e($maxDate) ?>"
                           value="<?= e($data['start_date']) ?>"
                           aria-describedby="start_date-error"<?= invalid_attr($errors, 'start_date') ?>>
                    <?= field_error($errors, 'start_date') ?>
                </div>
                <div class="field">
                    <label for="end_date">Move-out <span class="req" aria-hidden="true">*</span></label>
                    <input type="date" id="end_date" name="end_date" required
                           min="<?= e($today) ?>"
                           value="<?= e($data['end_date']) ?>"
                           aria-describedby="end_date-hint end_date-error"<?= invalid_attr($errors, 'end_date') ?>>
                    <p class="hint" id="end_date-hint">Minimum 1 month, maximum <?= BOOKING_MAX_MONTHS ?> months.</p>
                    <?= field_error($errors, 'end_date') ?>
                </div>
            </div>
            <div class="quick-dates" role="group" aria-label="Quick date options">
                <span>Quick pick:</span>
                <button type="button" class="chip" data-months="5">Semester (5 months)</button>
                <button type="button" class="chip" data-months="10">Academic year (10 months)</button>
            </div>
        </fieldset>

        <fieldset>
            <legend>3. Anything the admin should know?</legend>
            <div class="field">
                <label for="notes">Note <span class="optional">(optional)</span></label>
                <textarea id="notes" name="notes" rows="4" maxlength="<?= BOOKING_NOTES_MAX ?>"
                          placeholder="e.g. arrival time, questions about the room"
                          aria-describedby="notes-count notes-error"<?= invalid_attr($errors, 'notes') ?>><?= e($data['notes']) ?></textarea>
                <p class="hint counter" id="notes-count" aria-live="polite"><?= mb_strlen($data['notes']) ?> / <?= BOOKING_NOTES_MAX ?></p>
                <?= field_error($errors, 'notes') ?>
            </div>
        </fieldset>

        <div class="field">
            <label class="check">
                <input type="checkbox" id="agree" name="agree" value="1" <?= $data['agree'] ? 'checked' : '' ?>
                       aria-describedby="agree-error"<?= invalid_attr($errors, 'agree') ?>>
                <span>I understand this is a <strong>request</strong>. It is only confirmed once an admin approves it.</span>
            </label>
            <?= field_error($errors, 'agree') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= $booking ? 'dashboard.php' : e($listingsUrl) ?>">Cancel</a>
            <button class="btn" type="submit"><?= $booking ? 'Save changes' : 'Send request' ?></button>
        </div>
    </form>

    <aside class="card summary" aria-labelledby="summary-title">
        <h2 id="summary-title">Your request</h2>
        <div id="summaryEmpty" class="muted"<?= $selected ? ' hidden' : '' ?>>Choose a room to see the price.</div>
        <div id="summaryBody"<?= $selected ? '' : ' hidden' ?>>
            <p class="summary-place" id="sumProperty"><?= e($selected['property_name'] ?? '') ?></p>
            <p class="muted" id="sumArea"><?= $selected ? e($selected['area']) . ' · ' . e($selected['distance_to_campus_km']) . ' km to NUST' : '' ?></p>
            <dl class="summary-list">
                <div><dt>Room type</dt><dd id="sumType"><?= e(ucfirst($selected['room_type'] ?? '')) ?></dd></div>
                <div><dt>Monthly rent</dt><dd id="sumPrice"><?= $selected ? money($selected['price_per_month']) : '' ?></dd></div>
                <div><dt>Stay</dt><dd id="sumStay">–</dd></div>
            </dl>
            <p class="summary-total"><span>Estimated total</span><strong id="sumTotal">–</strong></p>
            <p class="fineprint">Estimate only. You pay nothing now; the admin will contact you after approval.</p>
        </div>
        <hr>
        <h3>Booking as</h3>
        <p><strong><?= e($user['full_name']) ?></strong><br>
            <span class="muted"><?= e($user['email']) ?><?= $user['phone'] ? ' · ' . e($user['phone']) : '' ?></span></p>
    </aside>
</div>

<?php endif; ?>

<script src="js/booking-form.js"></script>
</main>

<?php require __DIR__ . '/includes/footer.php';   // Member 1's shared footer ?>
