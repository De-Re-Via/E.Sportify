<?php
session_start(); // ← OBLIGATOIRE avant toute lecture de session
header("Content-Type: application/json");

$response = [
    "loggedIn" => isset($_SESSION["user_id"]),
    "username" => $_SESSION["username"] ?? null,
    "role" => $_SESSION["role"] ?? null
];

echo json_encode($response);
