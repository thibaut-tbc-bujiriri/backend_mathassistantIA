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
- `config.php` - Configuration générale de l'API avec gestion CORS
- `README.md` - Documentation

## Installation

1. Assurez-vous que XAMPP est démarré
2. Créez la base de données `mathassistant_bd` dans phpMyAdmin
3. Utilisez les fichiers de connexion dans vos scripts PHP

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

Créez les tables nécessaires dans votre base de données. Exemple pour une table `users`:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

