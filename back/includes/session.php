<?php
/**
 * Fichier : session.php
 * Rôle : Gère les vérifications de session utilisateur
 * - Vérifie si l’utilisateur est connecté
 * - Permet de savoir quel est son rôle
 * - Redirige si non connecté
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
