<?php
/**
 * Fichier : all_events.php
 * ➤ Rôle : retourne des blocs HTML filtrés selon les critères reçus
 * ➤ Utilisé par event-repo.js pour afficher dynamiquement la liste publique des événements
 */

// ➤ Démarre la session PHP pour accéder aux variables $_SESSION
session_start();
require_once("../config/database.php");

// Chargement des filtres transmis en POST (via JS)
$where = [];
$params = [];

if (!empty($_POST["titre"])) {
    $where[] = "titre LIKE ?";
    $params[] = "%" . $_POST["titre"] . "%";
}
if (!empty($_POST["jeu"])) {
    $where[] = "jeu LIKE ?";
    $params[] = "%" . $_POST["jeu"] . "%";
}
if (!empty($_POST["statut"])) {
    $where[] = "statut = ?";
    $params[] = $_POST["statut"];
}
if (!empty($_POST["date_event"])) {
    $where[] = "date_event = ?";
    $params[] = $_POST["date_event"];
}

$filterSql = $where ? "WHERE " . implode(" AND ", $where) : "";

try {
    // Récupération des événements + auteur
    $stmt = $pdo->prepare("
        SELECT e.*, u.username
        FROM events e
        JOIN users u ON e.id_createur = u.id
        $filterSql
        ORDER BY e.date_event DESC, e.heure_event DESC
    ");
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$events) {
        echo "<p>Aucun événement trouvé.</p>";
        exit;
    }

    // Récupération des participations groupées par id_event
    $inscriptions = $pdo->query("
        SELECT id_event, id_user
        FROM participants
    ")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

    // Fonction utilitaire pour savoir si l'utilisateur est inscrit à un événement
    function isUserRegistered($event_id, $user_id, $inscriptions) {
        return isset($inscriptions[$event_id]) && in_array($user_id, $inscriptions[$event_id]);
    }

    // Tri des événements : à venir, aujourd’hui, passés
    $today = date("Y-m-d");
    $upcoming = [];
    $todayEvents = [];
    $past = [];

    foreach ($events as $event) {
        if ($event["date_event"] > $today) {
            $upcoming[] = $event;
        } elseif ($event["date_event"] === $today) {
            $todayEvents[] = $event;
        } else {
            $past[] = $event;
        }
    }

    // Fonction d'affichage des cartes événements
    function displayEventCards($list, $label) {
        global $inscriptions;

        // Affiche le titre de la section (Aujourd’hui, À venir, Passés)
        echo "<h2 class='event-section-title'>$label</h2>";

        foreach ($list as $event) {
            $id = $event["id"];
            $titre = htmlspecialchars($event["titre"]);
            $desc = htmlspecialchars($event["description"]);
            $jeu = htmlspecialchars($event["jeu"]);
            $date = $event["date_event"];
            $heure = $event["heure_event"];
            $statut = $event["statut"];
            $auteur = htmlspecialchars($event["username"]);
            $created = $event["created_at"];
            $image = htmlspecialchars($event["image_url"] ?? "default.jpg");

            // Nombre de joueurs
            $max = $event["max_players"];
            $current = isset($inscriptions[$id]) ? count($inscriptions[$id]) : 0;

            // Affichage de la carte
            echo "<div class='event-card'>";
            echo "<img src='assets/events/$image' alt='$titre' class='event-cover' />";
            echo "<h3>$titre</h3>";
            echo "<p><strong>Jeu :</strong> $jeu</p>";
            echo "<p><strong>Date :</strong> $date à $heure</p>";
            echo "<p><strong>Description :</strong> $desc</p>";
            echo "<p><strong>Créé par :</strong> $auteur</p>";
            echo "<p><strong>Statut :</strong> <span class='badge $statut'>$statut</span></p>";
            echo "<p><strong>Ajouté le :</strong> $created</p>";
            echo "<p><strong>Joueurs :</strong> $current / $max</p>";

            // Affichage des boutons d'action selon le rôle et l'état
            if ($statut === "valide") {
                if (!isset($_SESSION["role"])) {
                    echo "<p class='not-logged-message'>Devenez joueur sur notre plateforme pour vous inscrire à cet event !</p>";
                } elseif ($current >= $max && !isUserRegistered($id, $_SESSION["user_id"], $inscriptions)) {
                    echo "<p class='not-logged-message'>Événement complet</p>";
                } elseif (isUserRegistered($id, $_SESSION["user_id"], $inscriptions)) {
                    echo "<button data-event-id='$id' class='unregister-btn'>Se désinscrire</button>";
                } else {
                    echo "<button data-event-id='$id' class='register-btn'>S’inscrire</button>";
                }
            }

            // Affichage du bouton Supprimer (admin uniquement)
            if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
                echo "<button onclick='supprimerEvent($id)' class='btn-delete'>Supprimer</button>";
            }

            echo "</div>";
        }
    }

    // Appels à la fonction pour afficher les trois sections
    displayEventCards($todayEvents, "Aujourd’hui");
    displayEventCards($upcoming, "À venir");
    displayEventCards($past, "Passés");

} catch (PDOException $e) {
    echo "<p>Erreur SQL : " . $e->getMessage() . "</p>";
}