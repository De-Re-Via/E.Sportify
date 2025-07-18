<?php
session_start();
require_once('../config/database.php');

// Affichage des erreurs activé (utile en local/dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// On indique que la réponse sera au format JSON
header('Content-Type: application/json');

// Vérifie la présence de l'ID d’événement dans l'URL
if (!isset($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$event_id = intval($_GET['event_id']);

// Requête SQL pour récupérer les données de l'événement
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

// Si aucun événement trouvé → renvoyer une erreur
if (!$event) {
    echo json_encode(['success' => false, 'message' => 'Événement introuvable']);
    exit;
}

// Récupère les infos de session utilisateur
$user_id = $_SESSION["user_id"] ?? null;
$role = $_SESSION["role"] ?? "visiteur";

// Détermine si l'utilisateur connecté est le créateur de l'événement
$est_createur = false;
if ($user_id && $user_id == $event['id_createur']) {
    $est_createur = true;
}

// Réponse JSON à retourner
echo json_encode([
    'success' => true,
    'event' => $event,
    'role' => $role,
    'est_createur' => $est_createur
]);
