<?php

/*
====================================================================================
    Fichier : top10.php

    Rôle :
    Ce fichier permet de récupérer le classement des 10 meilleurs joueurs du site, basé sur le total de points
    cumulés par chaque utilisateur. Il est utilisé pour afficher un tableau ou un widget "Top 10" visible par tous.

    Fonctionnement :
    - Interroge la base de données pour calculer le total de points de chaque utilisateur (jointure sur la table scores).
    - Trie les résultats par total décroissant pour afficher les meilleurs en haut du classement.
    - Limite la liste aux 10 premiers.
    - Retourne le classement au format JSON.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Appelé généralement via AJAX lors du chargement du widget classement sur la page d'accueil, le dashboard, etc.
    - Les résultats sont affichés dynamiquement côté client.

====================================================================================
*/

// Connexion à la base de données
require_once(__DIR__ . '/../config/database.php');
session_start();

/*
   Objectif :
   - Prendre en compte tous les participants qui ont au moins une participation
   - Ajouter tous les points (même 0), sauf si la valeur est NULL
   - Classer les utilisateurs par total décroissant
*/
// Préparation de la requête SQL pour calculer le top 10 des joueurs par total de points
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
