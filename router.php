<?php
/**
 * Router pour le serveur PHP intégré
 * Utilisé par Railway pour router les requêtes vers les fichiers dans api/
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Log pour débogage (peut être supprimé en production)
error_log("Router: REQUEST_URI = $requestUri");
error_log("Router: Parsed path = $requestPath");

// Enlever le préfixe /api si présent
if (strpos($requestPath, '/api/') === 0) {
    $requestPath = substr($requestPath, 4); // Enlève '/api'
    error_log("Router: After removing /api, path = $requestPath");
}

// Si c'est la racine ou vide, servir index.php
if ($requestPath === '/' || $requestPath === '' || $requestPath === '/api') {
    $requestPath = '/index.php';
}

// S'assurer que le chemin commence par /
if (!str_starts_with($requestPath, '/')) {
    $requestPath = '/' . $requestPath;
}

// Avec -t api, le document root est api/, donc on cherche directement dans api/
// router.php est dans la racine, donc __DIR__ pointe vers la racine
$filePath = __DIR__ . '/api' . $requestPath;
error_log("Router: Looking for file at: $filePath");
error_log("Router: File exists? " . (file_exists($filePath) ? 'YES' : 'NO'));

// Si le fichier existe et est un fichier PHP, le laisser être servi par PHP
// Le serveur PHP intégré le servira automatiquement si on retourne false
if (file_exists($filePath) && is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
    error_log("Router: Found PHP file, letting PHP serve it");
    // Laisser PHP servir le fichier directement
    return false;
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
    error_log("Router: Serving api/index.php");
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

