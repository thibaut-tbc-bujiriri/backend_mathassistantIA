# 🔍 Débogage du Router - "Endpoint non trouvé"

## Problème

Vous recevez toujours le message "Endpoint non trouvé" même si les fichiers existent.

## Vérifications à faire

### 1. Vérifier les logs Railway

Dans Railway :
1. Ouvrez votre service PHP (`backend_mathassistantIA`)
2. Onglet **Logs**
3. Cherchez les lignes qui commencent par "Router:"
4. Vous devriez voir :
   - `Router: REQUEST_URI = /api/login.php`
   - `Router: Parsed path = /api/login.php`
   - `Router: After removing /api, path = /login.php`
   - `Router: Looking for file at: ...`
   - `Router: File exists? YES/NO`

**Partagez-moi ces logs** pour que je puisse diagnostiquer le problème !

### 2. Tester directement une URL

Dans votre navigateur, essayez d'accéder directement à :
```
https://[votre-url-railway].app/api/login.php
```

**Résultat attendu :**
- Si ça fonctionne : vous verrez une erreur JSON (normal, car il manque les données POST)
- Si ça ne fonctionne pas : vous verrez "Endpoint non trouvé"

### 3. Vérifier l'URL du frontend

Dans la console du navigateur (F12) :
1. Onglet **Network**
2. Cliquez sur une requête API
3. Vérifiez l'URL dans "Request URL"
4. Partagez-moi cette URL

## Solutions possibles

### Solution A : Le router ne trouve pas les fichiers

Si les logs montrent "File exists? NO", le problème est que le router cherche au mauvais endroit.

### Solution B : Le frontend envoie la mauvaise URL

Si l'URL dans Network ne correspond pas à ce que le router attend, corrigez `src/config.js` dans le frontend.

### Solution C : Railway ne sert pas correctement les fichiers

Il faut peut-être ajuster le Procfile pour servir directement depuis `api/`.

---

## Test rapide

1. **Testez directement** : `https://[url-railway].app/api/login.php` dans le navigateur
2. **Regardez les logs Railway** : Que disent les logs "Router:" ?
3. **Vérifiez l'URL frontend** : Quelle URL exacte le frontend envoie-t-il ?

**Partagez-moi ces informations** et je pourrai résoudre le problème rapidement !


