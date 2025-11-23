<?php
/**
 * Fichier de connexion à la base de données MySQL
 * Base de données: mathassistant_bd
 * Utilise les variables d'environnement pour la configuration
 */

// Charger le fichier .env en local (si disponible)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        } catch (Exception $e) {
            // Ignorer silencieusement si le .env ne peut pas être chargé
        }
    }
}

// Fonction helper pour obtenir les variables d'environnement
$getEnvVar = function($key, $default = null) {
    if (isset($_ENV[$key]) && !empty($_ENV[$key])) {
        return $_ENV[$key];
    }
    $value = getenv($key);
    if ($value !== false && !empty($value)) {
        return $value;
    }
    return $default;
};

// Configuration de la connexion - Priorité : Variables d'environnement > .env > valeurs par défaut
$host = $getEnvVar('DB_HOST') ?: $getEnvVar('MYSQLHOST') ?: $getEnvVar('MYSQL_HOST') ?: 'localhost';
$port = $getEnvVar('DB_PORT') ?: $getEnvVar('MYSQLPORT') ?: $getEnvVar('MYSQL_PORT') ?: '3307';
$dbname = $getEnvVar('DB_NAME') ?: $getEnvVar('MYSQLDATABASE') ?: $getEnvVar('MYSQL_DATABASE') ?: 'mathassistant_bd';
$username = $getEnvVar('DB_USER') ?: $getEnvVar('MYSQLUSER') ?: $getEnvVar('MYSQL_USER') ?: 'tbc';
$password = $getEnvVar('DB_PASSWORD') ?: $getEnvVar('DB_PASS') ?: $getEnvVar('MYSQLPASSWORD') ?: $getEnvVar('MYSQL_PASSWORD') ?: 'YOUR_DB_PASSWORD_HERE';

try {
    // Création de la connexion PDO
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Active les exceptions pour les erreurs
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retourne les résultats en tableau associatif
            PDO::ATTR_EMULATE_PREPARES => false // Utilise les prepared statements natifs
        )
    );
    
    // Définir le timezone
    date_default_timezone_set('Europe/Paris');
    
    // Retourner la connexion (pour utilisation dans d'autres fichiers)
    return $pdo;
    
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

