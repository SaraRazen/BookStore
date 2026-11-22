<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['order_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Order ID is required"]);
    exit;
}

$order_id = $data['order_id'];

/* Check if order exists */
$stmt = $conn->prepare(
    "SELECT status FROM publisher_orders WHERE order_id = ?"
);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Order ID does not exist"]);
    exit;
}

$order = $result->fetch_assoc();

/* Check if already confirmed */
if ($order['status'] === 'CONFIRMED') {
    http_response_code(409);
    echo json_encode(["message" => "Order is already confirmed"]);
    exit;
}

/* Confirm order */
$update = $conn->prepare(
    "UPDATE publisher_orders SET status = 'CONFIRMED' WHERE order_id = ?"
);
$update->bind_param("i", $order_id);

if ($update->execute()) {
    echo json_encode([
        "message" => "Order confirmed successfully"
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Database error"]);
}
