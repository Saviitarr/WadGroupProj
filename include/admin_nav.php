<?php $adminPage = basename($_SERVER['SCRIPT_NAME'] ?? ''); ?>
<div class="admin-bar">
    <span class="admin-tag">ADMIN</span>
    <nav class="admin-tabs" aria-label="Admin sections">
        <a href="index.php"<?= $adminPage === 'index.php' ? ' aria-current="page"' : '' ?>>Overview</a>
        <a href="bookings.php"<?= $adminPage === 'bookings.php' ? ' aria-current="page"' : '' ?>>Bookings</a>
        <a href="rooms.php"<?= in_array($adminPage, ['rooms.php', 'room-form.php'], true) ? ' aria-current="page"' : '' ?>>Rooms</a>
        <a href="messages.php"<?= $adminPage === 'messages.php' ? ' aria-current="page"' : '' ?>>Messages</a>
    </nav>
</div>
