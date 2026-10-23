<?php
require 'config/db.php';
echo implode(', ', $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
