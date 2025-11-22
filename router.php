<?php
/**
 * Router pour le serveur PHP intégré
 * Utilisé par Railway pour router les requêtes vers les fichiers dans api/
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Enlever le préfixe /api si présent
if (strpos($requestPath, '/api/') === 0) {
    $requestPath = substr($requestPath, 4);
}

// Si c'est la racine, servir index.php
if ($requestPath === '/' || $requestPath === '') {
    $requestPath = '/index.php';
}

// Construire le chemin complet vers le fichier
$filePath = __DIR__ . '/api' . $requestPath;

// Si le fichier existe et est un fichier PHP, l'inclure
if (file_exists($filePath) && is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
    return false; // Laisser PHP servir le fichier directement
}

// Si c'est un fichier qui existe (images, CSS, etc.), le servir
if (file_exists($filePath) && is_file($filePath)) {
    return false;
}

// Si c'est un répertoire, chercher index.php dedans
if (is_dir($filePath)) {
    $indexFile = $filePath . '/index.php';
    if (file_exists($indexFile)) {
        include $indexFile;
        return true;
    }
}

// Sinon, servir index.php de api/
$indexPath = __DIR__ . '/api/index.php';
if (file_exists($indexPath)) {
    include $indexPath;
    return true;
}

// Si rien n'est trouvé, retourner 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Endpoint non trouvé',
    'path' => $requestPath
]);
return true;

