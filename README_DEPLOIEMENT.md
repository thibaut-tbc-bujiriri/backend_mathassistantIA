# 🚀 Guide de Déploiement Railway - Backend PHP

## ✅ Configuration finale simplifiée

### Fichiers essentiels :

1. **`Dockerfile`** - Build Docker complet avec toutes les dépendances
2. **`start.sh`** - Script de démarrage qui gère la variable PORT
3. **`router.php`** - Router qui dirige vers `api/`
4. **`index.php`** - Point d'entrée simple
5. **`composer.json`** - Dépendances PHP (phpdotenv)

## 🔧 Comment ça fonctionne

1. **Railway détecte le Dockerfile** et build l'image Docker
2. **Le Dockerfile installe** : git, unzip, extension PHP zip, Composer
3. **Composer installe** les dépendances (phpdotenv)
4. **start.sh démarre** le serveur PHP sur le port défini par Railway
5. **router.php route** les requêtes :
   - `/` → `index.php` (statut)
   - `/api/login.php` → `api/login.php`
   - `/api/*` → fichiers dans `api/`

## 📋 Commandes de déploiement

```bash
git add .
git commit -m "Fix: Final simplified configuration for Railway"
git push origin master
```

## 🔍 Vérification dans Railway

1. **Deployments → Logs** :
   - ✅ Build Docker réussi
   - ✅ `composer install` réussi
   - ✅ `PHP 8.2.x Development Server started`

2. **Test de l'URL** :
   - `https://backendmathassistantia-production.up.railway.app/`
   - Devrait afficher : `{"success":true,"message":"Backend PHP is running!",...}`

## ⚠️ Si le service crash

Vérifiez Railway Settings :
- **Start Command** : doit être VIDE
- **Build Command** : doit être VIDE
- Railway utilisera le Dockerfile ENTRYPOINT

## 🎯 Endpoints disponibles

- `GET /` → Statut du backend
- `GET /api/` → Liste des endpoints
- `POST /api/login.php` → Connexion
- `POST /api/register.php` → Inscription
- `POST /api/solve_math.php` → Résolution math
- etc.

