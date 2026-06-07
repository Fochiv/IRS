<?php
// ─── Détection de l'environnement ───────────────────────────────────────────
// 1. Replit  →  MariaDB local lancé par start.sh
// 2. Aeonfree / hébergement mutualisé  →  MySQL distant
// 3. WAMP / XAMPP / Laragon local  →  MySQL local

$isReplit   = isset($_SERVER['REPLIT_DB_URL']) || getenv('REPLIT_DB_URL') || file_exists('/home/runner');
$isAeonfree = (!$isReplit && isset($_SERVER['HTTP_HOST']) && (
    str_contains($_SERVER['SERVER_SOFTWARE'] ?? '', 'LiteSpeed') ||
    getenv('AEONFREE') === '1' ||
    file_exists(__DIR__ . '/../.aeonfree')   // fichier sentinelle optionnel
));

if ($isReplit) {
    // ── Replit (développement) ──────────────────────────────────────────────
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 3306);
    define('DB_NAME', 'irs_db');

} elseif ($isAeonfree) {
    // ── Aeonfree / hébergement mutualisé (production) ───────────────────────
    define('DB_HOST', 'sql112.hstn.me');
    define('DB_USER', 'mseet_42122245');
    define('DB_PASS', '1214161820Ben');
    define('DB_PORT', 3306);
    define('DB_NAME', 'mseet_42122245_irs_db');

} else {
    // ── WAMP / XAMPP / Laragon (local) ─────────────────────────────────────
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 3306);
    define('DB_NAME', 'irs_db');
}

define('SITE_NAME', 'IRS - International Registration Server');

if ($isReplit && isset($_SERVER['HTTP_HOST'])) {
    define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
} elseif ($isReplit) {
    define('SITE_URL', 'http://localhost:5000');
} elseif (isset($_SERVER['HTTP_HOST'])) {
    define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
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
