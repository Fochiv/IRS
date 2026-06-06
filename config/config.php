<?php
// Détection automatique de l'environnement (Replit ou WAMP local)
$isReplit = isset($_SERVER['REPLIT_DB_URL']) || getenv('REPLIT_DB_URL') || file_exists('/home/runner');

if ($isReplit) {
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 3306);
} else {
    // WAMP / XAMPP / Laragon local
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 3306);
}

define('DB_NAME', 'irs_db');

define('SITE_NAME', 'IRS - International Registration Server');

if ($isReplit && isset($_SERVER['HTTP_HOST'])) {
    define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
} elseif ($isReplit) {
    define('SITE_URL', 'http://localhost:5000');
} else {
    define('SITE_URL', 'http://localhost/irs');
}

define('ADMIN_EMAIL', 'admin@irs-server.com');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024);

date_default_timezone_set('UTC');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
