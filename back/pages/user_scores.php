<?php
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
