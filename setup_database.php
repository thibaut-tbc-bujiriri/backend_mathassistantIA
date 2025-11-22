<?php
/**
 * Script temporaire pour créer la base de données et les tables sur Railway
 * ⚠️ SUPPRIMEZ CE FICHIER APRÈS UTILISATION pour la sécurité !
 */

require_once 'api/config.php';

header('Content-Type: application/json');

// Sécurité : désactiver en production après utilisation
// Vous pouvez ajouter une vérification de mot de passe si nécessaire

try {
    // Se connecter sans spécifier la base de données pour créer la DB si elle n'existe pas
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    $results = [];
    
    // Créer la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $results[] = "✅ Base de données '" . DB_NAME . "' créée ou existe déjà";
    
    // Utiliser la base de données
    $pdo->exec("USE " . DB_NAME);
    
    // Lire et exécuter database.sql
    $databaseSql = file_get_contents(__DIR__ . '/api/database.sql');
    
    // Supprimer les lignes CREATE DATABASE et USE (déjà fait)
    $databaseSql = preg_replace('/CREATE DATABASE.*?;/i', '', $databaseSql);
    $databaseSql = preg_replace('/USE\s+\w+\s*;/i', '', $databaseSql);
    
    // Exécuter les requêtes une par une
    $statements = array_filter(
        array_map('trim', explode(';', $databaseSql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            $pdo->exec($statement);
        }
    }
    $results[] = "✅ Tables principales créées (users, history)";
    
    // Lire et exécuter password_reset_tokens.sql
    $passwordResetSql = file_get_contents(__DIR__ . '/api/password_reset_tokens.sql');
    
    // Supprimer USE si présent
    $passwordResetSql = preg_replace('/USE\s+\w+\s*;/i', '', $passwordResetSql);
    
    $statements = array_filter(
        array_map('trim', explode(';', $passwordResetSql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            $pdo->exec($statement);
        }
    }
    $results[] = "✅ Table password_reset_tokens créée";
    
    // Vérifier que les tables existent
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $results[] = "✅ Tables existantes : " . implode(', ', $tables);
    
    echo json_encode([
        'success' => true,
        'message' => 'Base de données configurée avec succès',
        'results' => $results,
        'tables' => $tables
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la configuration de la base de données',
        'error' => $e->getMessage(),
        'db_config' => [
            'host' => DB_HOST,
            'port' => DB_PORT,
            'database' => DB_NAME,
            'user' => DB_USER,
            'password_set' => !empty(DB_PASS)
        ]
    ], JSON_PRETTY_PRINT);
}
?>

