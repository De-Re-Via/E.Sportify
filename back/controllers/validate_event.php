<?php

/*
====================================================================================
    Fichier : validate_event.php

    Rôle :
    Ce fichier permet à l'administrateur de valider un événement en attente de validation.
    Il vérifie l'authentification de l'utilisateur et son rôle, puis met à jour le statut de l'événement en base.

    Fonctionnement :
    - Reçoit l'identifiant de l'événement via POST.
    - Vérifie que l'utilisateur est connecté et possède le rôle "admin".
    - Met à jour le champ statut de l'événement à "valide" dans la base de données.
    - Retourne une réponse JSON de succès ou d'échec.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé généralement via AJAX depuis le dashboard admin.
    - Permet à l'administrateur de valider un événement proposé par un joueur ou un organisateur.

====================================================================================
*/

session_start();
require_once("../config/database.php");

// Vérifie que l'utilisateur est connecté et admin
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Accès refusé.";
    exit;
}

// Vérifie les paramètres GET
if (!isset($_GET["event_id"], $_GET["action"])) {
    http_response_code(400);
    echo "Paramètres manquants.";
    exit;
}

$event_id = intval($_GET["event_id"]);
$action = $_GET["action"];

// Détermine le nouveau statut à appliquer
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
    // Met à jour le statut de l'événement
    $stmt = $pdo->prepare("UPDATE events SET statut = :statut WHERE id = :id");
    $stmt->execute([
        ':statut' => $new_status,
        ':id' => $event_id
    ]);

    // Redirige vers le dashboard
    header("Location: /esportify/front/dashboard.html");
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
?>
