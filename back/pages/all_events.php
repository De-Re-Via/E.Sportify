<?php
// Requêtes dynamiques pour la page d'accueil
if ($_GET['mode'] ?? '' === 'soon') {
  require_once("../config/database.php");
  session_start();

  $user_id = $_SESSION['user_id'] ?? null;

  $sql = "
    SELECT e.*, u.username,
      (SELECT COUNT(*) FROM participants p WHERE p.id_event = e.id) AS inscrits,
      EXISTS(SELECT 1 FROM participants p WHERE p.id_event = e.id AND p.id_user = ?) AS estInscrit
    FROM events e
    JOIN users u ON e.id_createur = u.id
    WHERE e.statut = 'valide' AND e.etat = 'attente'
    ORDER BY e.date_event ASC
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$user_id]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

if ($_GET['mode'] ?? '' === 'next') {
  require_once("../config/database.php");
  session_start();

  $sql = "
    SELECT e.*, u.username,
      (SELECT COUNT(*) FROM participants p WHERE p.id_event = e.id) AS inscrits,
      e.max_players
    FROM events e
    JOIN users u ON e.id_createur = u.id
    WHERE e.statut = 'valide' AND e.etat = 'attente'
    ORDER BY e.date_event ASC, e.heure_event ASC
    LIMIT 1
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();

  $data = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($data) {
    $data["max_players"] = $data["max_players"] ?? 0;
    $data["inscrits"] = $data["inscrits"] ?? 0;
  }

  echo json_encode($data);
  exit;
}

// Code principal
session_start();
require_once("../config/database.php");

$user_id = $_SESSION['user_id'] ?? null;

// Récupération des filtres
$titre = $_POST["titre"] ?? "";
$jeu = $_POST["jeu"] ?? "";
$statut = $_POST["statut"] ?? "";
$date_event = $_POST["date_event"] ?? "";

// Requête de base
$baseSql = "
  SELECT e.*, u.username,
    (SELECT COUNT(*) FROM participants p WHERE p.id_event = e.id) AS inscrits,
    EXISTS(SELECT 1 FROM participants p WHERE p.id_event = e.id AND p.id_user = ?) AS inscrit
  FROM events e
  JOIN users u ON e.id_createur = u.id
  WHERE e.statut != 'refuse'
";

$params = [$user_id];

// Ajout des filtres si fournis
if (!empty($titre)) {
  $baseSql .= " AND e.titre LIKE ?";
  $params[] = "%$titre%";
}
if (!empty($jeu)) {
  $baseSql .= " AND e.jeu LIKE ?";
  $params[] = "%$jeu%";
}
if (!empty($statut)) {
  $baseSql .= " AND e.statut = ?";
  $params[] = $statut;
}
if (!empty($date_event)) {
  $baseSql .= " AND e.date_event = ?";
  $params[] = $date_event;
}

// Événements à venir / en cours
$stmt = $pdo->prepare($baseSql . " AND e.etat != 'termine' ORDER BY e.date_event ASC");
$stmt->execute($params);
$actifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Événements terminés
$stmt = $pdo->prepare($baseSql . " AND e.etat = 'termine' ORDER BY e.date_event DESC");
$stmt->execute($params);
$termines = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction d'affichage d'une carte événement
function afficherCarte($event, $user_id, $readonly = false) {
  $id = $event["id"];
  $titre = htmlspecialchars($event["titre"]);
  $jeu = htmlspecialchars($event["jeu"]);
  $description = htmlspecialchars($event["description"]);
  $date = $event["date_event"];
  $heure = $event["heure_event"];
  $statut = strtoupper($event["statut"]);
  $etat = $event["etat"];
  $etatLabel = $etat === "en_cours" ? "En cours" : ($etat === "attente" ? "En attente" : "Terminé");
  $img = htmlspecialchars($event["image_url"] ?? 'default.jpg');
  $max = (int) $event["max_players"];
  $ajout = $event["created_at"];
  $createur = htmlspecialchars($event["username"]);
  $inscrits = $event["inscrits"];
  $estInscrit = $event["inscrit"];

  echo "<div class='event-card' data-event-id=\"$id\">";
  echo "<img class='event-cover' src='assets/events/$img' alt='Visuel événement'>";
  echo "<h3>$titre</h3>";
  echo "<p><strong>Jeu :</strong> $jeu</p>";
  echo "<p><strong>Date :</strong> $date à $heure</p>";
  echo "<p><strong>Description :</strong> $description</p>";
  echo "<p><strong>Créé par :</strong> $createur</p>";
  echo "<p><strong>Statut :</strong> <span class='badge badge-primary'>$statut</span></p>";
  echo "<p><strong>État :</strong> $etatLabel</p>";
  echo "<p><strong>Ajouté le :</strong> $ajout</p>";
  echo "<p><strong>Joueurs :</strong> $inscrits / $max</p>";

  if (!$readonly && $user_id) {
    if ($etat === "en_cours" && $estInscrit) {
      echo "<a href='/esportify/front/event_live.html?event_id=$id' target='_blank'><button>Rejoindre</button></a>";
    } elseif ($estInscrit) {
      echo "<button class='unregister-btn' data-event-id=\"$id\">Se désinscrire</button>";
    } else {
      echo "<button class='register-btn' data-event-id=\"$id\">S’inscrire</button>";
    }
  }

  echo "</div>";
}

// Affichage des événements actifs
echo "<section class='event-section'>";
echo "<h3>Événements à venir / en cours</h3>";
if (count($actifs) === 0) {
  echo "<p>Aucun événement à venir ou en cours.</p>";
} else {
  foreach ($actifs as $event) {
    afficherCarte($event, $user_id);
  }
}
echo "</section>";

// Affichage des événements passés
echo "<details class='event-section' style='margin-top:2rem;'>";
echo "<summary><h3>Événements passés (" . count($termines) . ")</h3></summary>";
if (count($termines) === 0) {
  echo "<p>Aucun événement passé.</p>";
} else {
  foreach ($termines as $event) {
    afficherCarte($event, $user_id, true);
  }
}
echo "</details>";
