# ✅ Configuration Simple Railway - PHP Pur

## 🎯 Solution finale

J'ai créé un **Dockerfile** pour forcer Railway à utiliser Docker au lieu de Nixpacks (qui causait les erreurs).

## 📋 Fichiers essentiels

### ✅ Fichiers nécessaires (à garder) :
1. **`Dockerfile`** - Build Docker (évite Nixpacks)
2. **`Procfile`** - Commande de démarrage (backup)
3. **`composer.json`** - Dépendances PHP
4. **`runtime.txt`** - Version PHP (optionnel avec Dockerfile)
5. **`router.php`** - Router pour les requêtes
6. **`index.php`** - Point d'entrée principal
7. **`api/`** - Dossier avec tous les endpoints

### ❌ Fichiers supprimés :
- `railway.json` - Causait des conflits
- `nixpacks.toml` - Causait l'erreur "undefined variable 'composer'"
- `.railwayignore` - Non nécessaire
- `.start.sh` - Non nécessaire

## 🚀 Déploiement

### 1. Commiter les changements

```bash
git add .
git commit -m "Fix: Use Dockerfile instead of Nixpacks"
git push origin master
```

### 2. Railway utilisera automatiquement Dockerfile

Railway détectera le `Dockerfile` et l'utilisera au lieu de Nixpacks.

### 3. Vérifier les logs

Dans Railway → Deployments → Logs, vous devriez voir :
- ✅ Build Docker réussi
- ✅ `composer install` exécuté
- ✅ `PHP 8.2.x Development Server started`
- ❌ Plus d'erreur Nixpacks

## 🔧 Si Railway utilise encore Nixpacks

Dans Railway Settings :
1. Service → **Settings**
2. Cherchez **"Buildpack"** ou **"Builder"**
3. Forcez **"Dockerfile"** ou **"Docker"**

## ✅ Test local du Dockerfile

Pour tester localement avant de pousser :

```bash
docker build -t backend-test .
docker run -p 8080:8080 -e PORT=8080 backend-test
```

Puis visitez : `http://localhost:8080/`

