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

// Headers CORS et JSON
header('Content-Type: application/json; charset=utf-8');

// Réponse pour la racine
echo json_encode([
    'success' => true,
    'message' => 'Backend PHP is running!',
    'service' => 'Math Assistant API',
    'version' => '1.0.0',
    'status' => 'online',
    'api_endpoint' => '/api/',
    'documentation' => 'Consultez /api/ pour la liste des endpoints disponibles',
    'endpoints' => [
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
exit;

