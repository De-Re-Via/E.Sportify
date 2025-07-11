<?php
/**
 * Fichier : register_event.php
 * ➤ Rôle : permet à un utilisateur connecté de s’inscrire à un événement
 */

session_start();
require_once("../config/database.php");

// ✅ Vérifie que l’utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo "Non connecté.";
    exit;
}

$id_user = $_SESSION["user_id"];

// 📥 Récupère l'ID de l'événement depuis la requête POST (envoyée via JS)
$data = json_decode(file_get_contents("php://input"), true);
$id_event = $data["id_event"] ?? null;

if (!$id_event) {
    http_response_code(400);
    echo "Événement non spécifié.";
    exit;
}

// 🔍 Vérifie si l'utilisateur est déjà inscrit
$check = $pdo->prepare("SELECT id FROM participants WHERE id_user = ? AND id_event = ?");
$check->execute([$id_user, $id_event]);
if ($check->rowCount() > 0) {
    echo "Tu es déjà inscrit à cet événement.";
    exit;
}

// ✅ Inscrit l'utilisateur dans la table participants
$insert = $pdo->prepare("INSERT INTO participants (id_user, id_event) VALUES (?, ?)");
$insert->execute([$id_user, $id_event]);

echo "Inscription confirmée !";
?>
