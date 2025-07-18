<?php
/*
====================================================================================
    Fichier : register.php

    Rôle :
    Ce fichier gère l'inscription de nouveaux utilisateurs. Il traite les données
    du formulaire d'inscription, vérifie leur validité et unicité, crée le compte,
    puis retourne une réponse JSON indiquant le succès ou l'échec.

    Fonctionnement :
    - Reçoit via POST les informations d'inscription (pseudo, email, mot de passe).
    - Vérifie la présence et la validité des données.
    - Vérifie l'unicité du pseudo et de l'email dans la base de données.
    - Hache le mot de passe avant stockage.
    - Insère le nouvel utilisateur dans la table users, rôle "joueur" par défaut.
    - Retourne une réponse JSON de succès ou d'erreur.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé depuis le formulaire d'inscription, généralement en AJAX.
    - La création de compte permet l'accès à toutes les fonctionnalités réservées aux membres.

====================================================================================
*/

// On démarre la session PHP
session_start();

// Connexion à la base de données via PDO
require_once("../config/database.php");

// Vérification que le formulaire est bien en méthode POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Récupération et nettoyage des champs du formulaire
    $username = trim($_POST["username"]);            // Nom d'utilisateur (pseudo)
    $email = trim($_POST["email"]);                  // Email
    $password = $_POST["password"];                  // Mot de passe
    $confirm = $_POST["confirm_password"];           // Confirmation du mot de passe
    $role = "joueur";                                // Rôle par défaut (joueur)

    // Vérification du format de l'adresse e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email invalide.";
        exit;
    }

    // Vérifie que le mot de passe est suffisamment long
    if (strlen($password) < 6) {
        echo "Mot de passe trop court. Minimum 6 caractères.";
        exit;
    }

    // Vérifie que les deux mots de passe saisis sont identiques
    if ($password !== $confirm) {
        echo "Les mots de passe ne correspondent pas.";
        exit;
    }

    // Vérifie si un utilisateur existe déjà avec le même email ou pseudo
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        echo "Un compte avec cet email ou ce pseudo existe déjà.";
        exit;
    }

    // Hash sécurisé du mot de passe (bcrypt par défaut)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion de l'utilisateur dans la base
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $password_hash, $role]);

    // Succès
    echo "Inscription réussie";
}
?>
