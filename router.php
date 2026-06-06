<?php
// Router pour le serveur PHP built-in
// Gère les fichiers statiques et redirige vers index.php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Servir les fichiers statiques (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    // Laisser PHP gérer les fichiers .php, sinon servir statiquement
    if (pathinfo($uri, PATHINFO_EXTENSION) !== 'php') {
        return false;
    }
}

// Réécriture des URLs sans sous-dossier /irs/
// Si l'URI est /, servir index.php
if ($uri === '/') {
    require __DIR__ . '/index.php';
    return;
}

// Chercher le fichier PHP correspondant
$file = __DIR__ . $uri;

if (file_exists($file) && is_file($file)) {
    require $file;
    return;
}

// Fichier non trouvé -> 404
http_response_code(404);
echo '<h1>404 - Page non trouvée</h1>';
