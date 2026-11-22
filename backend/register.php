<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['username']) ||
    empty($data['password']) ||
    empty($data['first_name']) ||
    empty($data['last_name']) ||
    empty($data['email']) ||
    empty($data['phone']) ||
    empty($data['address'])
) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields"]);
    exit;
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email format"]);
    exit;
}
$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users
(username, password, first_name, last_name, email, phone, address, role)
VALUES (?, ?, ?, ?, ?, ?, ?, 'CUSTOMER')";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssssss",
    $data['username'],
    $hashedPassword,
    $data['first_name'],
    $data['last_name'],
    $data['email'],
    $data['phone'],
    $data['address']
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Registration successful"]);
} else {
    echo json_encode([
        "message" => "Registration failed",
        "error" => $stmt->error
    ]);
}
?>
