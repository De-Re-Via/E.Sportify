<?php
require_once("../config/database.php");

try {
  // Sélectionne le prochain événement à venir, validé, et en état 'attente'
  $stmt = $pdo->prepare("
    SELECT e.*, u.username,
      (SELECT COUNT(*) FROM participants p WHERE p.id_event = e.id) AS inscrits,
      e.max_players
    FROM events e
    JOIN users u ON e.id_createur = u.id
    WHERE e.statut = 'valide'
      AND e.etat = 'attente'
      AND CONCAT(e.date_event, ' ', e.heure_event) >= NOW()
    ORDER BY e.date_event ASC, e.heure_event ASC
    LIMIT 1
  ");

  $stmt->execute();
  $event = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$event) {
    echo json_encode(null);
    exit;
  }

  // Protection contre valeurs nulles
  $event["max_players"] = $event["max_players"] ?? 0;
  $event["inscrits"] = $event["inscrits"] ?? 0;

  echo json_encode($event);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
