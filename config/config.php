<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'irs_db');

define('SITE_NAME', 'IRS - International Registration Server');
define('SITE_URL', 'http://localhost/irs');
define('ADMIN_EMAIL', 'admin@irs-server.com');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024);

date_default_timezone_set('UTC');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
