# 🚨 FIX CRASH - Application failed to respond

## Problème
Le service Railway crash immédiatement après le démarrage, affichant "Application failed to respond".

## ✅ Solution appliquée

### 1. Script de démarrage simple
Créé `start-server.sh` qui :
- Gère correctement la variable PORT de Railway
- Démarrer le serveur PHP dans le dossier `api/`
- Affiche des messages de débogage

### 2. Dockerfile simplifié
- Utilise le script de démarrage au lieu d'une commande CMD complexe
- Évite les problèmes d'interpolation de variables

### 3. Configuration finale

**Dockerfile** :
```dockerfile
CMD ["/app/start-server.sh"]
```

**start-server.sh** :
```bash
#!/bin/bash
set -e
PORT=${PORT:-8080}
echo "Starting PHP server on port $PORT..."
php -S 0.0.0.0:$PORT -t api
```

## 🚀 Déploiement

```bash
git add .
git commit -m "Fix: Add start-server.sh to handle PORT correctly"
git push origin master
```

## 🔍 Vérification

Dans Railway → Deployments → Logs, vous devriez voir :
- ✅ `Starting PHP server on port XXXX...`
- ✅ `PHP 8.2.x Development Server (http://0.0.0.0:XXXX) started`
- ✅ Pas de crash

## 📋 Fichiers créés

1. ✅ `start-server.sh` - Script de démarrage
2. ✅ `Dockerfile` mis à jour - Utilise le script

## ⚠️ Si ça crash encore

Vérifiez les logs Railway pour voir :
- Le message d'erreur exact
- Si le port est correctement détecté
- Si le serveur PHP démarre

