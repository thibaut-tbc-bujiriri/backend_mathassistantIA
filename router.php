<?php
/**
 * Router pour le serveur PHP intégré sur Railway
 * Route toutes les requêtes vers index.php
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Si c'est un fichier qui existe (CSS, JS, images, etc.), le servir directement
$filePath = __DIR__ . $requestPath;
if ($requestPath !== '/' && file_exists($filePath) && is_file($filePath)) {
    return false; // Laisser PHP servir le fichier directement
}

// Pour toutes les autres requêtes, router vers index.php
if (file_exists(__DIR__ . '/index.php')) {
    include __DIR__ . '/index.php';
    return true;
}

// Si index.php n'existe pas, retourner 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Backend not configured properly'
]);
return true;

