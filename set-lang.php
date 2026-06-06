<?php
require_once __DIR__ . '/config/config.php';
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en']) ? $_GET['lang'] : 'fr';
$_SESSION['lang'] = $lang;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/index.php';
if (!preg_match('/^\//', $redirect)) $redirect = '/index.php';
header('Location: ' . $redirect);
exit();
