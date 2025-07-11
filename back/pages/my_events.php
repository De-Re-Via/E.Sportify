<?php
session_start();
require_once("../config/database.php");
require_once("../includes/session.php");

requireLogin(); // Redirige si non connecté

$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT * FROM events WHERE id_createur = ? ORDER BY date_event DESC");
$stmt->execute([$user_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$events) {
    echo "<p>Aucun événement proposé.</p>";
    exit;
}

foreach ($events as $event) {
    $id = $event["id"];
    $titre = htmlspecialchars($event["titre"]);
    $desc = htmlspecialchars($event["description"]);
    $jeu = htmlspecialchars($event["jeu"]);
    $date = $event["date_event"];
    $heure = $event["heure_event"];
    $statut = $event["statut"];
    $image = htmlspecialchars($event["image_url"] ?? 'default.jpg');

    echo "<div class='event-card'>";
    echo "<img src='/esportify/front/assets/events/$image' alt='Image de l’événement' class='event-cover' />";
    echo "<h3>$titre</h3>";
    echo "<p><strong>Jeu :</strong> $jeu</p>";
    echo "<p><strong>Date :</strong> $date à $heure</p>";
    echo "<p><strong>Description :</strong> $desc</p>";
    echo "<p><strong>Statut :</strong> $statut</p>";
    echo "</div>";
}
?>
