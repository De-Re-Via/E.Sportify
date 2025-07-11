<?php
session_start();
require_once('../config/database.php');

header('Content-Type: application/json');

// Vérifie la présence de l'ID de l’événement
if (!isset($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$event_id = intval($_GET['event_id']);

// Requête SQL
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo json_encode(['success' => false, 'message' => 'Événement introuvable']);
    exit;
}

// Récupère le rôle de l'utilisateur connecté
$role = $_SESSION["role"] ?? "visiteur";

echo json_encode([
    'success' => true,
    'event' => $event,
    'role' => $role
]);
