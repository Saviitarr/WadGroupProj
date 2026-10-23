<?php
require 'include/auth.php';   // starts the session, so there is one to destroy

$_SESSION = [];          // clear the session data
session_destroy();       // delete the session on the server

header('Location: login.php');
exit;
