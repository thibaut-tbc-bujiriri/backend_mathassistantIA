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

// Si c'est une requête à la racine, afficher les informations de l'API
if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/api/' || $_SERVER['REQUEST_URI'] === '/api/index.php') {
    sendJSONResponse([
        'success' => true,
        'message' => 'API Math Assistant - Backend',
        'version' => '1.0.0',
        'status' => 'online',
        'endpoints' => $endpoints,
        'documentation' => 'Consultez le README.md pour plus d\'informations'
    ], 200);
}

// Si le fichier demandé n'existe pas, retourner une erreur 404
sendJSONResponse([
    'success' => false,
    'message' => 'Endpoint non trouvé',
    'available_endpoints' => $endpoints
], 404);

