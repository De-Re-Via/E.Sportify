<?php

/*
====================================================================================
    Fichier : my_events.php

    Rôle :
    Ce fichier permet à un utilisateur connecté de récupérer la liste de ses propres événements créés.
    Il sert à afficher sur le dashboard ou l'espace personnel tous les événements dont l'utilisateur est l'auteur.

    Fonctionnement :
    - Démarre la session PHP et vérifie l'authentification.
    - Récupère l'identifiant de l'utilisateur depuis la session.
    - Interroge la base de données pour obtenir la liste des événements créés par cet utilisateur.
    - Retourne le résultat au format JSON pour affichage dynamique.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé généralement via AJAX lors du chargement du dashboard ou de la page "mes événements".
    - Les événements retournés peuvent être filtrés ou gérés selon le rôle de l'utilisateur.

====================================================================================
*/

session_start();
require_once("../config/database.php");
require_once("../includes/session.php");

requireLogin(); // Redirige si non connecté

// Récupération de l'identifiant de l'utilisateur
$user_id = $_SESSION["user_id"];

// Préparation de la requête SQL pour récupérer les événements créés par l'utilisateur
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
