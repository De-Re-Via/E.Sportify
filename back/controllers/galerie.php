<?php
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
