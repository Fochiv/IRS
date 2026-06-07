<?php
require_once __DIR__ . '/../config/config.php';
$_SESSION = [];
session_destroy();
header('Location: /admin/login.php?msg=logged_out');
exit();
