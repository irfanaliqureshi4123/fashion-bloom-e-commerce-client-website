<?php
session_start();

header('Content-Type: application/json');

$logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

echo json_encode([
    'logged_in' => $logged_in,
    'user_id' => $user_id
]);
?>
