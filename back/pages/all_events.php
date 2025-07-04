<?php
session_start();
require_once("../config/database.php");

$isAdmin = (isset($_SESSION["role"]) && $_SESSION["role"] === "admin");

// Traitement des filtres éventuels (via POST)
$where = [];
$params = [];

if (!empty($_POST["titre"])) {
    $where[] = "e.titre LIKE ?";
    $params[] = "%" . $_POST["titre"] . "%";
}

if (!empty($_POST["jeu"])) {
    $where[] = "e.jeu LIKE ?";
    $params[] = "%" . $_POST["jeu"] . "%";
}

if (!empty($_POST["statut"])) {
    $where[] = "e.statut = ?";
    $params[] = $_POST["statut"];
}

if (!empty($_POST["date_event"])) {
    $where[] = "e.date_event = ?";
    $params[] = $_POST["date_event"];
}

$filterSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Requête globale
$sql = "SELECT e.*, u.username FROM events e
        JOIN users u ON e.id_createur = u.id
        $filterSql
        ORDER BY e.date_event DESC, e.heure_event DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$events) {
    echo "<p>Aucun événement trouvé.</p>";
    exit;
}

// Récupère l'heure actuelle
$now = new DateTime();

// On trie dans 3 tableaux
$enCours = [];
$aVenir = [];
$passes = [];

foreach ($events as $event) {
    $eventDateTime = new DateTime($event["date_event"] . " " . $event["heure_event"]);

    if ($eventDateTime->format("Y-m-d") === $now->format("Y-m-d") && $eventDateTime >= $now) {
        $enCours[] = $event;
    } elseif ($eventDateTime > $now) {
        $aVenir[] = $event;
    } else {
        $passes[] = $event;
    }
}

// Fonction pour afficher un bloc d’événements
function afficherBloc($titre, $liste, $isAdmin) {
    if (!$liste) return;

    echo "<h2>$titre</h2>";
    foreach ($liste as $event) {
        $id = $event["id"];
        $titre = htmlspecialchars($event["titre"]);
        $desc = htmlspecialchars($event["description"]);
        $jeu = htmlspecialchars($event["jeu"]);
        $date = $event["date_event"];
        $heure = $event["heure_event"];
        $statut = $event["statut"];
        $auteur = htmlspecialchars($event["username"]);
        $created = $event["created_at"];

        echo "<div class='event-card'>";
        echo "<h3>$titre</h3>";
        echo "<p><strong>Jeu :</strong> $jeu</p>";
        echo "<p><strong>Date :</strong> $date à $heure</p>";
        echo "<p><strong>Description :</strong> $desc</p>";
        echo "<p><strong>Créé par :</strong> $auteur</p>";
        echo "<p><strong>Statut :</strong> <span class='badge $statut'>$statut</span></p>";
        echo "<p><strong>Ajouté le :</strong> $created</p>";
        if ($isAdmin) {
            echo "<button onclick='supprimerEvent($id)'>Supprimer</button>";
        }
        echo "</div>";
    }
}

// Affichage ordonné
afficherBloc("🔵 Événement(s) en cours", $enCours, $isAdmin);
afficherBloc("🟢 Événement(s) à venir", $aVenir, $isAdmin);
afficherBloc("🔴 Événement(s) passés", $passes, $isAdmin);
?>
