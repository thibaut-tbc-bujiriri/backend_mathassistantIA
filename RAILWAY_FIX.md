# 🔧 Fix Railway - "Could not open input file: artisan"

## Problème
Railway essaie d'exécuter `artisan` (Laravel) alors que c'est un projet PHP pur.

## ✅ Solution 1 : Vérifier les Settings Railway

Dans Railway :
1. Ouvrez votre projet `backend_mathassistantIA`
2. Service PHP → **Settings**
3. Cherchez **"Build Command"** ou **"Start Command"**
4. Si vous voyez `php artisan ...`, **supprimez-le**
5. Laissez Railway utiliser le **Procfile**

## ✅ Solution 2 : Forcer le Procfile

Assurez-vous que Railway utilise bien le Procfile :
1. Service PHP → **Settings**
2. Section **"Deploy"** ou **"Run"**
3. Vérifiez que **"Use Procfile"** est activé
4. Le Procfile doit contenir : `web: php -S 0.0.0.0:$PORT router.php`

## ✅ Solution 3 : Créer un script de démarrage

Si Railway ignore le Procfile, créez `.start.sh` :
```bash
#!/bin/bash
php -S 0.0.0.0:$PORT router.php
```

Puis dans Railway Settings → Start Command : `bash .start.sh`

## ✅ Solution 4 : Vérifier le Buildpack

Railway peut utiliser un buildpack Laravel automatiquement. 

Dans Railway Settings :
1. Cherchez **"Buildpack"** ou **"Environment"**
2. Forcer l'utilisation de **PHP** (pas Laravel)
3. Ou sélectionner **"Custom"** et spécifier PHP

## 🔍 Vérifications

1. ✅ Le fichier `Procfile` existe à la racine
2. ✅ Le Procfile contient : `web: php -S 0.0.0.0:$PORT router.php`
3. ✅ Le fichier `router.php` existe à la racine
4. ✅ Le fichier `index.php` existe à la racine
5. ✅ **Aucun fichier `artisan` n'existe** (confirmé)

## 🚀 Action immédiate

1. **Dans Railway Dashboard :**
   - Service PHP → Settings
   - Cherchez "Start Command" ou "Run Command"
   - **Supprimez tout ce qui contient "artisan"**
   - Laissez vide pour utiliser le Procfile

2. **Redéployez :**
   - Commitez et poussez les changements
   - Railway redéploiera automatiquement

3. **Vérifiez les logs :**
   - Vous devriez voir : `PHP 8.x Development Server started`
   - Pas de message sur "artisan"

