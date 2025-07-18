<?php

/*
====================================================================================
    Fichier : delete_event.php

    Rôle :
    Ce fichier permet la suppression d'un événement par l'auteur de l'événement, un organisateur ou l'administrateur.
    Il reçoit l'identifiant de l'événement à supprimer, vérifie les droits de l'utilisateur connecté,
    puis supprime l'événement de la base de données si l'utilisateur dispose des droits requis.

    Fonctionnement :
    - Reçoit via POST l'identifiant de l'événement à supprimer.
    - Vérifie que l'utilisateur est connecté.
    - Récupère les informations de l'événement pour identifier l'auteur.
    - Vérifie que l'utilisateur connecté est soit l'auteur, soit organisateur, soit administrateur.
    - Exécute la suppression de l'événement dans la base de données.
    - Retourne une réponse JSON indiquant le succès ou l'échec de l'opération.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Est appelé depuis le dashboard ou la gestion d'événements, généralement en AJAX.
    - Fonctionne avec la gestion centralisée des rôles utilisateurs.

====================================================================================
*/

require_once("../config/database.php");
session_start();

// Vérification que l'utilisateur est connecté
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "Méthode non autorisée.";
  exit;
}

// Vérification de la présence de l'identifiant de l'événement à supprimer dans la requête
if (!isset($_POST["id_event"])) {
  echo "ID de l'événement manquant.";
  exit;
}

$id = intval($_POST["id_event"]);

if (!isset($_SESSION["user_id"])) {
  echo "Accès refusé.";
  exit;
}

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"] ?? "visiteur";

// Vérifie que l'appel vient bien du dashboard
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'dashboard') === false) {
  echo "Suppression uniquement autorisée depuis le dashboard.";
  exit;
}

// Vérifie si l'utilisateur est admin OU créateur
$stmt = $pdo->prepare("SELECT id_createur FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
  echo "Événement introuvable.";
  exit;
}

if ($role !== "admin" && $event["id_createur"] != $user_id) {
  echo "Vous n'avez pas les droits pour supprimer cet événement.";
  exit;
}

// Suppression
$stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
$success = $stmt->execute([$id]);

if ($success) {
  echo "Événement supprimé.";
} else {
  echo "Erreur lors de la suppression.";
}
exit;
