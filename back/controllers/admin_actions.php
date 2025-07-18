<?php

/*
====================================================================================
    Fichier : admin_actions.php

    Rôle :
    Ce fichier permet la gestion des actions réservées à l'administrateur du site Esportify.
    Il centralise les traitements côté serveur qui concernent la validation, la modification
    ou la suppression des événements et la gestion des utilisateurs par l'administrateur.

    Fonctionnement :
    - Le fichier reçoit des requêtes (POST ou GET) selon l'action demandée via le dashboard admin.
    - Contrôle si l'utilisateur est bien connecté et a le rôle administrateur.
    - Selon l'action (valider, refuser, supprimer, modifier un événement ou un utilisateur), exécute
      la requête SQL correspondante sur la base de données via PDO.
    - Retourne une réponse au format JSON pour indiquer le succès ou l'échec de l'action.

    Interactions avec le reste du projet :
    - Ce fichier est appelé en AJAX depuis le dashboard d'administration pour effectuer les actions
      sans recharger la page.
    - Utilise la connexion à la base de données définie dans database.php (incluse au début).
    - Nécessite que la session utilisateur soit active et que l'utilisateur soit identifié comme administrateur.
====================================================================================
*/

session_start();
require_once('../config/database.php'); //  bon chemin vers la BDD

//  Active les erreurs (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- PARTIE API pour check_admin ---
if (isset($_GET['action']) && $_GET['action'] === 'check_admin') {
    header('Content-Type: application/json');
    $isAdmin = false;

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT role FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $isAdmin = ($user && $user['role'] === 'admin');
    }

    echo json_encode(['isAdmin' => $isAdmin]);
    exit; // Fin de la requête ici pour check_admin
}

// --- PARTIE traitement de fin d'événement (code original) ---

//  Vérifie que l'utilisateur est connecté et qu’un ID d’événement est fourni
if (!isset($_SESSION['user_id']) || !isset($_GET['event_id'])) {
    header('Location: ../../index.html'); // Redirige vers accueil si non connecté
    exit;
}

$user_id = $_SESSION['user_id'];
$event_id = intval($_GET['event_id']);

//  Récupère le rôle de l'utilisateur
$sql = "SELECT role FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}
$role = $user['role'];

//  Récupère l’ID du créateur de l’événement
$sql = "SELECT id_createur FROM events WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Événement introuvable.";
    exit;
}

//  Vérifie si l'utilisateur est autorisé à modifier cet événement
$is_owner = ($event['id_createur'] == $user_id);
$is_admin = ($role === 'admin');

if (!$is_owner && !$is_admin) {
    echo "Accès refusé. Tu n'es ni admin, ni le créateur de cet événement.";
    exit;
}

//  Mise à jour : on marque l’événement comme terminé
$sql = "UPDATE events SET etat = 'termine' WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id]);

//  Redirection vers le dashboard une fois l’événement terminé
header("Location: ../../front/dashboard.html");
exit;
?>
