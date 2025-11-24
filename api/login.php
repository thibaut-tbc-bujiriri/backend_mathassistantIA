<?php
/**
 * Endpoint API pour la connexion (Login)
 * Méthode: POST
 * URL: /api/login.php
 * Production: https://backendmathassistantia-production.up.railway.app/api/login.php
 * 
 * Paramètres JSON attendus:
 * {
 *   "email": "email@example.com",
 *   "password": "motdepasse"
 * }
 */

// Désactiver l'affichage des erreurs pour éviter les warnings dans le JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers CORS
header("Access-Control-Allow-Origin: https://mathassistant-app-ia.vercel.app");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

// Vérifier que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez POST.'
    ], 405);
}

try {
    // Récupérer les données JSON
    $input = getJSONInput();
    
    // Vérifier si le JSON est valide
    if ($input === null || json_last_error() !== JSON_ERROR_NONE) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Données JSON invalides'
        ], 400);
    }
    
    // Valider les données
    if (empty($input['email']) || empty($input['password'])) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Email et mot de passe requis'
        ], 400);
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Format d\'email invalide'
        ], 400);
    }
    
    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    // Chercher l'utilisateur par email
    $stmt = $pdo->prepare("SELECT id, name, email, password, created_at FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Vérifier si l'utilisateur existe
    if (!$user) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
    }
    
    // Vérifier le mot de passe
    if (!password_verify($password, $user['password'])) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
    }
    
    // Connexion réussie - retourner les informations de l'utilisateur (sans le mot de passe)
    sendJSONResponse([
        'success' => true,
        'message' => 'Connexion réussie!',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'created_at' => $user['created_at']
        ]
    ], 200);
    
} catch (PDOException $e) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Erreur lors de la connexion',
        'error' => $e->getMessage()
    ], 500);
} catch (Exception $e) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ], 500);
}
?>

