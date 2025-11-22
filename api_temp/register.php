<?php
/**
 * Endpoint API pour l'inscription (Sign Up)
 * Méthode: POST
 * URL: http://localhost:8080/Math_AssistantApp/api/register.php
 * 
 * Paramètres JSON attendus:
 * {
 *   "name": "Nom complet",
 *   "email": "email@example.com",
 *   "password": "motdepasse"
 * }
 */

require_once 'config.php';

// Activer l'affichage des erreurs pour le débogage (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ne pas afficher les erreurs dans la réponse JSON
ini_set('log_errors', 1);

// Vérifier que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez POST.',
        'received_method' => $_SERVER['REQUEST_METHOD']
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
    if (empty($input['name']) || empty($input['email']) || empty($input['password'])) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Tous les champs sont requis (name, email, password)'
        ], 400);
    }
    
    $name = trim($input['name']);
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Format d\'email invalide'
        ], 400);
    }
    
    // Valider la longueur du mot de passe
    if (strlen($password) < 6) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Le mot de passe doit contenir au moins 6 caractères'
        ], 400);
    }
    
    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Cet email est déjà utilisé. Veuillez vous connecter.'
        ], 409);
    }
    
    // Hasher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insérer le nouvel utilisateur
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);
    
    // Récupérer les informations de l'utilisateur créé (sans le mot de passe)
    $userId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Réponse de succès
    sendJSONResponse([
        'success' => true,
        'message' => 'Compte créé avec succès!',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'created_at' => $user['created_at']
        ]
    ], 201);
    
} catch (PDOException $e) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Erreur lors de la création du compte',
        'error' => $e->getMessage()
    ], 500);
} catch (Exception $e) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ], 500);
}
?>

