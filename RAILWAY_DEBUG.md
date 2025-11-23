# 🔍 Guide de débogage - Railway "Application failed to respond"

## Problème
Vous voyez l'erreur "Application failed to respond" sur Railway.

## ✅ Solutions

### 1. Vérifier les logs Railway

1. Allez sur [Railway Dashboard](https://railway.app)
2. Ouvrez votre projet `backend_mathassistantIA`
3. Cliquez sur le service PHP
4. Onglet **Deployments** → Cliquez sur le dernier déploiement
5. Onglet **Logs**

**Cherchez :**
- Des erreurs PHP (syntax errors, fatal errors)
- Des messages de démarrage du serveur
- Des erreurs de connexion à la base de données

### 2. Vérifier le Procfile

Le Procfile doit contenir exactement :
```
web: php -S 0.0.0.0:$PORT router.php
```

**Vérifications :**
- ✅ Pas d'espaces avant `web:`
- ✅ Utilise `$PORT` (Railway injecte cette variable)
- ✅ Le fichier `router.php` existe à la racine

### 3. Vérifier que tous les fichiers existent

À la racine du projet, vous devez avoir :
- ✅ `Procfile`
- ✅ `router.php`
- ✅ `index.php`
- ✅ `composer.json`
- ✅ `runtime.txt` (avec `php-8.2`)

### 4. Tester localement

Pour tester si le serveur démarre correctement :

```bash
cd C:\xampp\htdocs\Backend
php -S localhost:8080 router.php
```

Puis ouvrez : `http://localhost:8080/`

**Résultat attendu :** Vous devriez voir `{"success":true,"message":"Backend PHP is running!",...}`

### 5. Vérifier les variables d'environnement Railway

Dans Railway :
1. Service PHP → **Variables**
2. Vérifiez que `PORT` est automatiquement défini par Railway
3. Vérifiez les variables de base de données si nécessaire

### 6. Solution alternative : Procfile simplifié

Si le problème persiste, essayez cette version du Procfile :

```
web: php -S 0.0.0.0:${PORT:-8080} router.php
```

Ou directement avec index.php :

```
web: php -S 0.0.0.0:$PORT -t . index.php
```

## 🔧 Corrections appliquées

1. ✅ Créé `index.php` à la racine
2. ✅ Créé `router.php` pour router les requêtes
3. ✅ Mis à jour `Procfile` pour utiliser `router.php`
4. ✅ Vérifié qu'aucun fichier Laravel n'existe

## 📋 Prochaines étapes

1. **Commitez et poussez les changements :**
   ```bash
   git add .
   git commit -m "Fix: Update Procfile and router for Railway"
   git push
   ```

2. **Attendez le redéploiement automatique** (2-3 minutes)

3. **Vérifiez les logs Railway** pour voir si le serveur démarre

4. **Testez l'URL :** `https://backendmathassistantia-production.up.railway.app/`

## ⚠️ Erreurs courantes

### Erreur : "Port already in use"
- **Cause :** Le port est déjà utilisé
- **Solution :** Railway gère automatiquement le port, ne le définissez pas manuellement

### Erreur : "router.php not found"
- **Cause :** Le fichier router.php n'existe pas ou n'est pas à la racine
- **Solution :** Vérifiez que router.php est bien à la racine du projet

### Erreur : "PHP syntax error"
- **Cause :** Erreur de syntaxe dans index.php ou router.php
- **Solution :** Testez localement avec `php -l index.php` et `php -l router.php`

