// Promotion de l'utilisateur en organisateur si ce n'est pas déjà le cas

$updateRole = $pdo->prepare("UPDATE users SET role = 'organisateur' WHERE id = ? AND role = 'joueur'");
$updateRole->execute([$id_createur]);