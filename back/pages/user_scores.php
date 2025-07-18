<?php

/*
====================================================================================
    Fichier : user_scores.php

    Rôle :
    Ce fichier permet de récupérer le détail des scores et classements d'un utilisateur donné sur tous les événements auxquels il a participé.
    Il sert à afficher sur le dashboard, la page profil ou le tableau de bord personnel un historique détaillé des performances du joueur.

    Fonctionnement :
    - Reçoit l'identifiant de l'utilisateur via GET (paramètre 'user_id').
    - Interroge la base de données pour obtenir tous les scores et classements de ce joueur, pour chaque événement où il a participé.
    - Retourne la liste détaillée des participations au format JSON.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé généralement via AJAX lors du chargement du dashboard, de la page profil, ou pour afficher le palmarès du joueur.
    - Peut être utilisé pour générer des graphiques ou des statistiques détaillées.

====================================================================================
*/

require_once("../config/database.php");

// Requête : total des points par joueur
$stmt = $pdo->prepare("
    SELECT u.username, SUM(p.points) AS total_points, COUNT(*) AS participations
    FROM participants p
    JOIN users u ON p.id_user = u.id
    GROUP BY p.id_user
    HAVING total_points > 0
    ORDER BY total_points DESC
    LIMIT 100
");
$stmt->execute();
$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Affichage HTML
echo "<h2>Classement général des joueurs</h2>";

if (!$joueurs) {
    echo "<p>Aucun joueur classé pour le moment.</p>";
    exit;
}

echo "<table class='score-table'>";
echo "<thead><tr><th>Position</th><th>Joueur</th><th>Points</th><th>Participations</th></tr></thead>";
echo "<tbody>";

$position = 1;
foreach ($joueurs as $joueur) {
    $pseudo = htmlspecialchars($joueur["username"]);
    $points = $joueur["total_points"];
    $count = $joueur["participations"];

    echo "<tr>";
    echo "<td>$position</td>";
    echo "<td>$pseudo</td>";
    echo "<td>$points</td>";
    echo "<td>$count</td>";
    echo "</tr>";

    $position++;
}

echo "</tbody></table>";
