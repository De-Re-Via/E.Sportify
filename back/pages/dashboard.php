<?php
/**
 * dashboard.php
 * Génère dynamiquement le contenu du dashboard selon le rôle
 */

require_once("../includes/session.php");

// Redirige si non connecté
requireLogin();

$role = getUserRole();
$username = $_SESSION["username"] ?? "Utilisateur";

// Contenu HTML renvoyé
switch ($role) {
    case "joueur":
        echo "<h2>Bienvenue $username !</h2><p>Tu es connecté en tant que <strong>joueur</strong>.</p><p>Tu peux t'inscrire aux événements, voir ton historique et suivre tes scores !</p>";
        break;

    case "organisateur":
        echo "<h2>Bienvenue $username !</h2><p>Tu es <strong>organisateur</strong>.</p><p>Tu peux créer, modifier et gérer des événements.</p>";
        break;

    case "admin":
        echo "<h2>Bienvenue $username !</h2><p>Tu es <strong>administrateur</strong>.</p><p>Tu as un accès total au site et aux validations.</p>";
        break;

    default:
        echo "<p>Rôle non reconnu.</p>";
        break;
}
?>
