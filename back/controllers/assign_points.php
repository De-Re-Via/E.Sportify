<?php

/*
====================================================================================
    Fichier : assign_points.php

    Rôle :
    Ce fichier gère l'attribution des points aux joueurs participants à un événement validé.
    Il permet à l'organisateur ou à l'administrateur d'enregistrer le classement des participants
    et de calculer automatiquement le nombre de points attribués selon la position dans le classement.

    Fonctionnement :
    - Reçoit via POST les données : identifiant de l'événement et un tableau associatif
      des participants avec leur classement.
    - Contrôle que l'utilisateur est connecté et possède le droit (organisateur ou admin)
      d'attribuer les points pour l'événement en question.
    - Calcule les points selon la règle définie (ex : 1er = 10 pts, 2e = 8 pts, 3e = 6 pts,
      4e = 4 pts, 5e ou plus = 2 pts, absent/non classé = 0 pt).
    - Insère ou met à jour les scores dans la table appropriée pour chaque joueur participant.

    Interactions avec le reste du projet :
    - Fichier appelé via AJAX ou formulaire par le dashboard organisateur ou admin.
    - Utilise la connexion à la base de données définie dans database.php (inclusion en début de fichier).
    - Les scores attribués sont utilisés dans le dashboard joueur et pour le classement général.

====================================================================================
*/

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
