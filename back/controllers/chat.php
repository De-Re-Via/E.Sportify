<?php
session_start();

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

try {
  $pdo = new PDO("mysql:host=localhost;dbname=esportify;charset=utf8", "root", "");
} catch (PDOException $e) {
  http_response_code(500);
  echo "Erreur BDD";
  exit;
}

// ✅ GET — récupérer les messages + rôle admin
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $stmt = $pdo->query("SELECT * FROM messages ORDER BY date_envoi DESC LIMIT 50");
  $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    "isAdmin" => $isAdmin,
    "messages" => $messages
  ]);
  exit;
}

// ✅ POST — ajouter un message (utilisateur connecté requis)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo "Connexion requise.";
    exit;
  }

  $data = json_decode(file_get_contents("php://input"), true);
  if (!isset($data['contenu'])) {
    http_response_code(400);
    echo "Contenu manquant.";
    exit;
  }

  $auteur = $_SESSION['username'];
  $contenu = substr(trim($data['contenu']), 0, 100);

  $stmt = $pdo->prepare("INSERT INTO messages (auteur, contenu) VALUES (?, ?)");
  $stmt->execute([$auteur, $contenu]);
  echo "Message envoyé.";
  exit;
}

// ✅ DELETE — supprimer un message (admin uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  if (!$isAdmin) {
    http_response_code(403);
    echo "Accès refusé.";
    exit;
  }

  $data = json_decode(file_get_contents("php://input"), true);
  if (!isset($data['id'])) {
    http_response_code(400);
    echo "ID manquant.";
    exit;
  }

  $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
  $stmt->execute([$data['id']]);
  echo "Message supprimé.";
  exit;
}

http_response_code(405);
echo "Méthode non autorisée.";
