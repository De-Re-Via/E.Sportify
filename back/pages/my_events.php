<?php
/**
 * Fichier : my_events.php
 * Rôle : Affiche la liste des événements créés par l’utilisateur connecté.
 * Affiche les événements avec titre, description, date, heure, jeu et statut.
 */

session_start(); // On démarre la session pour accéder à l’ID utilisateur

require_once("../config/database.php"); // Connexion à la base de données via PDO

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    echo "Erreur : non connecté."; // Si non connecté, on retourne une erreur simple
    exit;
}

$user_id = $_SESSION["user_id"]; // On récupère l'ID du créateur connecté

try {
    // Prépare une requête SQL pour récupérer tous les événements créés par l’utilisateur
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id_createur = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si aucun événement trouvé, on affiche un message
    if (!$events) {
        echo "<p>Tu n'as encore proposé aucun événement.</p>";
        exit;
    }

    // Parcours tous les événements récupérés
    foreach ($events as $event) {
        // Sécurise les données pour éviter les injections HTML
        $titre = htmlspecialchars($event["titre"]);
        $desc = htmlspecialchars($event["description"]);
        $date = $event["date_event"];
        $heure = $event["heure_event"];
        $jeu = htmlspecialchars($event["jeu"]);
        $statut = $event["statut"];

        // Affiche chaque événement sous forme de carte HTML
        echo "<div class='event-card'>";
        echo "<h3>$titre</h3>";
        echo "<p><strong>Jeu :</strong> $jeu</p>";
        echo "<p><strong>Date :</strong> $date à $heure</p>";
        echo "<p><strong>Description :</strong> $desc</p>";
        echo "<p><strong>Statut :</strong> <span class='badge $statut'>$statut</span></p>";
        echo "</div>";
    }

} catch (PDOException $e) {
    // En cas d'erreur SQL, on affiche une erreur lisible
    echo "Erreur de lecture : " . $e->getMessage();
}
?>
