<?php

/*
====================================================================================
    Fichier : create_event.php

    Rôle :
    Ce fichier gère la création d'un nouvel événement par un utilisateur connecté (joueur ou organisateur).
    Il traite la réception des données du formulaire de création d'événement, effectue les contrôles de validité,
    puis enregistre l'événement dans la base de données en attendant validation de l'administrateur.

    Fonctionnement :
    - Reçoit via POST les informations de l'événement à créer (titre, description, date, heure, jeu, nombre de joueurs max, etc.).
    - Vérifie que l'utilisateur est bien connecté et a les droits nécessaires pour créer un événement.
    - Effectue des contrôles sur les données (présence, format).
    - Insère l'événement en base avec le statut "en attente" (validation par l'admin requise).
    - Retourne une réponse JSON de succès ou d'erreur.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Est appelé en AJAX ou par soumission de formulaire depuis la page de création d'événement.
    - Les événements créés sont ensuite visibles par l'administrateur dans le dashboard pour validation.

====================================================================================
*/

session_start();
// Inclusion de la connexion à la base de données
require_once("../config/database.php");

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo "Non autorisé.";
    exit;
}

// Seuls les joueurs connectés (rôle joueur, organisateur ou admin) peuvent créer un événement
if (
    empty($_POST["titre"]) ||
    empty($_POST["description"]) ||
    empty($_POST["date_event"]) ||
    empty($_POST["heure_event"]) ||
    empty($_POST["jeu"]) ||
    empty($_POST["max_players"])
) {
    http_response_code(400);
    echo "Données manquantes.";
    exit;
}

// Récupération et nettoyage des données du formulaire
$titre = $_POST["titre"];
$description = $_POST["description"];
$date_event = $_POST["date_event"];
$heure_event = $_POST["heure_event"];
$jeu = $_POST["jeu"];
$max_players = intval($_POST["max_players"]);
$id_createur = $_SESSION["user_id"];
$created_at = date("Y-m-d H:i:s");
$image_url = null;

if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
    $uploadDir = "../../front/assets/events/";
    $filename = uniqid() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        $image_url = $filename;
    } else {
        http_response_code(500);
        echo "Erreur lors de l'enregistrement de l'image.";
        exit;
    }
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO events (titre, description, date_event, heure_event, jeu, id_createur, created_at, image_url, max_players)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $titre, $description, $date_event, $heure_event,
        $jeu, $id_createur, $created_at, $image_url, $max_players
    ]);

    echo "Événement proposé avec succès.";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
?>
