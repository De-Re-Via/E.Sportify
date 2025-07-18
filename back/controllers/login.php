<?php
/*
====================================================================================
    Fichier : login.php

    Rôle :
    Ce fichier gère l'authentification des utilisateurs lors de la connexion.
    Il vérifie les identifiants fournis (pseudo/email et mot de passe), initialise la session,
    et retourne un résultat au format JSON.

    Fonctionnement :
    - Reçoit via POST les identifiants de connexion (pseudo/email et mot de passe).
    - Vérifie la présence des données obligatoires.
    - Recherche l'utilisateur correspondant dans la base de données.
    - Vérifie la correspondance du mot de passe (haché).
    - En cas de succès, initialise la session (id, pseudo, rôle, etc.).
    - Retourne une réponse JSON indiquant le succès ou l'échec de l'authentification.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé lors de la connexion via formulaire, souvent en AJAX.
    - La session ainsi créée permet de sécuriser toutes les pages restreintes et d'attribuer les droits selon le rôle.

====================================================================================
*/


session_start();
require_once("../config/database.php"); // Connexion à la base

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Récupération des infos utilisateur
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // === DEBUG TEMPORAIRE ===
    if (!$user) {
        echo "Utilisateur introuvable";
        exit;
    }

    if (!password_verify($password, $user["password_hash"])) {
        echo "Mot de passe incorrect";
        exit;
    }
    // === FIN DEBUG ===

    // Création de la session
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"] = $user["role"];

    echo "Connexion réussie";
}
?>
