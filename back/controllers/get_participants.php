<?php

/*
====================================================================================
    Fichier : get_participants.php

    Rôle :
    Ce fichier permet de récupérer la liste des participants inscrits à un événement donné,
    à partir de l'identifiant de l'événement. Il fournit leurs informations pour affichage
    dans la fiche événement, le dashboard, ou toute interface de gestion.

    Fonctionnement :
    - Reçoit l'identifiant de l'événement via la méthode GET (paramètre 'event_id').
    - Interroge la base de données pour obtenir la liste des utilisateurs inscrits à cet événement,
      avec les informations pertinentes (pseudo, score, classement, etc.).
    - Retourne la liste des participants au format JSON.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Généralement appelé via AJAX lors du chargement de la page d'un événement ou d'un tableau de bord.
    - Les informations retournées peuvent être exploitées pour afficher la liste, gérer les présences,
      attribuer des points, ou afficher le classement.

====================================================================================
*/

require_once("../config/database.php");

// Vérifie que l'id_event est présent dans l'URL
if (!isset($_GET["id_event"])) {
    http_response_code(400);
    echo "id_event manquant.";
    exit;
}

$id_event = intval($_GET["id_event"]);

try {
    // Sélectionne les utilisateurs inscrits à l’événement
    $stmt = $pdo->prepare("
        SELECT p.id_user, u.username
        FROM participants p
        JOIN users u ON p.id_user = u.id
        WHERE p.id_event = ?
    ");
    $stmt->execute([$id_event]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Renvoie les données au format JSON
    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
