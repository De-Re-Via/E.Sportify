<?php
session_start();
require_once("../config/database.php");

if (!isset($_SESSION["user_id"])) {
  echo "<p>Connexion requise.</p>";
  exit;
}

$user_id = $_SESSION["user_id"];
$username = htmlspecialchars($_SESSION["username"]);
$role = $_SESSION["role"] ?? "visiteur";
?>

<div class="dashboard-wrapper">
  <h2 class="welcome-title">Bienvenue <strong><?= $username ?></strong></h2>
  <div class="dashboard-grid">

    <!-- Colonne : Événements à valider -->
    <?php if ($role === 'admin'): ?>
    <div class="dashboard-column">
      <h3>Événements à valider</h3>
      <?php
        $stmt = $pdo->query("SELECT * FROM events WHERE statut = 'en_attente' ORDER BY date_event DESC");
        $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$pending) {
          echo "<p>Aucun événement à valider.</p>";
        } else {
          foreach ($pending as $event) afficherCarteValidation($event);
        }
      ?>
    </div>
    <?php endif; ?>

    <!-- Colonne : Événements validés des autres organisateurs -->
    <?php if ($role === 'admin'): ?>
    <div class="dashboard-column">
      <h3>Événements validés des autres organisateurs</h3>
      <?php
        $stmt = $pdo->prepare("SELECT * FROM events WHERE statut = 'valide' AND id_createur != ? ORDER BY date_event DESC");
        $stmt->execute([$user_id]);
        $others = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$others) {
          echo "<p>Aucun événement d'autres organisateurs.</p>";
        } else {
          foreach ($others as $event) afficherCarteEvenement($event, false);
        }
      ?>
    </div>
    <?php endif; ?>

    <!-- Colonne : Mes événements en attente / refusés -->
    <div class="dashboard-column">
      <h3>Mes événements en attente / refusés</h3>
      <?php
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id_createur = ? AND statut IN ('en_attente', 'refuse') ORDER BY date_event DESC");
        $stmt->execute([$user_id]);
        $attente = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$attente) {
          echo "<p>Aucun événement.</p>";
        } else {
          foreach ($attente as $event) afficherCarteEvenement($event, true);
        }
      ?>
    </div>

    <!-- Colonne : Mes événements validés -->
    <div class="dashboard-column">
      <h3>Mes événements à venir</h3>
      <?php
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id_createur = ? AND statut = 'valide' ORDER BY date_event DESC");
        $stmt->execute([$user_id]);
        $organises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$organises) {
          echo "<p>Aucun événement validé.</p>";
        } else {
          foreach ($organises as $event) afficherCarteEvenement($event, true);
        }
      ?>
    </div>
  </div>
</div>

<?php
// Fonction d'affichage des cartes événements
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
  echo "<img class='event-cover' src='assets/events/$img' alt='Visuel événement'>";
  echo "<h3>$titre</h3>";
  echo "<p><strong>Jeu :</strong> $jeu</p>";
  echo "<p><strong>Date :</strong> $date à $heure</p>";
  echo "<p><strong>Description :</strong> $description</p>";
  echo "<p><strong>État :</strong> <span class='event-etat'>$etat</span></p>";

  if ($etat === 'attente' && $isCreator) {
    echo "<form action='/esportify/back/controllers/update_event_status.php' method='POST'>";
    echo "<input type='hidden' name='event_id' value='$id'>";
    echo "<input type='hidden' name='new_state' value='en_cours'>";
    echo "<button type='submit'>Démarrer</button></form>";
  }

  if ($etat === 'en_cours') {
    if ($isCreator) {
      echo "<form action='/esportify/back/controllers/update_event_status.php' method='POST'>";
      echo "<input type='hidden' name='event_id' value='$id'>";
      echo "<input type='hidden' name='new_state' value='termine'>";
      echo "<button type='submit'>Terminer</button></form>";
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

// Fonction d'affichage pour les cartes de validation (admin uniquement)
function afficherCarteValidation($event) {
  $id = $event["id"];
  $titre = htmlspecialchars($event["titre"]);
  $jeu = htmlspecialchars($event["jeu"]);
  $date = $event["date_event"];
  $heure = $event["heure_event"];
  $description = htmlspecialchars($event["description"]);
  $img = htmlspecialchars($event["image_url"] ?? 'default.jpg');

  echo "<div class='event-card'>";
  echo "<img class='event-cover' src='assets/events/$img' alt='Visuel événement'>";
  echo "<h3>$titre</h3>";
  echo "<p><strong>Jeu :</strong> $jeu</p>";
  echo "<p><strong>Date :</strong> $date à $heure</p>";
  echo "<p><strong>Description :</strong> $description</p>";
  echo "<a href='/esportify/back/controllers/validate_event.php?event_id=$id&action=valide'><button>Valider</button></a> ";
  echo "<a href='/esportify/back/controllers/validate_event.php?event_id=$id&action=refuse'><button>Refuser</button></a>";
  echo "</div>";
}
?>
