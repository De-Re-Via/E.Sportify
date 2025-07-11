<?php
require_once("../config/database.php");

// Vérifie que l'id_event est présent dans l'URL
if (!isset($_GET["id_event"])) {
    http_response_code(400);
    echo "id_event manquant.";
    exit;
}

$id_event = intval($_GET["id_event"]);

try {
    // Sélectionne les utilisateurs inscrits à l’événement
    $stmt = $pdo->prepare("
        SELECT p.id_user, u.username
        FROM participants p
        JOIN users u ON p.id_user = u.id
        WHERE p.id_event = ?
    ");
    $stmt->execute([$id_event]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Renvoie les données au format JSON
    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
