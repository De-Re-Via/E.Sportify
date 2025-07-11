<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=esportify;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Gestion de la mise à jour d'état si "Démarrer" ou "Terminer" est cliqué
if (isset($_GET['changer_etat'], $_GET['event_id'])) {
    $etat = $_GET['changer_etat'];
    $event_id = intval($_GET['event_id']);

    // Mise à jour en base de données de l'état de l'événement
    $stmt = $pdo->prepare("UPDATE events SET etat = ? WHERE id = ?");
    $stmt->execute([$etat, $event_id]);

    // Si on a démarré un événement, ouvrir la page de l'événement dans un nouvel onglet
    if ($etat === 'en_cours') {
        echo "<script>
          window.open('/esportify/front/event_live.html?event_id=$event_id', '_blank');
          window.location.href = '/esportify/front/dashboard.html';
        </script>";
        exit;
    }

    // Pour tout autre changement (ex : terminer), retour sur dashboard
    header("Location: /esportify/front/dashboard.html");
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo "<p>Connexion requise.</p>";
    exit;
}

$user_id = $_SESSION["user_id"];
$username = htmlspecialchars($_SESSION["username"]);
$role = $_SESSION["role"] ?? "visiteur";

// ─────────────────────────────────────────────
// Affichage des événements organisés par l'utilisateur (tous rôles)
// ─────────────────────────────────────────────
echo "<section class='dashboard-section'>";
echo "<h2>Bienvenue <strong>$username</strong></h2>";
echo "<h3>Mes événements organisés</h3>";

$stmt = $pdo->prepare("SELECT * FROM events WHERE id_createur = ? ORDER BY date_event DESC");
$stmt->execute([$user_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$events) {
    echo "<p>Aucun événement.</p>";
} else {
    foreach ($events as $event) {
        afficherCarteEvenement($event, true); // true = créateur
    }
}
echo "</section>";

// ─────────────────────────────────────────────
// Admin uniquement : tous les événements validés d’autres organisateurs
// ─────────────────────────────────────────────
if ($role === 'admin') {
    echo "<section class='dashboard-section'>";
    echo "<h3>Événements validés des autres organisateurs</h3>";

    $stmt = $pdo->prepare("SELECT * FROM events WHERE statut = 'valide' AND id_createur != ? ORDER BY date_event DESC");
    $stmt->execute([$user_id]);
    $others = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$others) {
        echo "<p>Aucun événement d'autres organisateurs validé.</p>";
    } else {
        foreach ($others as $event) {
            afficherCarteEvenement($event, false); // false = pas créateur
        }
    }
    echo "</section>";
}

// ─────────────────────────────────────────────
// Admin OU Organisateur : événements en attente ou refusés du créateur
// ─────────────────────────────────────────────
echo "<section class='dashboard-section'>";
echo "<h3>Mes événements en attente / refusés</h3>";

$stmt = $pdo->prepare("SELECT * FROM events WHERE id_createur = ? AND statut IN ('en_attente', 'refuse') ORDER BY date_event DESC");
$stmt->execute([$user_id]);
$attente = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$attente) {
    echo "<p>Aucun événement en attente ou refusé.</p>";
} else {
    foreach ($attente as $event) {
        afficherCarteEvenement($event, true); // vue créateur
    }
}
echo "</section>";

// ─────────────────────────────────────────────
// Admin : affichage des événements à valider
// ─────────────────────────────────────────────
if ($role === 'admin') {
    echo "<section class='dashboard-section'>";
    echo "<h3>Événements à valider</h3>";

    $stmt = $pdo->query("SELECT * FROM events WHERE statut = 'en_attente' ORDER BY date_event DESC");
    $en_attente = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$en_attente) {
        echo "<p>Aucun événement à valider.</p>";
    } else {
        foreach ($en_attente as $event) {
            afficherCarteValidation($event);
        }
    }

    echo "</section>";
}

// ─────────────────────────────────────────────
// FONCTION : afficher une carte événement
// ─────────────────────────────────────────────
function afficherCarteEvenement($event, $isCreator) {
    $id = $event["id"];
    $titre = htmlspecialchars($event["titre"]);
    $jeu = htmlspecialchars($event["jeu"]);
    $date = $event["date_event"];
    $heure = $event["heure_event"];
    $description = htmlspecialchars($event["description"]);
    $etat = htmlspecialchars($event["etat"]);
    $img = htmlspecialchars($event["image_url"] ?? 'default.jpg');

    echo "<div class='event-card' data-event-id='$id'>";
    echo "<img class='event-cover' src='/esportify/front/assets/events/$img' alt='Visuel événement'>";
    echo "<h3>$titre</h3>";
    echo "<p><strong>Jeu :</strong> $jeu</p>";
    echo "<p><strong>Date :</strong> $date à $heure</p>";
    echo "<p><strong>Description :</strong> $description</p>";
    echo "<p><strong>État :</strong> <span class='event-etat'>$etat</span></p>";

    if ($etat === 'attente' && $isCreator) {
        echo "<a href='/esportify/back/pages/dashboard.php?changer_etat=en_cours&event_id=$id'><button>Démarrer</button></a>";
    }

    if ($etat === 'en_cours') {
        if ($isCreator) {
            echo "<a href='/esportify/back/pages/dashboard.php?changer_etat=termine&event_id=$id'><button>Terminer</button></a>";
        }
        echo "<a href='/esportify/front/event_live.html?event_id=$id' target='_blank'><button>Rejoindre</button></a>";
    }

    if ($etat === 'termine') {
        echo "<p><em>Événement terminé.</em></p>";
    }

    if ($isCreator) {
        echo "<button onclick='openPointsModal($id)'>Attribuer les points</button>";
    }

    echo "</div>";
}

// ─────────────────────────────────────────────
// FONCTION : afficher une carte pour validation (admin)
// ─────────────────────────────────────────────
function afficherCarteValidation($event) {
    $id = $event["id"];
    $titre = htmlspecialchars($event["titre"]);
    $jeu = htmlspecialchars($event["jeu"]);
    $date = $event["date_event"];
    $heure = $event["heure_event"];
    $description = htmlspecialchars($event["description"]);
    $img = htmlspecialchars($event["image_url"] ?? 'default.jpg');

    echo "<div class='event-card'>";
    echo "<img class='event-cover' src='/esportify/front/assets/events/$img' alt='Visuel événement'>";
    echo "<h3>$titre</h3>";
    echo "<p><strong>Jeu :</strong> $jeu</p>";
    echo "<p><strong>Date :</strong> $date à $heure</p>";
    echo "<p><strong>Description :</strong> $description</p>";
    echo "<a href='/esportify/back/controllers/validate_event.php?event_id=$id&action=valide'><button>✅ Valider</button></a> ";
    echo "<a href='/esportify/back/controllers/validate_event.php?event_id=$id&action=refuse'><button>❌ Refuser</button></a>";
    echo "</div>";
}
?>
