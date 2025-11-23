# Configuration Frontend - Connexion au Backend Railway

## ✅ Côté Backend (déjà fait)

Le backend Railway a été configuré pour accepter les requêtes depuis :
- `https://mathassistant-app-ia.vercel.app` (production)
- `http://localhost:5173` et `http://localhost:3000` (développement local)

## 🔧 Côté Frontend (à faire)

### 1. Trouver l'URL de votre backend Railway

1. Allez sur [Railway.app](https://railway.app)
2. Ouvrez votre projet `backend_mathassistantIA`
3. Cliquez sur le service `backend_mathassistantIA`
4. Allez dans l'onglet **Settings**
5. Cherchez la section **Networking** ou **Public Domain**
6. Copiez l'URL publique (elle ressemble à : `https://backend-mathassistantia-production.up.railway.app`)

   OU

   - Allez dans l'onglet **Variables**
   - Cherchez `RAILWAY_PUBLIC_DOMAIN` ou similaire

### 2. Configurer l'URL API dans votre frontend

Dans votre projet frontend React/Vite, vous devez configurer l'URL de base de l'API.

#### Option A : Variables d'environnement (recommandé)

Créez/modifiez le fichier `.env.production` à la racine de votre frontend :

```env
VITE_API_URL=https://VOTRE-URL-RAILWAY.app
```

Exemple :
```env
VITE_API_URL=https://backend-mathassistantia-production.up.railway.app
```

Et dans `.env.local` pour le développement :
```env
VITE_API_URL=http://localhost:8080
```

#### Option B : Fichier de configuration

Si vous avez un fichier de configuration (ex: `src/config/api.js` ou `src/config/index.ts`), modifiez-le :

```javascript
const API_URL = import.meta.env.VITE_API_URL || 
  (import.meta.env.PROD 
    ? 'https://VOTRE-URL-RAILWAY.app' 
    : 'http://localhost:8080');

export default API_URL;
```

### 3. Mettre à jour les appels API

Assurez-vous que tous vos appels API utilisent cette URL de base :

```javascript
// Exemple : src/services/api.js
import API_URL from '../config/api';

// Login
export const login = async (email, password) => {
  const response = await fetch(`${API_URL}/api/login.php`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });
  return response.json();
};

// Register
export const register = async (userData) => {
  const response = await fetch(`${API_URL}/api/register.php`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(userData),
  });
  return response.json();
};

// Autres endpoints...
export const solveMath = async (imageFile) => {
  const formData = new FormData();
  formData.append('image', imageFile);
  
  const response = await fetch(`${API_URL}/api/solve_math.php`, {
    method: 'POST',
    body: formData,
  });
  return response.json();
};
```

### 4. Redéployer le frontend sur Vercel

1. Committez vos changements :
```bash
git add .
git commit -m "Configure API URL for Railway backend"
git push
```

2. Vercel redéploiera automatiquement

## 📋 Endpoints disponibles

Une fois configuré, votre frontend peut appeler ces endpoints :

- `POST /api/login.php` - Connexion utilisateur
- `POST /api/register.php` - Inscription utilisateur
- `POST /api/forgot_password.php` - Demande de réinitialisation
- `POST /api/reset_password.php` - Réinitialisation du mot de passe
- `POST /api/wolfram.php` - Résolution avec WolframAlpha
- `POST /api/llm_explanation.php` - Explication avec LLM
- `POST /api/mathpix.php` - Conversion image en LaTeX
- `POST /api/solve_math.php` - Résolution complète (image → LaTeX → solution)

## ✅ Test

Pour tester si la connexion fonctionne :

1. Ouvrez la console du navigateur (F12)
2. Allez sur `https://mathassistant-app-ia.vercel.app`
3. Essayez une action qui fait un appel API
4. Vérifiez dans l'onglet **Network** que les requêtes sont envoyées vers votre URL Railway
5. Vérifiez qu'il n'y a pas d'erreurs CORS dans la console

## 🐛 Dépannage

### Erreur CORS
Si vous voyez une erreur CORS :
- Vérifiez que l'URL du backend dans Railway est correcte
- Vérifiez que le domaine Vercel est bien dans `api/config.php`

### 404 Not Found
- Vérifiez que l'URL est correcte (avec `/api/` avant le nom du fichier)
- Vérifiez que Railway a bien déployé le backend

### Erreur de connexion
- Vérifiez que Railway est en ligne (regardez les logs)
- Vérifiez que le port est correct (Railway le gère automatiquement)

