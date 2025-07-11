<?php
/**
 * manage_events.php
 * Rôle : Affichage et traitement des événements en attente (modération)
 */

session_start();
require_once("../config/database.php");

// Protection : accès réservé à l'admin uniquement
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'organisateur'])) {
    http_response_code(403);
    echo "Accès refusé : réservé à l'administrateur.";
    exit;
}


// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
    echo "Accès refusé.";
    exit;
}

// Récupère les infos
$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];

// Seuls les organisateurs ou admins peuvent modérer
if (!in_array($role, ['organisateur', 'admin'])) {
    echo "Accès interdit.";
    exit;
}

// === TRAITEMENT DES ACTIONS DE MODÉRATION (via fetch en POST) ===
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["event_id"]) && isset($_POST["action"])) {
    $event_id = $_POST["event_id"];
    $action = $_POST["action"];

    if (!in_array($action, ["valide", "refuse"])) {
        echo "Action invalide.";
        exit;
    }

    // Récupère l’ID du créateur de l’événement
    $stmt = $pdo->prepare("SELECT id_createur FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        echo "Événement introuvable.";
        exit;
    }

    $createur_id = $event["id_createur"];

    // Met à jour le statut de l’événement
    $stmt = $pdo->prepare("UPDATE events SET statut = ? WHERE id = ?");
    $stmt->execute([$action, $event_id]);

    // Si l’action est "valide", on vérifie si le créateur est "joueur"
    if ($action === "valide") {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$createur_id]);
        $role_actuel = $stmt->fetchColumn();

        if ($role_actuel === "joueur") {
            // Promote à organisateur
            $stmt = $pdo->prepare("UPDATE users SET role = 'organisateur' WHERE id = ?");
            $stmt->execute([$createur_id]);
        }
    }

    echo "ok";
    exit;
}

// === AFFICHAGE DES ÉVÉNEMENTS EN ATTENTE ===

$stmt = $pdo->query("SELECT e.*, u.username FROM events e 
                     JOIN users u ON e.id_createur = u.id 
                     WHERE statut = 'en_attente'
                     ORDER BY e.created_at DESC");

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$events) {
    echo "<p>Aucun événement en attente.</p>";
    exit;
}

foreach ($events as $event) {
    $titre = htmlspecialchars($event["titre"]);
    $desc = htmlspecialchars($event["description"]);
    $jeu = htmlspecialchars($event["jeu"]);
    $date = $event["date_event"];
    $heure = $event["heure_event"];
    $auteur = htmlspecialchars($event["username"]);
    $id = $event["id"];

    echo "<div class='event-card'>";
    echo "<h3>$titre</h3>";
    echo "<p><strong>Créé par :</strong> $auteur</p>";
    echo "<p><strong>Jeu :</strong> $jeu</p>";
    echo "<p><strong>Date :</strong> $date à $heure</p>";
    echo "<p><strong>Description :</strong> $desc</p>";
    echo "<button onclick=\"validerEvent($id)\">Valider</button> ";
    echo "<button onclick=\"refuserEvent($id)\">Refuser</button>";
    echo "</div>";
}

// Affiche un bouton d’attribution des points pour les événements validés
$stmt_valide = $pdo->query("SELECT e.*, u.username FROM events e 
                     JOIN users u ON e.id_createur = u.id 
                     WHERE statut = 'valide'
                     ORDER BY e.date_event DESC");

$events_valide = $stmt_valide->fetchAll(PDO::FETCH_ASSOC);

if ($events_valide) {
    echo "<hr><h2>Événements validés à gérer :</h2>";

    foreach ($events_valide as $event) {
        $titre = htmlspecialchars($event["titre"]);
        $jeu = htmlspecialchars($event["jeu"]);
        $date = $event["date_event"];
        $heure = $event["heure_event"];
        $id = $event["id"];

        echo "<div class='event-card'>";
        echo "<h3>$titre</h3>";
        echo "<p><strong>Jeu :</strong> $jeu</p>";
        echo "<p><strong>Date :</strong> $date à $heure</p>";
        echo "<button onclick=\"openPointsModal($id)\">Attribuer les points</button>";
        echo "</div>";
    }
}

?>

<!-- MODAL : Attribution de points -->
<div id="pointsModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closePointsModal()">&times;</span>
    <h2>Attribuer les points</h2>
    <form id="assignPointsForm">
      <input type="hidden" name="id_event" id="pointsEventId" />
      <div id="playersList">
        <!-- Les joueurs seront injectés ici via JS -->
      </div>
      <button type="submit">Valider les scores</button>
    </form>
  </div>
</div>
