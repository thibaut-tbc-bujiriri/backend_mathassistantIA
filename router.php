<?php
/**
 * Router pour le serveur PHP intégré sur Railway
 * Route les requêtes vers les fichiers dans api/
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Si c'est la racine, servir index.php
if ($requestPath === '/' || $requestPath === '' || empty($requestPath)) {
    $indexFile = __DIR__ . '/index.php';
    if (file_exists($indexFile)) {
        include $indexFile;
        return true;
    }
}

// Si c'est un fichier qui existe (CSS, JS, images, etc.), le servir directement
$filePath = __DIR__ . $requestPath;
if ($requestPath !== '/' && file_exists($filePath) && is_file($filePath) && $requestPath !== '/index.php') {
    return false; // Laisser PHP servir le fichier directement
}

// Router vers api/ pour les requêtes /api/*
if (strpos($requestPath, '/api/') === 0) {
    // Enlever le préfixe /api/
    $apiPath = substr($requestPath, 5);
    
    // Si vide, servir api/index.php
    if (empty($apiPath) || $apiPath === '/') {
        $apiPath = 'index.php';
    }
    
    $apiFilePath = __DIR__ . '/api/' . $apiPath;
    
    if (file_exists($apiFilePath) && is_file($apiFilePath) && pathinfo($apiFilePath, PATHINFO_EXTENSION) === 'php') {
        // Changer le répertoire vers api/ pour que require_once fonctionne
        $originalDir = getcwd();
        chdir(__DIR__ . '/api');
        
        include $apiFilePath;
        
        chdir($originalDir);
        return true;
    }
}

// Pour index.php à la racine
if ($requestPath === '/index.php' && file_exists(__DIR__ . '/index.php')) {
    include __DIR__ . '/index.php';
    return true;
}

// 404 - Endpoint non trouvé
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => false,
    'message' => 'Endpoint non trouvé',
    'request_path' => $requestPath
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
return true;

