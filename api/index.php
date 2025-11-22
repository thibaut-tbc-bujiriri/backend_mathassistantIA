<?php
/**
 * API Backend - Math Assistant
 * Point d'entrée principal de l'API
 */

require_once 'config.php';

handleCORS();

// Liste des endpoints disponibles
$endpoints = [
    'POST' => [
        '/api/login.php' => 'Authentification - Connexion utilisateur',
        '/api/register.php' => 'Authentification - Inscription utilisateur',
        '/api/forgot_password.php' => 'Authentification - Demande de réinitialisation de mot de passe',
        '/api/reset_password.php' => 'Authentification - Réinitialisation du mot de passe',
        '/api/wolfram.php' => 'Mathématiques - Résolution avec WolframAlpha',
        '/api/llm_explanation.php' => 'Mathématiques - Explication avec LLM (OpenAI/Gemini)',
        '/api/mathpix.php' => 'Vision - Conversion d\'image en LaTeX avec OpenAI Vision',
        '/api/solve_math.php' => 'Mathématiques - Résolution complète (image -> LaTeX -> solution)',
    ]
];

// Vérifier si on est appelé depuis le router ou directement
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Si c'est une requête à la racine ou index.php, afficher les informations de l'API
if ($requestPath === '/' || $requestPath === '/api/' || $requestPath === '/api/index.php' || $requestPath === '/index.php' || empty($requestPath)) {
    sendJSONResponse([
        'success' => true,
        'message' => 'API Math Assistant - Backend',
        'version' => '1.0.0',
        'status' => 'online',
        'endpoints' => $endpoints,
        'documentation' => 'Consultez le README.md pour plus d\'informations'
    ], 200);
}

// Si on arrive ici, c'est que le router n'a pas trouvé le fichier et a servi index.php
// Retourner une erreur 404 avec plus de détails pour le débogage
sendJSONResponse([
    'success' => false,
    'message' => 'Endpoint non trouvé',
    'request_uri' => $requestUri,
    'request_path' => $requestPath,
    'available_endpoints' => $endpoints,
    'debug' => [
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    ]
], 404);

