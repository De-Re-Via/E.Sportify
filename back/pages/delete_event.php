<?php
/**
 * delete_event.php
 * Rôle : Supprimer un événement (réservé à l'administrateur)
 */

session_start();
require_once("../config/database.php");

// Vérifie que l'utilisateur est admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    echo "Accès refusé.";
    exit;
}

// Vérifie que l’ID est fourni et valide
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $event_id = $_POST["delete_id"];

    // Vérifie que l’événement existe
    $stmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "Événement introuvable.";
        exit;
    }

    // Supprime l’événement
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$event_id]);

    echo "ok";
} else {
    echo "Requête invalide.";
}
?>
