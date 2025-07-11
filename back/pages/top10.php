<?php
require_once("../config/database.php");

// Requête : top 10 des joueurs avec points
$stmt = $pdo->prepare("
    SELECT u.username, SUM(p.points) AS total_points
    FROM participants p
    JOIN users u ON p.id_user = u.id
    GROUP BY p.id_user
    HAVING total_points > 0
    ORDER BY total_points DESC
    LIMIT 10
");
$stmt->execute();
$top = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Génère la liste
echo "<h3>Top 10 Joueurs</h3>";
echo "<ol class='top10-list'>";
foreach ($top as $joueur) {
    $pseudo = htmlspecialchars($joueur["username"]);
    $points = $joueur["total_points"];
    echo "<li>$pseudo - $points pts</li>";
}
echo "</ol>";
