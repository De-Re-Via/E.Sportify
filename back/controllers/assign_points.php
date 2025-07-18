<?php
session_start();
require_once("../config/database.php");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo "Non autorisé.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Méthode non autorisée.";
    exit;
}

$id_event = $_POST["id_event"] ?? null;
if (!$id_event || !isset($_POST["id"])) {
    http_response_code(400);
    echo "Paramètres manquants.";
    exit;
}

// Pour debug : enregistrer les données reçues
file_put_contents(__DIR__ . "/debug_points.log", print_r($_POST, true));

try {
    $update = $pdo->prepare("UPDATE participants SET points = ?, classement = ? WHERE id_user = ? AND id_event = ?");
    $check = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE id_user = ? AND id_event = ?");

    foreach ($_POST["id"] as $id_user) {
        $classement = $_POST["classement_$id_user"] ?? 0;
        $points = $_POST["points_$id_user"] ?? 0;

        $check->execute([$id_user, $id_event]);
        if ($check->fetchColumn() > 0) {
            $update->execute([$points, $classement, $id_user, $id_event]);
        }
    }

    echo "Points attribués avec succès.";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
