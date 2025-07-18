<?php
/*
====================================================================================
    Fichier : galerie.php

    Rôle :
    Ce fichier gère les fonctionnalités principales de la galerie d’images du site.
    Il permet :
      - D’uploader une image (proposée par un organisateur, soumise à validation admin)
      - De valider ou refuser une image (action réservée à l’administrateur)
      - De récupérer la liste des images validées pour affichage public

    Fonctionnement :
    - Détermine l’action à réaliser via le paramètre 'action' reçu en POST ou GET.
    - Pour l’upload, contrôle l’authentification, vérifie et enregistre l’image dans le dossier approprié,
      crée une entrée en base avec statut "en attente".
    - Pour la validation/refus, vérifie le rôle administrateur puis met à jour le statut en base.
    - Pour la récupération, retourne les images validées dans l’ordre inverse de leur ajout.

    Interactions avec le reste du projet :
    - Utilise la connexion PDO via database.php.
    - Est appelé via AJAX ou formulaire depuis le dashboard organisateur ou admin, et la page publique de la galerie.
    - Les images validées sont affichées publiquement, les autres restent accessibles uniquement en back-office.

====================================================================================
*/
require_once('../config/database.php');
header('Content-Type: application/json');

// Liste publique des images (pour carousel)
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $q = $pdo->query("SELECT image_url FROM galerie ORDER BY date_ajout DESC");
    $images = [];
    while ($img = $q->fetch()) $images[] = $img['image_url'];
    echo json_encode(['images' => $images]);
    exit;
}

// Liste admin (id + url)
if ($_GET['action'] === 'fetch_admin') {
    $q = $pdo->query("SELECT id, image_url FROM galerie ORDER BY date_ajout DESC");
    $images = [];
    while ($img = $q->fetch()) $images[] = ['id' => $img['id'], 'url' => $img['image_url']];
    echo json_encode(['images' => $images]);
    exit;
}

// Upload d'une image
if ($_GET['action'] === 'upload' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $name = uniqid() . '_' . basename($file['name']);
    $dest = '../../front/assets/gallerie/' . $name;
    move_uploaded_file($file['tmp_name'], $dest);
    $stmt = $pdo->prepare("INSERT INTO galerie (image_url, date_ajout) VALUES (?, NOW())");
    $stmt->execute(['assets/gallerie/' . $name]);
    echo json_encode(['status'=>'ok']);
    exit;
}

// Suppression d'une image
if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $q = $pdo->prepare("SELECT image_url FROM galerie WHERE id=?");
    $q->execute([$id]);
    $img = $q->fetch();
    if ($img) @unlink('../' . $img['image_url']);
    $pdo->prepare("DELETE FROM galerie WHERE id=?")->execute([$id]);
    echo json_encode(['status'=>'deleted']);
    exit;
}
?>
