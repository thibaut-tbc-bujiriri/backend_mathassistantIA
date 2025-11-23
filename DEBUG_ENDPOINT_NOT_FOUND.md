# 🔍 Guide de débogage : "Endpoint non trouvé"

## ✅ Corrections appliquées

J'ai corrigé le `router.php` pour qu'il inclue correctement les fichiers PHP au lieu de retourner `false`. 
Les changements ont été poussés sur Railway.

## 🔍 Vérifications à faire

### 1. Vérifier l'URL Railway dans le frontend

Ouvrez la console du navigateur (F12) sur `https://mathassistant-app-ia.vercel.app` et vérifiez :

1. **Onglet Console** : Regardez les logs `API Request:` 
   - L'URL doit être : `https://[votre-url-railway].app/api/login.php`
   - ❌ Si vous voyez : `https://VOTRE-URL-RAILWAY.app/api/login.php` → L'URL n'a pas été remplacée !

2. **Onglet Network** : 
   - Cliquez sur une requête API (ex: login.php)
   - Vérifiez l'URL complète dans l'onglet "Headers"
   - Vérifiez la réponse dans l'onglet "Response"

### 2. Vérifier que l'URL Railway est correcte

Dans `src/config.js`, vérifiez que cette ligne contient votre vraie URL Railway :

```javascript
const RAILWAY_URL = import.meta.env.VITE_API_URL || 'https://VOTRE-URL-RAILWAY.app'
```

**Remplacez `https://VOTRE-URL-RAILWAY.app` par votre vraie URL Railway !**

Pour trouver votre URL Railway :
1. Allez sur [railway.app](https://railway.app)
2. Ouvrez votre projet `backend_mathassistantIA`
3. Cliquez sur le service
4. Settings → Public Domain
5. Copiez l'URL (ex: `https://backend-mathassistantia-production.up.railway.app`)

### 3. Tester directement l'URL Railway

Ouvrez dans votre navigateur : `https://[votre-url-railway].app`

Vous devriez voir :
```json
{
  "success": true,
  "message": "API Math Assistant - Backend",
  "version": "1.0.0",
  "status": "online",
  "endpoints": { ... }
}
```

Si vous voyez ça, le backend fonctionne ✅

### 4. Tester un endpoint directement

Testez : `https://[votre-url-railway].app/api/login.php`

Vous devriez voir une réponse JSON (même si c'est une erreur de méthode, c'est bon signe).

### 5. Vérifier les logs Railway

1. Allez sur Railway
2. Ouvrez votre service
3. Onglet **Logs**
4. Regardez les requêtes qui arrivent
5. Vérifiez s'il y a des erreurs

## 🐛 Problèmes courants et solutions

### Problème 1 : L'URL contient encore "VOTRE-URL-RAILWAY"

**Solution** : Modifiez `src/config.js` et remplacez par votre vraie URL Railway

### Problème 2 : Les requêtes vont vers localhost

**Solution** : Vérifiez que `import.meta.env.DEV` retourne `false` en production sur Vercel

### Problème 3 : Erreur CORS

**Solution** : Le backend accepte déjà le domaine Vercel. Si vous voyez une erreur CORS, 
vérifiez que l'URL Railway est correcte dans le frontend.

### Problème 4 : 404 sur tous les endpoints

**Solution** : 
1. Vérifiez que Railway a bien redéployé (attendez 1-2 minutes après le push)
2. Vérifiez les logs Railway pour voir les requêtes qui arrivent
3. Testez directement l'URL Railway dans le navigateur

## 📝 Checklist de débogage

- [ ] L'URL Railway dans `config.js` est correcte (pas "VOTRE-URL-RAILWAY")
- [ ] L'URL Railway fonctionne quand on l'ouvre dans le navigateur
- [ ] Les logs de la console montrent les bonnes URLs
- [ ] Les logs Railway montrent que les requêtes arrivent
- [ ] Railway a redéployé après les changements (vérifier les logs)

## 🚀 Test rapide

1. Ouvrez `https://mathassistant-app-ia.vercel.app`
2. Ouvrez la console (F12)
3. Essayez de vous connecter
4. Regardez l'onglet **Network**
5. Cliquez sur la requête `login.php`
6. Vérifiez :
   - **Request URL** : doit être `https://[railway-url]/api/login.php`
   - **Response** : ne doit pas être "Endpoint non trouvé"

Si vous voyez toujours "Endpoint non trouvé", partagez-moi :
- L'URL exacte de la requête (depuis l'onglet Network)
- La réponse complète (depuis l'onglet Response)
- Les logs Railway (si possible)


