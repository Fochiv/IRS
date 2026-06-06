<?php
require_once __DIR__ . '/../config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#DC3545;">
        <h2>Erreur de connexion à la base de données</h2>
        <p>' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Veuillez vérifier la configuration dans <code>config/config.php</code></p>
    </div>');
}

$conn->set_charset('utf8mb4');
