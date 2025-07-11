<?php
require_once('../config/database.php'); // Ce fichier doit définir $pdo

if (!isset($_POST['event_id'], $_POST['new_state'])) {
    die("Paramètres manquants.");
}

$eventId = intval($_POST['event_id']);
$newState = $_POST['new_state'];
$allowedStates = ['attente', 'en_cours', 'termine'];

if (!in_array($newState, $allowedStates)) {
    die("État invalide.");
}

try {
    // Connexion déjà faite via $pdo
    $stmt = $pdo->prepare("UPDATE events SET etat = :etat WHERE id = :id");
    $stmt->execute([
        ':etat' => $newState,
        ':id' => $eventId
    ]);

    // Redirection vers le dashboard
    header("Location: /esportify/front/dashboard.html");
    exit();
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
