<?php
// FICHIER TEMPORAIRE DE DIAGNOSTIC — SUPPRIMER APRÈS VÉRIFICATION
// Accès : https://irs-server.zya.me/test-connexion.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<style>body{font-family:sans-serif;padding:30px;max-width:700px;margin:auto;}
.ok{color:green;font-weight:bold;} .err{color:red;font-weight:bold;}
.box{background:#f5f5f5;border:1px solid #ddd;padding:15px;border-radius:8px;margin-bottom:15px;}
h2{color:#0A2342;}</style>';

echo '<h2>🔍 Diagnostic IRS — Aeonfree</h2>';

// 1. Version PHP
echo '<div class="box">';
echo '<b>PHP Version :</b> ' . phpversion() . ' ';
echo version_compare(phpversion(), '7.4', '>=')
    ? '<span class="ok">✓ OK</span>'
    : '<span class="err">✗ Trop ancien (7.4+ requis)</span>';
echo '</div>';

// 2. Extensions
echo '<div class="box"><b>Extensions PHP :</b><br>';
$exts = ['mysqli', 'pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
foreach ($exts as $ext) {
    $ok = extension_loaded($ext);
    echo "&nbsp;&nbsp;• $ext : " . ($ok ? '<span class="ok">✓ Chargée</span>' : '<span class="err">✗ Manquante</span>') . '<br>';
}
echo '</div>';

// 3. Test connexion DB
echo '<div class="box"><b>Connexion Base de Données :</b><br>';
$host = 'sql112.hstn.me';
$user = 'mseet_42122245';
$pass = '1214161820Ben';
$db   = 'mseet_42122245_irs_db';

$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo '<span class="err">✗ Erreur : ' . htmlspecialchars($conn->connect_error) . '</span>';
} else {
    echo '<span class="ok">✓ Connexion réussie à ' . $host . '</span><br>';
    // Vérifier les tables
    $tables = ['admins', 'users', 'documents', 'submitted_documents', 'notifications', 'settings'];
    echo '<br><b>Tables :</b><br>';
    foreach ($tables as $t) {
        $r = $conn->query("SHOW TABLES LIKE '$t'");
        $exists = $r && $r->num_rows > 0;
        echo "&nbsp;&nbsp;• $t : " . ($exists ? '<span class="ok">✓ Présente</span>' : '<span class="err">✗ Manquante — importer le SQL</span>') . '<br>';
    }
    $conn->close();
}
echo '</div>';

// 4. Dossier uploads
echo '<div class="box"><b>Dossier uploads/ :</b><br>';
$upDir = __DIR__ . '/uploads';
echo '&nbsp;&nbsp;• Existe : ' . (is_dir($upDir) ? '<span class="ok">✓</span>' : '<span class="err">✗</span>') . '<br>';
echo '&nbsp;&nbsp;• Écriture : ' . (is_writable($upDir) ? '<span class="ok">✓ Accessible</span>' : '<span class="err">✗ Non accessible — mettre en 755</span>') . '<br>';
echo '</div>';

// 5. Config détection
echo '<div class="box"><b>Détection environnement :</b><br>';
$isReplit = isset($_SERVER['REPLIT_DB_URL']) || getenv('REPLIT_DB_URL') || file_exists('/home/runner');
$isAeonfree = !$isReplit && (file_exists(__DIR__ . '/.aeonfree') || getenv('AEONFREE') === '1');
echo '&nbsp;&nbsp;• Replit : ' . ($isReplit ? '<span class="ok">OUI</span>' : 'non') . '<br>';
echo '&nbsp;&nbsp;• Aeonfree (.aeonfree file) : ' . ($isAeonfree ? '<span class="ok">OUI</span>' : 'non (fallback Aeonfree utilisé)') . '<br>';
echo '&nbsp;&nbsp;• Environnement actif : <b>' . ($isReplit ? 'Replit' : 'Aeonfree/Production') . '</b><br>';
echo '</div>';

echo '<p style="color:#888;font-size:0.85rem;">⚠️ Supprimez ce fichier après vérification : <code>test-connexion.php</code></p>';
