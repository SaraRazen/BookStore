<?php
include "db.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get JSON data
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON data"]);
    exit();
}

$username = $data["username"] ?? "";
$password = $data["password"] ?? "";

// Use prepared statements to prevent SQL injection
$sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        echo json_encode([
            "user_id" => $user['user_id'],
            "username" => $user['username'],
            "role" => $user['role']
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Invalid login credentials"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Invalid login credentials"]);
}


$stmt->close();
$conn->close();
?>