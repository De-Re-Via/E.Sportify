<?php
/**
 * Fichier : database.php
 * Rôle : Établit la connexion sécurisée à la base de données via PDO.
 * Ce fichier est inclus dans tous les scripts qui ont besoin d'accéder à la base.
 */

$host = 'localhost';        // Hôte local (XAMPP)
$dbname = 'esportify';      // Nom de la base de données
$user = 'root';             // Nom d'utilisateur (défaut XAMPP)
$pass = '';                 // Mot de passe (vide par défaut sous XAMPP)

try {
    // Connexion à MariaDB avec encodage utf8mb4 (gère les caractères spéciaux)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);

    // Mode d'erreur : les erreurs déclenchent des exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En cas d'échec : arrêt du script et affichage de l'erreur
    die("Erreur de connexion : " . $e->getMessage());
}
?>
