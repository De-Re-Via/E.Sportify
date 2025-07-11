<?php
require_once("../config/database.php");

try {
    // Sélectionne le prochain événement validé (date future)
    $stmt = $pdo->prepare("
        SELECT id, titre, date_event, heure_event, jeu, description, image_url
        FROM events
        WHERE statut = 'valide' AND date_event >= CURDATE()
        ORDER BY date_event ASC, heure_event ASC
        LIMIT 1
    ");
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(null);
        exit;
    }

    echo json_encode($event);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
