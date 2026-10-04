# Mise en ligne de l'IFPM

Le site part sur **ifpm.lamajestueuse.com** à chaque poussée sur `main`. GitHub Actions
installe les dépendances, compile les assets, envoie les fichiers par rsync,
puis joue les migrations et reconstruit les caches sur le serveur.

Composer ne tourne jamais sur le serveur : `vendor/` arrive tout construit.

## Ce qu'il faut régler une seule fois

### 1. Les secrets du dépôt

`Settings → Secrets and variables → Actions → New repository secret`, dans
[ifpm-portail](https://github.com/alphonsemvele/ifpm-portail/settings/secrets/actions) :

| Secret | Obligatoire | Ce que c'est |
|---|---|---|
| `SSH_HOST` | oui | l'hôte SSH de PlanetHoster |
| `SSH_USER` | oui | l'utilisateur SSH du compte |
| `SSH_KEY` | oui | la **clé privée** de déploiement, en entier |
| `APP_PATH` | oui | où vit l'application, **hors** de toute racine web — par exemple `/home/<utilisateur>/apps/ifpm` |
| `RACINE_WEB` | recommandé | la racine du sous-domaine, `/home/<utilisateur>/ifpm` : elle devient un lien vers `public/` |
| `SSH_PORT` | non | si différent de 22 |
| `PHP_BIN` | non | chemin complet du PHP du serveur, si `php` n'est pas le bon |

Ce sont les mêmes valeurs que pour le portail, sauf `APP_PATH` et
`RACINE_WEB`, propres à ce site.

**Pourquoi l'application ne doit pas vivre dans la racine du sous-domaine :**
le panneau fait pointer ifpm.lamajestueuse.com sur `~/ifpm`. Si le code était
déposé là, `https://ifpm.lamajestueuse.com/.env` serait servi au premier venu. On dépose donc
l'application ailleurs, et `~/ifpm` devient un lien vers son dossier
`public/`, le seul qui doit être visible.

### 2. La clé de déploiement

Si vous réutilisez celle du portail, rien à faire côté serveur : la même clé
publique est déjà dans `~/.ssh/authorized_keys`. Sinon :

```bash
ssh-keygen -t ed25519 -C "deploiement-ifpm" -f ~/.ssh/deploiement-ifpm
cat ~/.ssh/deploiement-ifpm.pub   # à ajouter dans authorized_keys du serveur
cat ~/.ssh/deploiement-ifpm       # à coller dans le secret SSH_KEY
```

### 3. La base et le fichier .env

Créez la base depuis le panneau PlanetHoster, puis, sur le serveur :

```bash
mkdir -p ~/apps/ifpm
cp ~/apps/ifpm/deploiement/env-production.exemple ~/apps/ifpm/.env
# compléter DB_DATABASE, DB_USERNAME, DB_PASSWORD, puis :
php ~/apps/ifpm/artisan key:generate
```

Le `.env` reste sur le serveur : le pipeline ne l'envoie ni ne l'écrase.

### 4. Le certificat

La colonne SSL du panneau est à ✗ pour ce sous-domaine : émettez le
certificat avec le bouton **SSL/TLS**. Tant qu'il manque, le site répond en
clair — le pipeline l'accepte le temps de l'émission, mais `APP_URL` doit
alors rester en `http://`.

## Au quotidien

- **Déployer** : pousser sur `main`. L'onglet *Actions* montre le détail.
- **Rejouer un déploiement** : *Actions → Déploiement → Run workflow*.
- **Essai à blanc sur le serveur**, sans migration ni coupure :

```bash
bash ~/apps/ifpm/deploiement/apres-deploiement.sh --essai
```

## Les tests

La suite de tests de ce projet ne passe pas encore (24 échecs sur 25). Le déploiement
n'est donc pas conditionné à elle — il vérifie seulement que l'application
démarre. Dès que la suite sera verte, il faudra la remettre en garde-fou
avant l'envoi, comme sur le portail du groupe.
