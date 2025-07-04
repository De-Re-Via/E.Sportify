<?php
/**
 * Fichier : register.php
 * Rôle : Gère l’inscription d’un nouvel utilisateur.
 * Sécurise les données, vérifie les doublons, compare les mots de passe,
 * hash le mot de passe et insère le nouvel utilisateur dans la base.
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
