# Prompt à donner à votre IA pour lier le Frontend au Backend

---

## 📋 PROMPT COMPLET (Copiez-collez ceci) :

```
Je dois connecter mon frontend React/Vite (déployé sur Vercel : mathassistant-app-ia.vercel.app) 
avec mon backend PHP (déployé sur Railway).

CONTEXTE :
- Frontend : Application React avec Vite, située dans C:\xampp\htdocs\Frontend
- Backend : API PHP déployée sur Railway avec l'URL : https://[VOTRE-URL-RAILWAY].app
- Backend déjà configuré avec CORS pour accepter les requêtes depuis Vercel
- Structure : Les fichiers API sont dans api/ (login.php, register.php, solve_math.php, etc.)

OBJECTIF :
Modifier les fichiers du frontend pour que toutes les requêtes API pointent vers l'URL Railway 
en production, tout en gardant le proxy local pour le développement.

FICHIERS À MODIFIER :
1. src/config.js - Configuration de l'API
2. src/MathSolver.jsx - Appels API pour solve_math.php

CE QUI A DÉJÀ ÉTÉ FAIT :
- Le backend accepte déjà les requêtes CORS depuis mathassistant-app-ia.vercel.app
- Le fichier config.js a été partiellement modifié mais contient encore "https://VOTRE-URL-RAILWAY.app" 
  qui doit être remplacé par la vraie URL Railway

CE QUE JE VEUX :
1. Modifier src/config.js pour :
   - En développement (import.meta.env.DEV) : utiliser '/api' (proxy Vite)
   - En production : utiliser l'URL Railway complète (ex: https://backend-mathassistantia-production.up.railway.app)
   - Les endpoints doivent être accessibles via : {API_BASE_URL}/api/{endpoint}.php
   - Par exemple : https://railway.app/api/login.php, https://railway.app/api/solve_math.php

2. Modifier src/MathSolver.jsx pour :
   - La fonction handleSolve utilise actuellement fetch('/api/solve_math.php', ...)
   - Elle doit utiliser API_BASE_URL pour construire l'URL correcte selon l'environnement
   - Format attendu en production : {API_BASE_URL}/api/solve_math.php

3. Vérifier que src/App.jsx utilise bien apiRequest() de config.js (déjà fait normalement)

IMPORTANT :
- L'URL Railway exacte doit être demandée ou lue depuis les variables d'environnement
- Les endpoints sont dans le dossier api/ donc l'URL complète est : {RAILWAY_URL}/api/{fichier}.php
- En développement local, le proxy Vite redirige /api vers localhost:8080

FICHIERS ACTUELS :

1. src/config.js (lignes importantes) :
```javascript
const isDevelopment = import.meta.env.DEV
const RAILWAY_URL = import.meta.env.VITE_API_URL || 'https://VOTRE-URL-RAILWAY.app'
export const API_BASE_URL = isDevelopment 
  ? '/api'
  : RAILWAY_URL
```

2. src/MathSolver.jsx (ligne ~204) :
```javascript
const response = await fetch('/api/solve_math.php', {
```

INSTRUCTIONS :
1. Demande-moi d'abord l'URL exacte de mon backend Railway, ou vérifie si elle est dans 
   les variables d'environnement (.env.production)
2. Modifie src/config.js pour utiliser cette URL en production
3. Modifie src/MathSolver.jsx pour utiliser API_BASE_URL au lieu de '/api' en dur
4. Assure-toi que tous les appels API utilisent le bon format d'URL selon l'environnement
5. Vérifie que les autres fichiers (App.jsx) utilisent bien apiRequest() de config.js

Le backend a déjà été configuré pour accepter les requêtes CORS depuis le domaine Vercel, 
donc une fois les URLs configurées, tout devrait fonctionner.
```

---

## 🎯 PROMPT COURT (Version condensée) :

```
Je dois connecter mon frontend React/Vite (Vercel) avec mon backend PHP (Railway).

Modifie ces fichiers :
1. src/config.js : Remplacer 'https://VOTRE-URL-RAILWAY.app' par la vraie URL Railway 
   pour que API_BASE_URL pointe vers Railway en production et '/api' en développement.

2. src/MathSolver.jsx : Modifier handleSolve() pour utiliser API_BASE_URL au lieu 
   de '/api/solve_math.php' en dur, afin que ça fonctionne en production sur Railway.

Les endpoints sont : {RAILWAY_URL}/api/{fichier}.php (ex: /api/login.php, /api/solve_math.php)

Demande-moi l'URL Railway si tu ne l'as pas, puis applique les modifications.
```

---

## 📝 PROMPT AVEC DÉTAILS TECHNIQUES :

```
CONTEXTE PROJET :
- Frontend : React + Vite, dossier C:\xampp\htdocs\Frontend, déployé sur Vercel
- Backend : PHP, déployé sur Railway, URL à configurer
- Structure API : fichiers dans api/ (login.php, register.php, solve_math.php, etc.)

PROBLÈME :
Les requêtes API du frontend pointent vers localhost en production. Je dois les 
rediriger vers l'URL Railway.

TÂCHES :
1. Ouvrir et analyser src/config.js
   - Actuellement : API_BASE_URL utilise 'https://VOTRE-URL-RAILWAY.app' en dur
   - À faire : Remplacer par la vraie URL Railway OU utiliser VITE_API_URL depuis .env.production
   - Format URL production : https://[nom-service].up.railway.app
   - Format URL endpoint : {RAILWAY_URL}/api/{endpoint}.php

2. Ouvrir et analyser src/MathSolver.jsx
   - Ligne ~204 : fetch('/api/solve_math.php', ...)
   - À faire : Utiliser API_BASE_URL importé de config.js
   - Construire l'URL : `${API_BASE_URL}/api/solve_math.php` en prod, '/api/solve_math.php' en dev

3. Vérifier src/App.jsx
   - Confirmer que login.php, register.php, etc. utilisent apiRequest() de config.js
   - Si oui, rien à faire. Si non, les modifier.

CONTRAINTES :
- En développement : utiliser le proxy Vite (/api → localhost:8080)
- En production : utiliser l'URL Railway complète
- Détecter l'environnement avec : import.meta.env.DEV (dev) ou import.meta.env.PROD (prod)
- Les endpoints commencent toujours par /api/ puis le nom du fichier .php

OUTPUT ATTENDU :
1. Me demander l'URL Railway exacte (ou me dire comment la trouver)
2. Modifier les fichiers nécessaires
3. Me montrer les changements effectués
4. M'expliquer comment tester (console navigateur, onglet Network)
```

---

## 🚀 PROMPT SIMPLE (Pour une IA basique) :

```
Mon frontend React (déployé sur Vercel) doit communiquer avec mon backend PHP 
(déployé sur Railway).

Dans src/config.js, remplace 'https://VOTRE-URL-RAILWAY.app' par mon URL Railway.

Dans src/MathSolver.jsx, ligne 204, change '/api/solve_math.php' pour utiliser 
API_BASE_URL au lieu de '/api' en dur.

Les endpoints sont : {URL_RAILWAY}/api/{fichier}.php
En dev : '/api/{fichier}.php'
En prod : '{URL_RAILWAY}/api/{fichier}.php'

Modifie ces fichiers maintenant.
```

---

## 💡 CONSEIL :

Choisissez le prompt selon le type d'IA :
- **Prompt Complet** : pour une IA avancée (Claude, GPT-4)
- **Prompt Court** : pour une réponse rapide
- **Prompt avec Détails Techniques** : si vous voulez être très précis
- **Prompt Simple** : pour une IA basique

Copiez-collez le prompt de votre choix dans votre interface IA !


