<?php
session_start();
require_once("../config/database.php");

// 🔒 Vérifie que l'utilisateur est connecté et admin
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Accès refusé.";
    exit;
}

// 🔍 Vérifie les paramètres GET
if (!isset($_GET["event_id"], $_GET["action"])) {
    http_response_code(400);
    echo "Paramètres manquants.";
    exit;
}

$event_id = intval($_GET["event_id"]);
$action = $_GET["action"];

// 🎯 Détermine le nouveau statut à appliquer
if ($action === "valide") {
    $new_status = "valide";
} elseif ($action === "refuse") {
    $new_status = "refuse";
} else {
    http_response_code(400);
    echo "Action invalide.";
    exit;
}

try {
    // 🗂️ Met à jour le statut de l'événement
    $stmt = $pdo->prepare("UPDATE events SET statut = :statut WHERE id = :id");
    $stmt->execute([
        ':statut' => $new_status,
        ':id' => $event_id
    ]);

    // 🔁 Redirige vers le dashboard
    header("Location: /esportify/front/dashboard.html");
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
?>
