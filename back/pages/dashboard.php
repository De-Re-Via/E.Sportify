<?php

/*
====================================================================================
    Fichier : dashboard.php

    Rôle :
    Ce fichier gère l'affichage dynamique du dashboard utilisateur. Il adapte le contenu
    selon le rôle du membre connecté (joueur, organisateur, administrateur) et affiche
    ses événements, participations, scores ou outils d'administration selon les droits.

    Fonctionnement :
    - Démarre la session PHP et vérifie la connexion de l'utilisateur.
    - Récupère le rôle du membre et adapte l'affichage/les actions possibles.
    - Récupère depuis la base de données les événements et informations pertinentes :
        - Événements créés, participations, scores, etc.
        - Pour les admins : liste globale des événements, outils de gestion.
        - Pour les organisateurs : événements à gérer, validations, etc.
    - Affiche les tableaux, listes, boutons/actions selon les droits.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé lors du chargement du dashboard (dashboard.html ou équivalent).
    - Utilise les informations de session pour la sécurité et l'affichage conditionnel.
    - Fait appel à d'autres scripts pour les actions (validation, suppression, mise à jour d'événements...).

====================================================================================
*/

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

    <!-- BLOC MES PERFORMANCES -->
    <div class="dashboard-column">
      <h3>Mes performances</h3>
      <?php
      require_once("../config/database.php");
      $id_user = $_SESSION['user_id'] ?? null;

      if (!$id_user) {
        echo "<p>Non connecté.</p>";
      } else {
        // Récupérer les scores
        $sql = "
          SELECT e.titre, e.date_event, p.points
          FROM participants p
          JOIN events e ON p.id_event = e.id
          WHERE p.id_user = ?
          ORDER BY e.date_event DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_user]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcul total
        $total = 0;
        foreach ($rows as $r) $total += (int)$r['points'];

        echo "<p><strong>Total des points :</strong> $total</p>";
        if (count($rows) > 0) {
          echo "<ul>";
          foreach ($rows as $row) {
            $titre = htmlspecialchars($row['titre']);
            $date = $row['date_event'];
            $pts = $row['points'];
            echo "<li>$titre ($date) → <strong>$pts pts</strong></li>";
          }
          echo "</ul>";
        } else {
          echo "<p>Aucun point attribué pour le moment.</p>";
        }
      }
      ?>
    </div>
    

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
        $stmt = $pdo->prepare("SELECT * FROM events WHERE statut = 'valide' AND etat != 'termine' AND id_createur != ? ORDER BY date_event DESC");
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

    <!-- Colonne : Mes événements à venir -->
    <div class="dashboard-column">
      <h3>Mes événements à venir</h3>
      <?php
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id_createur = ? AND statut = 'valide' AND etat != 'termine' ORDER BY date_event DESC");
        $stmt->execute([$user_id]);
        $organises = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$organises) {
          echo "<p>Aucun événement à venir.</p>";
        } else {
          foreach ($organises as $event) afficherCarteEvenement($event, true);
        }
      ?>
    </div>
  </div>


   


  <!-- BANNIÈRE DÉROULANTE : événements terminés -->
  <details class="dashboard-column" style="margin-top:2rem;">
    <summary><h3>📁 Événements terminés</h3></summary>
    <?php
      $stmt = $pdo->prepare("SELECT * FROM events WHERE statut = 'valide' AND etat = 'termine' ORDER BY date_event DESC");
      $stmt->execute();
      $termines = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (!$termines) {
        echo "<p>Aucun événement terminé.</p>";
      } else {
        foreach ($termines as $event) afficherCarteEvenement($event, $event['id_createur'] == $user_id);
      }
    ?>
  </details>
</div>


<?php
function afficherCarteEvenement($event) {
  global $pdo;

  $id = $event["id"];
  $titre = htmlspecialchars($event["titre"]);
  $jeu = htmlspecialchars($event["jeu"]);
  $date = $event["date_event"];
  $heure = $event["heure_event"];
  $description = htmlspecialchars($event["description"]);
  $etat = strtolower($event["etat"]);
  $statut = strtolower($event["statut"]);
  $img = htmlspecialchars($event["image_url"] ?? 'default.jpg');
  $max = (int) $event["max_players"];
  $created_at = $event["created_at"];
  $id_createur = $event["id_createur"];

  $user_id = $_SESSION["user_id"];
  $role = $_SESSION["role"];
  $isOwnerOrAdmin = ($user_id == $id_createur || $role === "admin");

  // Récupération du pseudo du créateur
  $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
  $stmt->execute([$id_createur]);
  $creator = $stmt->fetchColumn() ?: 'Inconnu';

  // Comptage des participants
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE id_event = ?");
  $stmt->execute([$id]);
  $inscrits = $stmt->fetchColumn();

  echo "<div class='event-card' data-event-id='$id'>";
  echo "<img class='event-cover' src='assets/events/$img' alt='Visuel événement'>";
  echo "<h3>$titre</h3>";
  echo "<p><strong>Jeu :</strong> $jeu</p>";
  echo "<p><strong>Date :</strong> $date à $heure</p>";
  echo "<p><strong>Description :</strong> $description</p>";
  echo "<p><strong>Créé par :</strong> $creator</p>";
  echo "<p><strong>Statut :</strong> <span class='badge badge-primary'>" . strtoupper($statut) . "</span></p>";
  echo "<p><strong>État :</strong> <span class='event-etat'>" . strtoupper($etat) . "</span></p>";
  echo "<p><strong>Ajouté le :</strong> $created_at</p>";
  echo "<p><strong>Joueurs :</strong> $inscrits / $max</p>";

  // Bouton Démarrer : seulement si statut = valide et état = attente
  if ($statut === 'valide' && $etat === 'attente' && $isOwnerOrAdmin) {
    echo "<button onclick='startEvent($id)'>Démarrer</button>";
  }

  // Bouton Terminer : seulement si statut = valide et état = en_cours
  if ($statut === 'valide' && $etat === 'en_cours' && $isOwnerOrAdmin) {
    echo "<button onclick='endEvent($id)'>Terminer</button>";
  }

  // Bouton Rejoindre : seulement si statut = valide et état = en_cours
  if ($statut === 'valide' && $etat === 'en_cours') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE id_event = ? AND id_user = ?");
    $stmt->execute([$id, $user_id]);
    $isParticipant = $stmt->fetchColumn() > 0;

    if ($isParticipant || $isOwnerOrAdmin || $role === 'organisateur') {
      echo "<a href='/esportify/front/event_live.html?event_id=$id' target='_blank'><button>Rejoindre</button></a>";
    }
  }

  // Boutons Supprimer et Attribuer les points : seulement si statut = valide
  if ($statut === 'valide' && $isOwnerOrAdmin) {
    echo "<button onclick='deleteEvent($id)'>Supprimer</button>";
    echo "<button onclick='openPointsModal($id)'>Attribuer les points</button>";
  }

  echo "</div>";
}



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