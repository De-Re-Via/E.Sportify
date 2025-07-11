<?php
session_start();
require_once("../config/database.php");

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo "Non autorisé.";
    exit;
}

// Récupère les données envoyées en JSON
$data = json_decode(file_get_contents("php://input"), true);

// Vérifie que les données attendues sont bien présentes
if (
    !isset($data["id_event"]) ||
    !isset($data["players"]) ||
    !is_array($data["players"])
) {
    http_response_code(400);
    echo "Données incomplètes.";
    exit;
}

$id_event = intval($data["id_event"]);
$joueurs = $data["players"];

try {
    // Prépare la requête pour mettre à jour les points et le classement
    $stmt = $pdo->prepare("UPDATE participants SET points = ?, classement = ? WHERE id_user = ? AND id_event = ?");

    foreach ($joueurs as $joueur) {
        $id_user = intval($joueur["id"]);
        $points = intval($joueur["points"]);
        $classement = intval($joueur["classement"]);

        $stmt->execute([$points, $classement, $id_user, $id_event]);
    }

    echo "Attribution des points réussie.";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
