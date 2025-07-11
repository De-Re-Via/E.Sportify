<?php
/**
 * Fichier : soon_events.php
 * ➤ Renvoie les 4 événements à venir avec statut "valide"
 * ➤ Utilisé dans la section SOON de la page d’accueil
 */

require_once("../config/database.php");

try {
    $stmt = $pdo->prepare("
        SELECT titre, jeu, date_event, heure_event, image_url
        FROM events
        WHERE statut = 'valide'
        ORDER BY date_event ASC, heure_event ASC
        LIMIT 5
    ");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as &$event) {
        if (empty($event["image_url"])) {
            $event["image_url"] = "default.jpg";
        }
    }

    header("Content-Type: application/json");
    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
}
?>
