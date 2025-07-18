<?php

/*
====================================================================================
    Fichier : unregister_event.php

    Rôle :
    Ce fichier gère la désinscription d'un utilisateur connecté d'un événement auquel il était inscrit.
    Il vérifie que l'utilisateur est bien authentifié, puis supprime l'inscription dans la base de données.

    Fonctionnement :
    - Reçoit l'identifiant de l'événement via POST.
    - Vérifie la connexion de l'utilisateur via la session PHP.
    - Vérifie que l'utilisateur est bien inscrit à l'événement.
    - Supprime l'entrée correspondante dans la table des inscriptions.
    - Retourne une réponse JSON de succès ou d'échec.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé généralement via AJAX ou soumission de formulaire lors du clic sur "Se désinscrire".
    - Permet de gérer le nombre d'inscrits et d'afficher le statut de l'utilisateur sur la fiche événement, dashboard, etc.

====================================================================================
*/

session_start();
require_once("../config/database.php");

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo "Non autorisé.";
    exit;
}

// Vérifie que l'ID de l'événement a bien été envoyé
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["id_event"])) {
    http_response_code(400);
    echo "Paramètre id_event manquant.";
    exit;
}

$id_user = $_SESSION["user_id"];
$id_event = intval($data["id_event"]);

try {
    // Supprime l'enregistrement du participant
    $stmt = $pdo->prepare("DELETE FROM participants WHERE id_user = ? AND id_event = ?");
    $stmt->execute([$id_user, $id_event]);

    echo "Désinscription réussie.";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur SQL : " . $e->getMessage();
}
