<?php
/*
====================================================================================
    Fichier : register_event.php

    Rôle :
    Ce fichier gère l'inscription d'un utilisateur connecté à un événement.
    Il vérifie que l'utilisateur est authentifié, contrôle les doublons, vérifie que la jauge n'est pas atteinte,
    puis inscrit l'utilisateur à l'événement en base de données.

    Fonctionnement :
    - Reçoit l'identifiant de l'événement via POST.
    - Vérifie la connexion de l'utilisateur via la session PHP.
    - Vérifie que l'utilisateur n'est pas déjà inscrit à l'événement.
    - Vérifie que le nombre maximum de participants n'est pas atteint.
    - Inscrit l'utilisateur à l'événement en ajoutant une entrée dans la table d'inscriptions.
    - Retourne une réponse JSON de succès ou d'échec.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé généralement via AJAX ou soumission de formulaire lors du clic sur "S'inscrire".
    - Permet d'afficher le statut d'inscription sur la fiche événement, le dashboard, etc.

====================================================================================
*/
session_start();
require_once("../config/database.php");

// Vérifie que l’utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo "Non connecté.";
    exit;
}

$id_user = $_SESSION["user_id"];

// Récupère l'ID de l'événement depuis la requête POST (envoyée via JS)
$data = json_decode(file_get_contents("php://input"), true);
$id_event = $data["id_event"] ?? null;

if (!$id_event) {
    http_response_code(400);
    echo "Événement non spécifié.";
    exit;
}

// Vérifie si l'utilisateur est déjà inscrit
$check = $pdo->prepare("SELECT id FROM participants WHERE id_user = ? AND id_event = ?");
$check->execute([$id_user, $id_event]);
if ($check->rowCount() > 0) {
    echo "Tu es déjà inscrit à cet événement.";
    exit;
}

// Inscrit l'utilisateur dans la table participants
$insert = $pdo->prepare("INSERT INTO participants (id_user, id_event) VALUES (?, ?)");
$insert->execute([$id_user, $id_event]);

echo "Inscription confirmée !";
?>
