<?php
/**
 * create_event.php
 * Rôle : enregistre un nouvel événement avec statut "en_attente"
 */

session_start();
require_once("../config/database.php");

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    echo "Erreur : non connecté.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupère les données
    $titre = trim($_POST["titre"]);
    $description = trim($_POST["description"]);
    $date_event = $_POST["date_event"];
    $heure_event = $_POST["heure_event"];
    $jeu = trim($_POST["jeu"]);
    $id_createur = $_SESSION["user_id"];
    $statut = "en_attente";

    // Vérification minimale
    if (empty($titre) || empty($description) || empty($date_event) || empty($heure_event) || empty($jeu)) {
        echo "Tous les champs sont requis.";
        exit;
    }

    // Insertion dans la BDD
    try {
        $stmt = $pdo->prepare("INSERT INTO events (titre, description, date_event, heure_event, jeu, statut, id_createur) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titre, $description, $date_event, $heure_event, $jeu, $statut, $id_createur]);

        echo "succès";
    } catch (PDOException $e) {
        echo "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
?>
