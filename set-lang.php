<?php
require_once __DIR__ . '/config/config.php';
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en']) ? $_GET['lang'] : 'fr';
$_SESSION['lang'] = $lang;
// Aussi dans le cookie pour la lecture JS sans rechargement
setcookie('lang', $lang, time() + 365 * 24 * 3600, '/');

// Support AJAX (pas de redirect, juste JSON)
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'lang' => $lang]);
    exit();
}

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/index.php';
if (!preg_match('/^\//', $redirect)) $redirect = '/index.php';
header('Location: ' . $redirect);
exit();
