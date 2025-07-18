<?php
/*
====================================================================================
    Fichier : logout.php

    Rôle :
    Ce fichier gère la déconnexion des utilisateurs. Il détruit la session PHP active,
    supprimant ainsi toutes les informations de connexion, puis retourne un message de confirmation.

    Fonctionnement :
    - Démarre la session PHP pour accéder aux variables de session.
    - Vide toutes les variables de session.
    - Détruit la session côté serveur.
    - Retourne une réponse JSON confirmant la déconnexion.

    Interactions avec le reste du projet :
    - Appelé lors du clic sur "Déconnexion" dans l'interface utilisateur.
    - Permet de garantir que toutes les pages restreintes deviennent inaccessibles après la déconnexion.

====================================================================================
*/

// Démarre la session si elle existe
session_start();

// Vide toutes les variables de session
$_SESSION = [];

// Supprime le cookie de session si existant
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Détruit la session côté serveur
session_destroy();

// Redirige vers la page d'accueil
header("Location: /esportify/front/index.html");
exit;
?>
