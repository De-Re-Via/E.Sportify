<?php

/*
====================================================================================
    Fichier : session-check.php

    Rôle :
    Ce fichier vérifie si l'utilisateur est connecté (session active) et effectue une redirection
    automatique vers la page de connexion si ce n'est pas le cas. Il est généralement inclus ou appelé
    au début de toutes les pages restreintes du site afin de sécuriser l'accès.

    Fonctionnement :
    - Démarre la session PHP.
    - Vérifie la présence des variables de session essentielles (user_id, pseudo, role).
    - Si l'utilisateur n'est pas connecté, effectue une redirection HTTP vers la page de connexion (login.html ou équivalent).

    Interactions avec le reste du projet :
    - Inclus dans toutes les pages qui nécessitent d'être authentifié (dashboard, espace membre, back-office...).
    - Permet d'assurer la sécurité des pages et d'éviter l'accès non autorisé aux ressources du site.

====================================================================================
*/

session_start(); // ← OBLIGATOIRE avant toute lecture de session
header("Content-Type: application/json");

$response = [
    "loggedIn" => isset($_SESSION["user_id"]),
    "username" => $_SESSION["username"] ?? null,
    "role" => $_SESSION["role"] ?? null
];

echo json_encode($response);
