<?php
/**
 * Fichier : logout.php
 * Rôle : Déconnecte l'utilisateur
 * - Détruit la session
 * - Redirige vers la page d’accueil (index.html)
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
