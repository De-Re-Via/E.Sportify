# Esportify

Plateforme de gestion d'événements e-sport multi-rôles : inscription, création, validation, classement, modération et discussion communautaire.

---

## Présentation

**Esportify** est une application web permettant d'organiser, gérer et suivre des événements e-sport en ligne.  
Elle propose un parcours utilisateur adapté à chaque profil : visiteur, joueur, organisateur, administrateur.  
Le projet met l’accent sur la sécurité, la modularité du code, la gestion des droits et la simplicité d’utilisation.

---

## Fonctionnalités principales

- Authentification sécurisée (inscription, connexion, gestion des rôles)
- Création d’événements avec workflow de validation
- Filtrage dynamique et recherche avancée des événements
- Inscription/désinscription des joueurs, jauges de places
- Système de classement, attribution automatique des points
- Tableau de scores, affichage des participations et événements créés
- Gestion des rôles : joueur, organisateur, administrateur
- Discussion communautaire (chat), modération des messages
- Galerie dynamique (carrousel), gestion admin (ajout/suppression images)
- Interface responsive (desktop optimisé, mobile/tablette support partiel)
- Documentation complète et arborescence claire

---

## Stack technique

- **Front-end** : HTML5 / CSS3 / Bootstrap / JavaScript (fetch API)
- **Back-end** : PHP (PDO, sessions sécurisées)
- **Base de données** : MariaDB (phpMyAdmin)
- **Hébergement** : AlwaysData (mutualisé)

---

## Déploiement

### 1. En local (XAMPP)

1. Cloner le projet dans `C:/xampp/htdocs/esportify/`
2. Importer la base via phpMyAdmin :
    - Fichier : `/sql/esportify_schema.sql`
3. Lancer XAMPP (services Apache et MySQL)
4. Accéder à `http://localhost/esportify/front/index.html`

### 2. En ligne (AlwaysData)

1. Créer un compte sur https://alwaysdata.com/
2. Créer une base de données MySQL via le panneau AlwaysData
3. Adapter `back/config/database.php` avec les identifiants AlwaysData
4. Uploader `/front` et `/back` dans `/www/` via FTP
5. Importer la base depuis `esportify_schema.sql` (via phpMyAdmin AlwaysData)
6. Accéder à `https://tonsite.alwaysdata.net/index.html`

---

## Structure du projet

/front/ # Pages HTML, JS, CSS, images (public)
/back/ # Contrôleurs PHP, includes, logique applicative
/sql/esportify_schema.sql # Script SQL de création de la base
/assets/ # Images, logos, icônes, etc.
/docs/ # Documentation (PDF, guides)

yaml
Copier
Modifier

---

## Sécurité & bonnes pratiques

- Mots de passe **hashés** (`password_hash`)
- Requêtes SQL **préparées** (PDO)
- Contrôle des **rôles** et des accès sur chaque action
- Sécurisation de la session PHP (SID, droits)
- Validation des entrées côté client et serveur
- Respect du RGPD (données minimales, aucune exploitation commerciale)
- Interface administrateur protégée

---

## Points importants / Recommandations

- **Optimisation** : L’expérience utilisateur est optimale sur desktop (responsive mobile à compléter)
- **Messagerie (chat)** : Stockée en SQL pour cohérence avec le reste du projet (NoSQL possible à terme)
- **Sécurisation & déploiement** : Modules avancés à venir dans la formation (voir documentation)
- **Appui IA** : Développement accompagné par des outils d’aide (ChatGPT), l’architecture et l’intégration restent sous contrôle humain (voir synthèse projet pour transparence)

---

## Axes d’amélioration

- Responsive design à finaliser (dashboard, cards, modales)
- Tests automatisés (PHPUnit, JS)
- Intégration d’un système de notifications temps réel (WebSocket, NoSQL)
- Statistiques avancées pour l’admin
- Refonte UX/UI (framework front moderne conseillé)

---

## Documentation livrable

- `charte-graphique.pdf`      (identité visuelle)
- `manuel-utilisateur.pdf`    (guide pas à pas)
- `gestion-projet.pdf`        (méthodologie, organisation, planning)
- `technique.pdf`             (architecture, sécurité, code)
- `documentation-complete.pdf` (synthèse et bilan global du projet)

---

## Licence

Projet réalisé dans le cadre de l’ECF – Titre professionnel Développeur Web & Web Mobile.  
Usage pédagogique uniquement.  
Crédits images : banques libres de droit ou créations originales.

---