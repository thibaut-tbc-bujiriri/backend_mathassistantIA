<?php
/**
 * Point d'entrée principal du backend PHP
 * Backend Math Assistant - API REST
 */

// Charger l'autoloader Composer si disponible
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Router les requêtes vers le dossier api/
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Si c'est la racine, afficher un message de statut
if ($requestPath === '/' || $requestPath === '/index.php' || empty($requestPath)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'Backend PHP is running!',
        'service' => 'Math Assistant API',
        'version' => '1.0.0',
        'status' => 'online',
        'api_endpoint' => '/api/',
        'documentation' => 'Consultez /api/ pour la liste des endpoints disponibles'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Router vers api/ pour toutes les autres requêtes
if (strpos($requestPath, '/api/') === 0) {
    // Enlever le préfixe /api/ et router vers le fichier correspondant
    $apiPath = substr($requestPath, 5); // Enlever '/api/'
    
    // Si c'est vide ou juste '/', servir api/index.php
    if (empty($apiPath) || $apiPath === '/') {
        $apiPath = 'index.php';
    }
    
    // Construire le chemin complet vers le fichier API
    $filePath = __DIR__ . '/api/' . $apiPath;
    
    // Si le fichier existe et est un fichier PHP, l'inclure
    if (file_exists($filePath) && is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        // Changer le répertoire de travail vers api/ pour que les require_once fonctionnent
        $originalDir = getcwd();
        chdir(__DIR__ . '/api');
        
        // Inclure le fichier API
        include $filePath;
        
        // Restaurer le répertoire de travail
        chdir($originalDir);
        exit;
    }
}

// Si on arrive ici, c'est une 404
header('Content-Type: application/json; charset=utf-8');
http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Endpoint non trouvé',
    'request_path' => $requestPath,
    'available_endpoints' => [
        'GET /' => 'Statut du backend',
        'GET /api/' => 'Liste des endpoints API',
        'POST /api/login.php' => 'Connexion utilisateur',
        'POST /api/register.php' => 'Inscription utilisateur',
        'POST /api/forgot_password.php' => 'Demande de réinitialisation',
        'POST /api/reset_password.php' => 'Réinitialisation du mot de passe',
        'POST /api/solve_math.php' => 'Résolution de problème mathématique',
        'POST /api/wolfram.php' => 'Résolution avec WolframAlpha',
        'POST /api/llm_explanation.php' => 'Explication avec LLM',
        'POST /api/mathpix.php' => 'Conversion image en LaTeX'
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

