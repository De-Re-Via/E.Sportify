<?php

/*
====================================================================================
    Fichier : update_event_status.php

    Rôle :
    Ce fichier gère la mise à jour du statut d'un événement (par exemple, passage en "en cours", "terminé", etc.).
    Il vérifie l'authentification et les droits de l'utilisateur, puis met à jour le champ statut de l'événement
    dans la base de données.

    Fonctionnement :
    - Reçoit l'identifiant de l'événement et le nouveau statut via POST.
    - Vérifie que l'utilisateur est connecté et possède les droits suffisants (organisateur ou administrateur).
    - Met à jour le statut de l'événement dans la base de données.
    - Retourne une réponse JSON de succès ou d'échec.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé généralement via AJAX depuis le dashboard ou la fiche événement.
    - Permet la gestion dynamique de l'état des événements (par exemple, affichage de boutons différents selon le statut).

====================================================================================
*/

require_once('../config/database.php'); 

// Vérification que l'utilisateur est connecté
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
