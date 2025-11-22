<?php
/**
 * Fichier de connexion à la base de données MySQL
 * Base de données: mathassistant_bd
 * Serveur: localhost (XAMPP)
 */

// Configuration de la connexion
$host = 'localhost';        // Serveur MySQL (par défaut pour XAMPP)
$port = '3307';             // Port MySQL personnalisé (XAMPP)
$dbname = 'mathassistant_bd';  // Nom de la base de données
$username = 'tbc';         // Nom d'utilisateur par défaut XAMPP
$password = 'thi@.32a';             // Mot de passe par défaut XAMPP (vide)

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

