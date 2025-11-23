# 📋 Instructions Railway CLI pour créer les tables MySQL

## Étape 1 : Se connecter à Railway

```bash
railway login
```
- Une fenêtre de navigateur s'ouvrira automatiquement
- Connectez-vous avec votre compte Railway
- Une fois connecté, fermez la fenêtre du navigateur

## Étape 2 : Lier au projet

```bash
railway link
```

Si vous avez plusieurs projets :
- Sélectionnez le projet `abundant-unity` ou `backend_mathassistantIA`
- Sélectionnez l'environnement `production`

## Étape 3 : Se connecter à MySQL

```bash
railway connect mysql
```

Cette commande va :
- Ouvrir une connexion MySQL
- Vous connecter directement à la base de données
- Vous pouvez ensuite exécuter des commandes SQL

## Étape 4 : Exécuter le script SQL

Une fois connecté à MySQL via Railway CLI :

1. **Option A** : Copiez-collez le contenu de `SQL_COMPLET.sql` ligne par ligne

2. **Option B** : Utilisez la commande `source` si MySQL CLI le supporte :
   ```sql
   source SQL_COMPLET.sql;
   ```

3. **Option C** : Exécutez le SQL directement ligne par ligne dans le terminal MySQL

## Étape 5 : Vérifier

Dans MySQL, exécutez :
```sql
USE mathassistant_bd;
SHOW TABLES;
```

Vous devriez voir :
- users
- history
- password_reset_tokens

---

## Alternative : Via MySQL en ligne de commande local

Si Railway CLI ne fonctionne pas, utilisez MySQL localement :

```bash
# Se connecter via Railway CLI pour obtenir les credentials
railway connect mysql

# OU utiliser mysql directement avec les credentials de Railway
mysql -h [MYSQLHOST] -P [MYSQLPORT] -u [MYSQLUSER] -p[MYSQLPASSWORD] [MYSQLDATABASE] < SQL_COMPLET.sql
```


