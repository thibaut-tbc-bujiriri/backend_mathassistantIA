# 🚀 Commandes Railway CLI - Guide Rapide

## 📋 Commandes à exécuter dans l'ordre :

### 1. Se connecter à Railway
```bash
railway login
```
- Appuyez sur **Y** quand demandé
- Une fenêtre de navigateur s'ouvrira
- Connectez-vous avec votre compte Railway
- Une fois connecté, la fenêtre se fermera automatiquement

### 2. Lier au projet
```bash
railway link
```
- Sélectionnez votre projet (ex: `abundant-unity` ou `backend_mathassistantIA`)
- Sélectionnez l'environnement `production`

### 3. Se connecter à MySQL
```bash
railway connect mysql
```
- Cette commande va vous connecter directement à MySQL
- Vous verrez un prompt MySQL : `mysql>`

### 4. Exécuter le script SQL

Une fois dans MySQL (`mysql>`), copiez-collez ceci :

```sql
CREATE DATABASE IF NOT EXISTS mathassistant_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mathassistant_bd;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    reset_code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SHOW TABLES;
```

### 5. Vérifier

Après avoir exécuté le SQL, vous devriez voir :
```
+-------------------------------+
| Tables_in_mathassistant_bd    |
+-------------------------------+
| history                       |
| password_reset_tokens         |
| users                         |
+-------------------------------+
```

### 6. Quitter MySQL

```sql
exit;
```

---

## ⚡ Méthode Alternative Plus Rapide

Au lieu d'utiliser Railway CLI, vous pouvez :

1. **Configurer les variables d'environnement** dans Railway (DB_HOST, DB_PORT, etc.)
2. **Attendre 1-2 minutes** que Railway redéploie
3. **Ouvrir dans votre navigateur** : `https://[votre-url-railway].app/setup_database.php`
4. Les tables seront créées automatiquement !

---

## 🐛 Dépannage

### Si `railway connect mysql` ne fonctionne pas :

Utilisez plutôt un client MySQL externe (MySQL Workbench, DBeaver, TablePlus) :
1. Récupérez les credentials depuis Railway → MySQL → Variables
2. Connectez-vous avec ces credentials
3. Exécutez le script SQL

### Si vous avez une erreur de connexion :

Vérifiez que :
- Le service MySQL est démarré sur Railway
- Les variables d'environnement sont correctement configurées
- Le port est 3306 (par défaut sur Railway)


