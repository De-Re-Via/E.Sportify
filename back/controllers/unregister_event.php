<?php
session_start();
require_once("../config/database.php");

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo "Non autorisé.";
    exit;
}

// Vérifie que l'ID de l'événement a bien été envoyé
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["id_event"])) {
    http_response_code(400);
    echo "Paramètre id_event manquant.";
    exit;
}

$id_user = $_SESSION["user_id"];
$id_event = intval($data["id_event"]);

try {
    // Supprime l'enregistrement du participant
    $stmt = $pdo->prepare("DELETE FROM participants WHERE id_user = ? AND id_event = ?");
    $stmt->execute([$id_user, $id_event]);

    echo "Désinscription réussie.";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
