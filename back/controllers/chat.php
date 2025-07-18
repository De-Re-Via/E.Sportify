<?php

/*
====================================================================================
    Fichier : chat.php

    Rôle :
    Ce fichier gère les actions liées au système de messagerie (chat) entre membres connectés.
    Il permet :
      - D'envoyer un nouveau message
      - De récupérer la liste des messages pour l'affichage du chat

    Fonctionnement :
    - Le fichier détermine l'action à effectuer selon le paramètre 'action' reçu via POST ou GET.
    - Pour l'envoi d'un message, il enregistre le contenu du message, l'identifiant de l'utilisateur et la date/heure dans la base de données.
    - Pour la récupération, il retourne la liste des derniers messages stockés, triés du plus récent au plus ancien, pour affichage côté client.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO définie dans database.php.
    - Est appelé en AJAX ou via formulaire par la page chat.html (ou autre interface) pour gérer l'envoi et l'affichage en temps réel.
    - Nécessite que l'utilisateur soit connecté (session active).
====================================================================================
*/


session_start();
header('Content-Type: application/json');

// Debug (à retirer en prod)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../config/database.php');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer les messages (ordre du plus ancien au plus récent)
            $stmt = $pdo->prepare("SELECT * FROM messages ORDER BY date_envoi DESC");
            $stmt->execute();
            $raw_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Adapter les champs pour le front (identiques à ta base !)
            $messages = array_map(function($msg) {
                return [
                    "id" => $msg["id"],
                    "auteur" => $msg["auteur"],
                    "contenu" => $msg["contenu"],
                    "date_envoi" => $msg["date_envoi"]
                ];
            }, $raw_messages);

            echo json_encode([
                "messages" => $messages,
                "isAdmin" => (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')
            ]);
            break;

        case 'POST':
            // Ajouter un message
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['contenu'])) {
                if (!isset($_SESSION['username'])) {
                    echo json_encode(['error' => 'Utilisateur non connecté']);
                    exit;
                }
                $auteur = htmlspecialchars($_SESSION['username']);
                $contenu = htmlspecialchars($data['contenu']);
            } else if (isset($data['auteur']) && isset($data['contenu'])) {
                $auteur = htmlspecialchars($data['auteur']);
                $contenu = htmlspecialchars($data['contenu']);
            } else {
                echo json_encode(['error' => 'Champs manquants']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO messages (auteur, contenu) VALUES (?, ?)");
            $success = $stmt->execute([$auteur, $contenu]);

            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Erreur lors de l\'insertion']);
            }
            break;

        case 'DELETE':
            // Suppression d'un message (admin uniquement)
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
                $success = $stmt->execute([$data['id']]);
                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Erreur lors de la suppression']);
                }
            } else {
                echo json_encode(['error' => 'Droits insuffisants ou ID manquant']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
