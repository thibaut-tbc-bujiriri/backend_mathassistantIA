# API Backend - Math Assistant

Ce dossier contient les fichiers PHP pour la communication avec la base de données MySQL.

## Configuration

### Base de données
- **Nom**: `mathassistant_bd`
- **Serveur**: `localhost`
- **Utilisateur**: `root`
- **Mot de passe**: (vide par défaut sur XAMPP)

## Fichiers

- `connexion.php` - Fichier de connexion à la base de données (PDO)
- `config.php` - Configuration générale de l'API avec gestion CORS et fonction d'envoi d'email
- `email_config.php` - Configuration email Gmail SMTP
- `README.md` - Documentation

## Installation

1. Assurez-vous que XAMPP est démarré
2. Créez la base de données `mathassistant_bd` dans phpMyAdmin
3. Exécutez les fichiers SQL : `database.sql` et `password_reset_tokens.sql`
4. Utilisez les fichiers de connexion dans vos scripts PHP

## Utilisation

### Avec connexion.php
```php
<?php
require_once 'connexion.php';
// $pdo est maintenant disponible
?>
```

### Avec config.php
```php
<?php
require_once 'config.php';
$pdo = getDBConnection();
// Utiliser $pdo pour les requêtes
?>
```

## Structure de la base de données

Créez les tables nécessaires dans votre base de données en exécutant les fichiers SQL fournis.

