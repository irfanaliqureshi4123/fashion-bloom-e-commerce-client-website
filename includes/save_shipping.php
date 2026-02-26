<?php
session_start();

header('Content-Type: application/json');

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// Store shipping info in session
$_SESSION['shipping_info'] = [
    'first_name' => $data['first_name'] ?? '',
    'last_name' => $data['last_name'] ?? '',
    'email' => $data['email'] ?? '',
    'phone' => $data['phone'] ?? '',
    'address' => $data['address'] ?? '',
    'city' => $data['city'] ?? '',
    'postal' => $data['postal'] ?? '',
    'notes' => $data['notes'] ?? ''
];

echo json_encode(['success' => true]);
?>
