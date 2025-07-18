<?php

/*
====================================================================================
    Fichier : next_event.php

    Rôle :
    Ce fichier permet de récupérer le prochain événement à venir pour un utilisateur connecté,
    en se basant sur la date des événements auxquels il est inscrit. Il est utilisé pour afficher
    un rappel ou une information rapide sur la page d'accueil ou le dashboard de l'utilisateur.

    Fonctionnement :
    - Démarre la session PHP et vérifie l'authentification de l'utilisateur.
    - Récupère l'identifiant de l'utilisateur depuis la session.
    - Interroge la base de données pour obtenir l'événement à venir le plus proche auquel l'utilisateur est inscrit.
    - Retourne les informations de l'événement au format JSON.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé via AJAX ou lors du chargement du dashboard, page d'accueil, etc.
    - Permet d'afficher un rappel personnalisé à chaque membre connecté.

====================================================================================
*/

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
