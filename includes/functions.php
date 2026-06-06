<?php
require_once __DIR__ . '/../includes/db.php';

function sanitize($conn, $data) {
    return $conn->real_escape_string(trim(htmlspecialchars($data)));
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireUserLogin() {
    if (!isUserLoggedIn()) {
        redirect('/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect('/admin/login.php');
    }
}

function getUserById($conn, $id) {
    $id = (int)$id;
    $result = $conn->query("SELECT * FROM users WHERE id = $id LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

function getAdminById($conn, $id) {
    $id = (int)$id;
    $result = $conn->query("SELECT * FROM admins WHERE id = $id LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

function logActivity($conn, $action, $description, $user_id = null, $admin_id = null) {
    $ip = $conn->real_escape_string($_SERVER['REMOTE_ADDR'] ?? '');
    $action = $conn->real_escape_string($action);
    $description = $conn->real_escape_string($description);
    $user_id = $user_id ? (int)$user_id : 'NULL';
    $admin_id = $admin_id ? (int)$admin_id : 'NULL';
    $conn->query("INSERT INTO activity_log (user_id, admin_id, action, description, ip_address) VALUES ($user_id, $admin_id, '$action', '$description', '$ip')");
}

function createNotification($conn, $user_id, $title, $message, $type = 'info') {
    $user_id = (int)$user_id;
    $title = $conn->real_escape_string($title);
    $message = $conn->real_escape_string($message);
    $type = $conn->real_escape_string($type);
    $conn->query("INSERT INTO notifications (user_id, title, message, type) VALUES ($user_id, '$title', '$message', '$type')");
}

function getUserNotificationsCount($conn, $user_id) {
    $user_id = (int)$user_id;
    $result = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = $user_id AND is_read = 0");
    if ($result) {
        $row = $result->fetch_assoc();
        return (int)$row['cnt'];
    }
    return 0;
}

function getStatusBadge($status) {
    $badges = [
        'pending'        => ['warning', 'En attente', 'hourglass-split'],
        'validated'      => ['success', 'Validé', 'check-circle-fill'],
        'rejected'       => ['danger', 'Rejeté', 'x-circle-fill'],
        'info_requested' => ['info', 'Info requise', 'info-circle-fill'],
        'verified'       => ['success', 'Vérifié', 'patch-check-fill'],
    ];
    $b = $badges[$status] ?? ['secondary', ucfirst($status), 'question-circle'];
    return '<span class="badge bg-' . $b[0] . '"><i class="bi bi-' . $b[2] . ' me-1"></i>' . $b[1] . '</span>';
}

function getDocumentStats($conn, $user_id) {
    $user_id = (int)$user_id;
    $stats = ['total' => 0, 'validated' => 0, 'rejected' => 0, 'pending' => 0];
    $result = $conn->query("SELECT status, COUNT(*) as cnt FROM submitted_documents WHERE user_id = $user_id GROUP BY status");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats[$row['status']] = (int)$row['cnt'];
            $stats['total'] += (int)$row['cnt'];
        }
    }
    return $stats;
}

function uploadFile($file, $subdir = 'documents') {
    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dest = UPLOAD_DIR . $subdir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/' . $subdir . '/' . $filename;
    }
    return false;
}

function getLang() {
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['fr', 'en'])) {
        return $_SESSION['lang'];
    }
    return 'fr';
}

function t($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

function loadTranslations() {
    global $translations;
    $lang = getLang();
    $file = __DIR__ . '/../lang/' . $lang . '.php';
    if (file_exists($file)) {
        $translations = require $file;
    } else {
        $translations = require __DIR__ . '/../lang/fr.php';
    }
}

function getTheme() {
    if (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') return 'dark';
    return 'light';
}

function formatDate($date) {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($date) {
    if (!$date) return '-';
    return date('d/m/Y H:i', strtotime($date));
}

function getCountriesList() {
    return [
        'AF' => 'Afghanistan', 'AL' => 'Albanie', 'DZ' => 'Algérie', 'AD' => 'Andorre',
        'AO' => 'Angola', 'AG' => 'Antigua-et-Barbuda', 'AR' => 'Argentine', 'AM' => 'Arménie',
        'AU' => 'Australie', 'AT' => 'Autriche', 'AZ' => 'Azerbaïdjan', 'BS' => 'Bahamas',
        'BH' => 'Bahreïn', 'BD' => 'Bangladesh', 'BB' => 'Barbade', 'BY' => 'Biélorussie',
        'BE' => 'Belgique', 'BZ' => 'Belize', 'BJ' => 'Bénin', 'BT' => 'Bhoutan',
        'BO' => 'Bolivie', 'BA' => 'Bosnie-Herzégovine', 'BW' => 'Botswana', 'BR' => 'Brésil',
        'BN' => 'Brunei', 'BG' => 'Bulgarie', 'BF' => 'Burkina Faso', 'BI' => 'Burundi',
        'CV' => 'Cap-Vert', 'KH' => 'Cambodge', 'CM' => 'Cameroun', 'CA' => 'Canada',
        'CF' => 'Centrafrique', 'TD' => 'Tchad', 'CL' => 'Chili', 'CN' => 'Chine',
        'CO' => 'Colombie', 'KM' => 'Comores', 'CG' => 'Congo', 'CD' => 'Congo (RDC)',
        'CR' => 'Costa Rica', 'CI' => "Côte d'Ivoire", 'HR' => 'Croatie', 'CU' => 'Cuba',
        'CY' => 'Chypre', 'CZ' => 'République tchèque', 'DK' => 'Danemark', 'DJ' => 'Djibouti',
        'DM' => 'Dominique', 'DO' => 'République dominicaine', 'EC' => 'Équateur', 'EG' => 'Égypte',
        'SV' => 'Salvador', 'GQ' => 'Guinée équatoriale', 'ER' => 'Érythrée', 'EE' => 'Estonie',
        'SZ' => 'Eswatini', 'ET' => 'Éthiopie', 'FJ' => 'Fidji', 'FI' => 'Finlande',
        'FR' => 'France', 'GA' => 'Gabon', 'GM' => 'Gambie', 'GE' => 'Géorgie',
        'DE' => 'Allemagne', 'GH' => 'Ghana', 'GR' => 'Grèce', 'GD' => 'Grenade',
        'GT' => 'Guatemala', 'GN' => 'Guinée', 'GW' => 'Guinée-Bissau', 'GY' => 'Guyana',
        'HT' => 'Haïti', 'HN' => 'Honduras', 'HU' => 'Hongrie', 'IS' => 'Islande',
        'IN' => 'Inde', 'ID' => 'Indonésie', 'IR' => 'Iran', 'IQ' => 'Irak',
        'IE' => 'Irlande', 'IL' => 'Israël', 'IT' => 'Italie', 'JM' => 'Jamaïque',
        'JP' => 'Japon', 'JO' => 'Jordanie', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya',
        'KI' => 'Kiribati', 'KW' => 'Koweït', 'KG' => 'Kirghizstan', 'LA' => 'Laos',
        'LV' => 'Lettonie', 'LB' => 'Liban', 'LS' => 'Lesotho', 'LR' => 'Liberia',
        'LY' => 'Libye', 'LI' => 'Liechtenstein', 'LT' => 'Lituanie', 'LU' => 'Luxembourg',
        'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaisie', 'MV' => 'Maldives',
        'ML' => 'Mali', 'MT' => 'Malte', 'MH' => 'Marshall', 'MR' => 'Mauritanie',
        'MU' => 'Maurice', 'MX' => 'Mexique', 'FM' => 'Micronésie', 'MD' => 'Moldavie',
        'MC' => 'Monaco', 'MN' => 'Mongolie', 'ME' => 'Monténégro', 'MA' => 'Maroc',
        'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibie', 'NR' => 'Nauru',
        'NP' => 'Népal', 'NL' => 'Pays-Bas', 'NZ' => 'Nouvelle-Zélande', 'NI' => 'Nicaragua',
        'NE' => 'Niger', 'NG' => 'Nigeria', 'NO' => 'Norvège', 'OM' => 'Oman',
        'PK' => 'Pakistan', 'PW' => 'Palaos', 'PA' => 'Panama', 'PG' => 'Papouasie',
        'PY' => 'Paraguay', 'PE' => 'Pérou', 'PH' => 'Philippines', 'PL' => 'Pologne',
        'PT' => 'Portugal', 'QA' => 'Qatar', 'RO' => 'Roumanie', 'RU' => 'Russie',
        'RW' => 'Rwanda', 'KN' => 'Saint-Kitts', 'LC' => 'Sainte-Lucie', 'VC' => 'Saint-Vincent',
        'WS' => 'Samoa', 'SM' => 'Saint-Marin', 'ST' => 'São Tomé', 'SA' => 'Arabie saoudite',
        'SN' => 'Sénégal', 'RS' => 'Serbie', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone',
        'SG' => 'Singapour', 'SK' => 'Slovaquie', 'SI' => 'Slovénie', 'SB' => 'Salomon',
        'SO' => 'Somalie', 'ZA' => 'Afrique du Sud', 'SS' => 'Soudan du Sud', 'ES' => 'Espagne',
        'LK' => 'Sri Lanka', 'SD' => 'Soudan', 'SR' => 'Suriname', 'SE' => 'Suède',
        'CH' => 'Suisse', 'SY' => 'Syrie', 'TW' => 'Taïwan', 'TJ' => 'Tadjikistan',
        'TZ' => 'Tanzanie', 'TH' => 'Thaïlande', 'TL' => 'Timor oriental', 'TG' => 'Togo',
        'TO' => 'Tonga', 'TT' => 'Trinité-et-Tobago', 'TN' => 'Tunisie', 'TR' => 'Turquie',
        'TM' => 'Turkménistan', 'TV' => 'Tuvalu', 'UG' => 'Ouganda', 'UA' => 'Ukraine',
        'AE' => 'Émirats arabes unis', 'GB' => 'Royaume-Uni', 'US' => 'États-Unis',
        'UY' => 'Uruguay', 'UZ' => 'Ouzbékistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela',
        'VN' => 'Vietnam', 'YE' => 'Yémen', 'ZM' => 'Zambie', 'ZW' => 'Zimbabwe',
    ];
}

loadTranslations();
