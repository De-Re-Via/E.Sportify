<?php
/*
====================================================================================
    Fichier : soon_events.php

    Rôle :
    Ce fichier permet de récupérer la liste des événements à venir dans les prochains jours,
    quelle que soit l'inscription de l'utilisateur. Il sert à afficher un aperçu des événements à venir,
    par exemple sur la page d'accueil ou dans un widget d'actus.

    Fonctionnement :
    - Interroge la base de données pour récupérer tous les événements dont la date est supérieure à la date courante.
    - Trie les événements par date croissante pour mettre en avant les plus proches dans le temps.
    - Retourne la liste des événements au format JSON.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé généralement via AJAX ou lors du chargement dynamique de la page d'accueil.
    - Les événements retournés peuvent être filtrés ou affichés dans différents widgets du site.

====================================================================================
*/

require_once("../config/database.php");

// Préparation de la requête SQL pour récupérer les événements à venir
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
    echo json_encode(["error" => "Erreur : " . $e->getMessage()]);                    // Retour des événements à venir au format JSON
}
?>
