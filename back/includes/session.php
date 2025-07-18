<?php
/*
====================================================================================
    Fichier : session.php

    Rôle :
    Ce fichier vérifie l'état de la session utilisateur, c'est-à-dire si un utilisateur est
    actuellement connecté, et retourne les informations de session utiles pour l'affichage
    dynamique des interfaces ou la sécurisation de certaines pages.

    Fonctionnement :
    - Démarre la session PHP.
    - Vérifie la présence des variables de session essentielles (user_id, pseudo, role).
    - Retourne un résultat JSON :
        - success = true + informations utilisateur si l'utilisateur est connecté.
        - success = false sinon.

    Interactions avec le reste du projet :
    - Appelé en AJAX ou en PHP natif lors du chargement d'une page ou d'une action sécurisée.
    - Permet de gérer l'affichage conditionnel (boutons, accès à certaines pages, etc.).
    - Utilisé pour vérifier l'accès aux API sensibles, aux dashboards ou aux pages membres.

====================================================================================
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Fonction : utilisateur connecté ?
function isLoggedIn() {
    return isset($_SESSION["user_id"]);
}

// Fonction : récupérer le rôle de l’utilisateur (joueur / organisateur / admin)
function getUserRole() {
    return $_SESSION["role"] ?? null;
}

// Fonction : redirige vers l’accueil si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /front/index.html");
        exit;
    }
}
?>
