# Esportify

Plateforme de gestion d'événements e-sport avec rôles utilisateurs, système de participation, modération et classement.

## Fonctionnalités

- Authentification sécurisée (inscription / connexion)
- Création d'événements par les joueurs
- Validation / refus par l'administrateur
- Classement et points des participants
- Filtres dynamiques sur les événements
- Tableau des scores
- Gestion des actualités et galerie (à venir)

## Stack utilisée

- HTML / CSS / Bootstrap
- JavaScript (fetch)
- PHP (PDO, sessions)
- MariaDB (via phpMyAdmin)
- Hébergement : AlwaysData

## Déploiement

### 1. En local (XAMPP)

1. Placer le projet dans `C:/xampp/htdocs/esportify/`
2. Importer la base via phpMyAdmin :
   - Fichier : `/sql/esportify_schema.sql`
3. Lancer XAMPP (Apache + MySQL)
4. Accéder à `http://localhost/esportify/front/index.html`

### 2. En ligne (AlwaysData)

1. Créer un compte gratuit sur https://alwaysdata.com
2. Créer une base de données MySQL
3. Modifier `back/config/database.php` avec les identifiants fournis
4. Envoyer les fichiers `/front` et `/back` via FTP (dans `/www/`)
5. Importer la base depuis `esportify_schema.sql`
6. Accéder à : `https://tonsite.alwaysdata.net/index.html`

## Structure du projet

/front → HTML/CSS/JS (pages publiques)
/back → PHP (controllers, sessions, pages dynamiques)
/sql/esportify_schema.sql → Structure de la base + compte admin


## Compte test (admin)

- Email : `admin@esportify.test`
- Mot de passe : `admin123`

##  Documents livrables

- `charte-graphique.pdf`
- `manuel-utilisateur.pdf`
- `gestion-projet.pdf`
- `technique.pdf`
