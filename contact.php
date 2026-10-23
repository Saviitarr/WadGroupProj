<?php
// contact.php - contact page and form; messages are saved to the `contact` table (Member 3)
// Admins read them in the admin area (Member 5).
require_once __DIR__ . '/includes/helpers.php';

$subjects = [
    'Booking question',
    'Listing a property',
    'Account or login help',
    'Report a problem',
    'Other',
];

$user = current_user($pdo);

// Logged-in users get their name and email filled in for them
$data = [
    'name'    => $user['full_name'] ?? '',
    'email'   => $user['email'] ?? '',
    'subject' => '',
    'message' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'    => trim($_POST['name'] ?? ''),
        'email'   => trim($_POST['email'] ?? ''),
        'subject' => trim($_POST['subject'] ?? ''),
        'message' => trim($_POST['message'] ?? ''),
    ];

    if (!csrf_ok()) {
        $errors['form'] = 'Your session expired. Please send the form again.';
    }

    // Honeypot: a hidden field real people never fill in. Bots usually do.
    if (!empty($_POST['website'])) {
        $errors['form'] = 'Message could not be sent.';
    }

    if ($data['name'] === '') {
        $errors['name'] = 'Enter your name.';
    } elseif (mb_strlen($data['name']) > 100) {
        $errors['name'] = 'Name is too long (100 characters max).';
    }

    if ($data['email'] === '') {
        $errors['email'] = 'Enter your email address.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) {
        $errors['email'] = 'Enter a valid email address, like name@example.com.';
    }

    if (!in_array($data['subject'], $subjects, true)) {
        $errors['subject'] = 'Choose what your message is about.';
    }

    if (mb_strlen($data['message']) < 10) {
        $errors['message'] = 'Write a message of at least 10 characters.';
    } elseif (mb_strlen($data['message']) > 2000) {
        $errors['message'] = 'Keep your message under 2000 characters.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO contact (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['email'], $data['subject'], $data['message']]);

        flash('success', 'Thanks, ' . explode(' ', $data['name'])[0] . '! Your message was sent. We usually reply within 2 working days.');
        header('Location: contact.php');
        exit;
    }
}

function field_error($errors, $name)
{
    $msg = $errors[$name] ?? '';
    return '<p class="field-error" id="' . $name . '-error"' . ($msg ? '' : ' hidden') . '>' . e($msg) . '</p>';
}
function invalid_attr($errors, $name)
{
    return isset($errors[$name]) ? ' aria-invalid="true"' : '';
}

$pageTitle = 'Contact us';
$pageCss   = ['css/forms.css'];   // styles for the Member 3 pages
require __DIR__ . '/includes/header.php';   // Member 1's shared header
?>
<main class="m3 wrap page">
<?php show_flash(); ?>

<h1>Contact us</h1>
<p class="lead">Questions about a room, a booking or your account? Send us a message.</p>

<div class="contact-layout">
    <form id="contactForm" class="card form" method="post" action="contact.php" novalidate>
        <?= csrf_field() ?>

        <?php if (isset($errors['form'])): ?>
            <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
        <?php elseif ($errors): ?>
            <div class="alert alert-error" role="alert">Please fix the highlighted fields below.</div>
        <?php endif; ?>

        <div class="field-row">
            <div class="field">
                <label for="name">Full name <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" required maxlength="100" autocomplete="name"
                       value="<?= e($data['name']) ?>" aria-describedby="name-error"<?= invalid_attr($errors, 'name') ?>>
                <?= field_error($errors, 'name') ?>
            </div>
            <div class="field">
                <label for="email">Email <span class="req" aria-hidden="true">*</span></label>
                <input type="email" id="email" name="email" required maxlength="150" autocomplete="email"
                       value="<?= e($data['email']) ?>" aria-describedby="email-error"<?= invalid_attr($errors, 'email') ?>>
                <?= field_error($errors, 'email') ?>
            </div>
        </div>

        <div class="field">
            <label for="subject">What is it about? <span class="req" aria-hidden="true">*</span></label>
            <select id="subject" name="subject" required aria-describedby="subject-error"<?= invalid_attr($errors, 'subject') ?>>
                <option value="">Choose a topic…</option>
                <?php foreach ($subjects as $s): ?>
                    <option<?= $s === $data['subject'] ? ' selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'subject') ?>
        </div>

        <div class="field">
            <label for="message">Message <span class="req" aria-hidden="true">*</span></label>
            <textarea id="message" name="message" rows="6" required maxlength="2000"
                      aria-describedby="message-count message-error"<?= invalid_attr($errors, 'message') ?>><?= e($data['message']) ?></textarea>
            <p class="hint counter" id="message-count" aria-live="polite"><?= mb_strlen($data['message']) ?> / 2000</p>
            <?= field_error($errors, 'message') ?>
        </div>

        <!-- Honeypot: hidden from people, tempting for bots -->
        <div class="hp" aria-hidden="true">
            <label for="website">Leave this empty</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">Send message</button>
        </div>
    </form>

    <aside class="contact-side">
        <div class="card">
            <h2>Other ways to reach us</h2>
            <ul class="contact-list">
                <li><span class="label">Email</span><a class="link" href="mailto:support@campusconnect.example">support@campusconnect.example</a></li>
                <li><span class="label">Phone</span><a class="link" href="tel:+264612072111">+264 61 207 2111</a></li>
                <li><span class="label">Office</span>Student Services, NUST main campus<br>13 Jackson Kaujeua Street, Windhoek</li>
                <li><span class="label">Hours</span>Mon–Fri, 08:00–16:30</li>
            </ul>
        </div>
        <div class="card faq">
            <h2>Quick answers</h2>
            <details>
                <summary>How long does approval take?</summary>
                <p>Most requests are approved or declined within 2 working days.</p>
            </details>
            <details>
                <summary>Can I change my booking?</summary>
                <p>Yes. While it is pending you can edit or cancel it from <a class="link" href="dashboard.php">My bookings</a>.</p>
            </details>
            <details>
                <summary>I want to list my property</summary>
                <p>Choose “Listing a property” above and tell us about the rooms. We will get back to you.</p>
            </details>
        </div>
    </aside>
</div>

<script src="js/contact-form.js"></script>
</main>

<?php require __DIR__ . '/includes/footer.php';   // Member 1's shared footer ?>
