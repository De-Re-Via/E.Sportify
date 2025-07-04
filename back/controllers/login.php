<?php
/**
 * Fichier : login.php
 * Rôle : Gère la connexion utilisateur
 * - Vérifie l’email
 * - Vérifie le mot de passe
 * - Crée la session avec rôle
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
