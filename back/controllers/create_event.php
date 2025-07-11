<?php
session_start();
require_once("../config/database.php");

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo "Non autorisé.";
    exit;
}

// Vérifie que toutes les données requises sont là
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

// Récupération des données
$titre = $_POST["titre"];
$description = $_POST["description"];
$date_event = $_POST["date_event"];
$heure_event = $_POST["heure_event"];
$jeu = $_POST["jeu"];
$max_players = intval($_POST["max_players"]);
$id_createur = $_SESSION["user_id"];
$created_at = date("Y-m-d H:i:s");

// Gestion de l'image uploadée
$image_url = null;

if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
    $uploadDir = "../../assets/events/";
    $filename = uniqid() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        $image_url = $filename;
    }
}

try {
    // Prépare la requête d'insertion avec le champ max_players
    $stmt = $pdo->prepare("
        INSERT INTO events (titre, description, date_event, heure_event, jeu, id_createur, created_at, image_url, max_players)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $titre,
        $description,
        $date_event,
        $heure_event,
        $jeu,
        $id_createur,
        $created_at,
        $image_url,
        $max_players
    ]);

    echo "Événement proposé avec succès.";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
