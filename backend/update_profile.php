<?php
header("Content-Type: application/json");
require "db.php";

// Validate email format if provided
if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid email format"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["message" => "User ID missing"]);
    exit;
}

$fields = [];
$params = [];
$types = "";

if (!empty($data['first_name'])) {
    $fields[] = "first_name = ?";
    $params[] = $data['first_name'];
    $types .= "s";
}
if (!empty($data['last_name'])) {
    $fields[] = "last_name = ?";
    $params[] = $data['last_name'];
    $types .= "s";
}
if (!empty($data['email'])) {
    $fields[] = "email = ?";
    $params[] = $data['email'];
    $types .= "s";
}
if (!empty($data['phone'])) {
    $fields[] = "phone = ?";
    $params[] = $data['phone'];
    $types .= "s";
}
if (!empty($data['address'])) {
    $fields[] = "address = ?";
    $params[] = $data['address'];
    $types .= "s";
}
if (!empty($data['password'])) {
    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $fields[] = "password = ?";
    $params[] = $hashed;
    $types .= "s";
}

if (empty($fields)) {
    echo json_encode(["message" => "Nothing to update"]);
    exit;
}

$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE user_id = ?";
$params[] = $user_id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(["message" => "Profile updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Update failed", "error" => $stmt->error]);
}
