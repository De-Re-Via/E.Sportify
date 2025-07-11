<?php
// delete_event.php – supprime un événement si admin ou créateur

session_start();
require_once("../config/database.php");

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"] ?? "";

// Lecture du JSON envoyé par le frontend
$data = json_decode(file_get_contents("php://input"), true);
$event_id = $data["event_id"] ?? null;

if (!$event_id) {
    echo json_encode(["success" => false, "message" => "ID manquant"]);
    exit;
}

// Récupère l'événement pour connaître son créateur
$stmt = $pdo->prepare("SELECT id_createur FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo json_encode(["success" => false, "message" => "Événement introuvable"]);
    exit;
}

// Vérifie les droits
if ($role !== "admin" && $event["id_createur"] != $user_id) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Accès interdit"]);
    exit;
}

// Supprime l'événement
$stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
$stmt->execute([$event_id]);

echo json_encode(["success" => true]);
?>
