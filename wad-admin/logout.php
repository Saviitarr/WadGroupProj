<?php
require_once __DIR__ . "/auth.php";

$_SESSION = [];
session_regenerate_id(true);
header("Location: login.php");
exit;
