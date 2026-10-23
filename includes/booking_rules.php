<?php
// Booking rules shared by book.php (create + edit).
// The same rules are repeated in js/booking-form.js so the student gets instant feedback,
// but THIS file is the one that actually protects the database - JavaScript can be switched off.

const BOOKING_MIN_DAYS   = 28;   // shortest stay we accept (about one month)
const BOOKING_MAX_MONTHS = 12;   // longest stay we accept
const BOOKING_NOTES_MAX  = 500;  // characters

// Turn a 'YYYY-MM-DD' string into a DateTime, or null if it is not a real date
function parse_date($value)
{
    $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
    return ($date && $date->format('Y-m-d') === $value) ? $date : null;
}

// How many (started) months a stay covers, e.g. 1 Feb - 30 Jun = 5
function months_between(DateTime $start, DateTime $end)
{
    $months = ((int) $end->format('Y') - (int) $start->format('Y')) * 12
            + ((int) $end->format('n') - (int) $start->format('n'));
    // A part-month at the end counts as a full month of rent
    if ((int) $end->format('j') > (int) $start->format('j')) {
        $months++;
    }
    return max(1, $months);
}

// All rooms a student may pick, grouped later by property.
// $includeRoomId lets the edit page keep showing the room already chosen, even if it is no longer free.
function bookable_rooms(PDO $pdo, $includeRoomId = 0)
{
    $stmt = $pdo->prepare(
        'SELECT r.id, r.room_number, r.room_type, r.capacity, r.price_per_month,
                p.id AS property_id, p.name AS property_name, p.area, p.distance_to_campus_km
           FROM rooms r
           JOIN properties p ON p.id = r.property_id
          WHERE r.is_available = 1 OR r.id = ?
          ORDER BY p.name, r.room_number'
    );
    $stmt->execute([(int) $includeRoomId]);
    return $stmt->fetchAll();
}

// Check a submitted booking. Returns [cleanData, errors].
// $editId is the booking being edited (0 when creating a new one).
function validate_booking(PDO $pdo, array $input, $userId, array $rooms, $editId = 0)
{
    $errors = [];
    $data = [
        'room_id'    => (int) ($input['room_id'] ?? 0),
        'start_date' => trim($input['start_date'] ?? ''),
        'end_date'   => trim($input['end_date'] ?? ''),
        'notes'      => trim($input['notes'] ?? ''),
        'agree'      => !empty($input['agree']),
    ];

    // Room: must be one of the rooms we offered
    $room = null;
    foreach ($rooms as $r) {
        if ((int) $r['id'] === $data['room_id']) {
            $room = $r;
        }
    }
    if (!$room) {
        $errors['room_id'] = 'Choose a room from the list.';
    }

    // Dates
    $today = new DateTime('today');
    $start = parse_date($data['start_date']);
    $end   = parse_date($data['end_date']);

    if ($data['start_date'] === '') {
        $errors['start_date'] = 'Choose a move-in date.';
    } elseif (!$start) {
        $errors['start_date'] = 'Enter a valid move-in date.';
    } elseif ($start < $today) {
        $errors['start_date'] = 'Move-in date cannot be in the past.';
    } elseif ($start > (clone $today)->modify('+1 year')) {
        $errors['start_date'] = 'You can only book up to a year ahead.';
    }

    if ($data['end_date'] === '') {
        $errors['end_date'] = 'Choose a move-out date.';
    } elseif (!$end) {
        $errors['end_date'] = 'Enter a valid move-out date.';
    } elseif ($start && $end <= $start) {
        $errors['end_date'] = 'Move-out must be after move-in.';
    } elseif ($start && $start->diff($end)->days < BOOKING_MIN_DAYS) {
        $errors['end_date'] = 'The minimum stay is one month.';
    } elseif ($start && $end > (clone $start)->modify('+' . BOOKING_MAX_MONTHS . ' months')) {
        $errors['end_date'] = 'The maximum stay is ' . BOOKING_MAX_MONTHS . ' months.';
    }

    // Notes
    if (mb_strlen($data['notes']) > BOOKING_NOTES_MAX) {
        $errors['notes'] = 'Keep your note under ' . BOOKING_NOTES_MAX . ' characters.';
    }

    // Terms
    if (!$data['agree']) {
        $errors['agree'] = 'Please confirm you understand this is a request.';
    }

    // Database checks only make sense once the basics are fine
    if (!$errors) {
        // 1. Is the room already full for these dates? (approved bookings that overlap)
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
              WHERE room_id = ? AND status = 'approved'
                AND start_date < ? AND end_date > ? AND id <> ?"
        );
        $stmt->execute([$data['room_id'], $data['end_date'], $data['start_date'], (int) $editId]);
        if ((int) $stmt->fetchColumn() >= (int) $room['capacity']) {
            $errors['room_id'] = 'This room is already fully booked for those dates. Try other dates or another room.';
        }

        // 2. Has this student already asked for this room for overlapping dates?
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
              WHERE user_id = ? AND room_id = ? AND status IN ('pending', 'approved')
                AND start_date < ? AND end_date > ? AND id <> ?"
        );
        $stmt->execute([$userId, $data['room_id'], $data['end_date'], $data['start_date'], (int) $editId]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['room_id'] = 'You already have a booking for this room on those dates. Check your dashboard.';
        }
    }

    return [$data, $errors];
}
