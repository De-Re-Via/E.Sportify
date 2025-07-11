
# Git Commands – Projet Esportify

Ce fichier regroupe les principales commandes Git utilisées dans le cadre de l'organisation du projet.

## Initialisation du dépôt

```bash
git init
git add .
git commit -m "Initialisation projet Esportify"
```

## Connexion au dépôt distant GitHub

```bash
git remote add origin https://github.com/TON-UTILISATEUR/esportify.git
git branch -M main
git push -u origin main
```

## Création de la branche de développement

```bash
git checkout -b develop
git push -u origin develop
```

## Créer une nouvelle fonctionnalité

Depuis `develop` :

```bash
git checkout develop
git checkout -b feat/nom-fonctionnalite
# ... coder ...
git add .
git commit -m "feat: nom de la fonctionnalité"
```

## Fusion de la fonctionnalité dans develop

```bash
git checkout develop
git merge feat/nom-fonctionnalite
git push
```

## Fusion de `develop` dans `main` pour déploiement

```bash
git checkout main
git merge develop
git push
```

## Mettre à jour la branche develop avec les dernières modifs de main (si nécessaire)

```bash
git checkout develop
git pull origin main
```

## Supprimer une branche locale (si besoin)

```bash
git branch -d feat/ancienne-branche
```

## Voir les branches

```bash
git branch         # en local
git branch -r      # distantes
git branch -a      # toutes
```

##  Vérifier l'URL du dépôt distant

```bash
git remote -v
```

---

Ces commandes sont valables pour tous les cycles de développement du projet Esportify.
