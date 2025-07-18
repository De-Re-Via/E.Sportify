<?php
// Connexion à la base de données
require_once(__DIR__ . '/../config/database.php');
session_start();

/*
   Objectif :
   - Prendre en compte tous les participants qui ont au moins une participation
   - Ajouter tous les points (même 0), sauf si la valeur est NULL
   - Classer les utilisateurs par total décroissant
*/

$sql = "
    SELECT u.username, SUM(CASE WHEN p.points IS NOT NULL THEN p.points ELSE 0 END) AS total_points
    FROM participants p
    JOIN users u ON p.id_user = u.id
    GROUP BY p.id_user
    ORDER BY total_points DESC
    LIMIT 10
";

$stmt = $pdo->query($sql);
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Affichage
echo "<h2>Top 10 Joueurs</h2>";
echo "<ol>";
foreach ($players as $player) {
    $pts = (int) $player['total_points'];
    echo "<li>{$player['username']} – {$pts} pts</li>";
}
echo "</ol>";
