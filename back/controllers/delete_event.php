<?php
require_once("../config/database.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "Méthode non autorisée.";
  exit;
}

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
