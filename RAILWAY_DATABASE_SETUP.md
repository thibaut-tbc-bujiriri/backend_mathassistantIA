# 🗄️ Configuration de la Base de Données sur Railway

## ⚠️ IMPORTANT

Railway **ne crée PAS automatiquement** la base de données MySQL. Vous devez :
1. Créer un service MySQL sur Railway
2. Configurer les variables d'environnement de connexion
3. Exécuter les scripts SQL pour créer les tables

---

## 📋 Étape 1 : Créer un service MySQL sur Railway

### Option A : Créer un nouveau service MySQL

1. Allez sur [railway.app](https://railway.app)
2. Ouvrez votre projet `backend_mathassistantIA`
3. Cliquez sur **"+ New"** ou **"New Service"**
4. Sélectionnez **"Database"** → **"MySQL"**
5. Railway créera automatiquement un service MySQL avec :
   - Host, Port, User, Password
   - Ces informations sont dans les **Variables d'environnement**

### Option B : Utiliser une base de données externe

Si vous avez déjà une base de données MySQL ailleurs (ex: PlanetScale, Supabase, etc.), 
notez les informations de connexion.

---

## 🔧 Étape 2 : Configurer les variables d'environnement

### Dans Railway :

1. Allez dans votre service **backend_mathassistantIA** (pas MySQL, le service PHP)
2. Onglet **Variables**
3. Ajoutez ces variables d'environnement :

```
DB_HOST=<MYSQL_HOST>
DB_PORT=<MYSQL_PORT>
DB_NAME=mathassistant_bd
DB_USER=<MYSQL_USER>
DB_PASS=<MYSQL_PASSWORD>
```

**Où trouver ces valeurs :**

#### Si vous avez créé MySQL sur Railway :

1. Ouvrez le service **MySQL** (pas PHP)
2. Onglet **Variables**
3. Vous verrez :
   - `MYSQLHOST` ou `MYSQL_HOST`
   - `MYSQLPORT` ou `MYSQL_PORT` (généralement 3306)
   - `MYSQLUSER` ou `MYSQL_USER`
   - `MYSQLPASSWORD` ou `MYSQL_PASSWORD`
   - `MYSQLDATABASE` ou `MYSQL_DATABASE`

4. Dans le service **PHP**, ajoutez :
   ```
   DB_HOST=<valeur de MYSQLHOST>
   DB_PORT=<valeur de MYSQLPORT> (généralement 3306)
   DB_NAME=mathassistant_bd
   DB_USER=<valeur de MYSQLUSER>
   DB_PASS=<valeur de MYSQLPASSWORD>
   ```

#### Exemple de configuration Railway :

```
DB_HOST=containers-us-west-123.railway.app
DB_PORT=3306
DB_NAME=mathassistant_bd
DB_USER=root
DB_PASS=xxxxx
```

---

## 🔧 Étape 3 : Mettre à jour api/config.php

Le fichier `api/config.php` doit utiliser les variables d'environnement :

```php
// Configuration de la base de données
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'mathassistant_bd');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
```

---

## 🗄️ Étape 4 : Créer la base de données et les tables

### Méthode 1 : Via Railway CLI (recommandé)

1. Installez Railway CLI si ce n'est pas fait
2. Connectez-vous : `railway login`
3. Sélectionnez votre projet : `railway link`
4. Connectez-vous à MySQL :
   ```bash
   railway connect mysql
   ```
5. Exécutez les scripts SQL (copiez-collez le contenu de `database.sql` et `password_reset_tokens.sql`)

### Méthode 2 : Via un client MySQL externe

1. Utilisez un client comme **MySQL Workbench**, **DBeaver**, ou **phpMyAdmin**
2. Connectez-vous avec les informations de Railway
3. Exécutez les scripts SQL

### Méthode 3 : Via un script PHP (temporaire)

Créez un fichier `setup_database.php` à la racine :

```php
<?php
require_once 'api/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    
    // Lire et exécuter database.sql
    $sql = file_get_contents(__DIR__ . '/api/database.sql');
    $pdo->exec($sql);
    echo "✅ Base de données créée\n";
    
    // Lire et exécuter password_reset_tokens.sql
    $sql2 = file_get_contents(__DIR__ . '/api/password_reset_tokens.sql');
    $pdo->exec($sql2);
    echo "✅ Tables créées\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
```

Puis accédez à : `https://[votre-url-railway].app/setup_database.php`

⚠️ **Supprimez ce fichier après utilisation pour la sécurité !**

---

## 📝 Scripts SQL à exécuter

### 1. database.sql

```sql
-- Création de la base de données mathassistant_bd
CREATE DATABASE IF NOT EXISTS mathassistant_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE mathassistant_bd;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de l'historique des problèmes résolus
CREATE TABLE IF NOT EXISTS history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    problem TEXT NOT NULL,
    solution TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. password_reset_tokens.sql

```sql
USE mathassistant_bd;

-- Table pour les tokens de réinitialisation de mot de passe
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    reset_code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_reset_code (reset_code),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## ✅ Vérification

1. Vérifiez que les variables d'environnement sont bien configurées dans Railway
2. Testez la connexion en accédant à : `https://[votre-url-railway].app`
3. Essayez de vous inscrire (register)
4. Si ça fonctionne, la base de données est configurée !

---

## 🐛 Dépannage

### Erreur : "Access denied for user"
- Vérifiez que `DB_USER` et `DB_PASS` sont corrects
- Vérifiez que l'utilisateur MySQL a les permissions nécessaires

### Erreur : "Unknown database"
- La base de données n'a pas été créée
- Exécutez le script `database.sql` qui contient `CREATE DATABASE`

### Erreur : "Connection refused"
- Vérifiez `DB_HOST` et `DB_PORT`
- Sur Railway, le port est généralement `3306`
- Vérifiez que le service MySQL est démarré sur Railway

### Erreur : "Table doesn't exist"
- Les tables n'ont pas été créées
- Exécutez les scripts SQL (`database.sql` et `password_reset_tokens.sql`)

