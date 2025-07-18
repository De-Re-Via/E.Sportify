<?php
/** 
 * // Configuration AlwaysData: 'mysql-derevia.alwaysdata.net', 'derevia_esportify', 'derevia', 'DeReViaStudio974'
 * Fichier : database.php
 * Rôle : Établit la connexion sécurisée à la base de données via PDO.
 * Ce fichier est inclus dans tous les scripts qui ont besoin d'accéder à la base.
 * 

 */

// Configuration XAMP
// $host = 'localhost';        Hôte local
// $dbname = 'esportify';      Nom de la base locale (à adapter si besoin)
// $user = 'root';             Utilisateur par défaut sous XAMPP
// $pass = '';                  Aucun mot de passe sous XAMPP

// Configuration AlwaysData
$host = 'mysql-derevia.alwaysdata.net';     // Hôte AlwaysData
$dbname = 'derevia_esportify';              // Nom de la base AlwaysData
$user = 'derevia';                          // Utilisateur AlwaysData
$pass = 'DeReViaStudio974';                 // Mot de passe AlwaysData 

try {
    // Connexion à MySQL avec encodage utf8mb4
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);

    // Mode d'erreur : les erreurs déclenchent des exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En cas d'échec : arrêt du script et affichage de l'erreur
    die("Erreur de connexion : " . $e->getMessage());
}
?>
