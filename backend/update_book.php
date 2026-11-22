<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$isbn = trim($data['isbn'] ?? '');
$price = $data['price'] ?? '';
$stock = $data['stock'] ?? '';
$threshold = $data['threshold'] ?? '';

// Numeric validations
// Price: number > 0
if ($price !== '') {
    if (!is_numeric($price) || $price <= 0) {
        http_response_code(400);
        echo json_encode(["message" => "Price must be a positive number"]);
        exit;
    }
}

// Stock: integer >= 0
if ($stock !== '') {
    if (!ctype_digit(strval($stock))) {
        http_response_code(400);
        echo json_encode(["message" => "Stock must be a non-negative integer"]);
        exit;
    }
}

// Threshold: integer >= 0
if ($threshold !== '') {
    if (!ctype_digit(strval($threshold))) {
        http_response_code(400);
        echo json_encode(["message" => "Threshold must be a non-negative integer"]);
        exit;
    }
}



// ISBN required
if ($isbn === '') {
    http_response_code(400);
    echo json_encode(["message" => "ISBN is required"]);
    exit;
}

// Check if book exists
$check = $conn->prepare("SELECT isbn FROM books WHERE isbn = ?");
$check->bind_param("s", $isbn);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Book does not exist"]);
    exit;
}

// Build dynamic update query
$fields = [];
$params = [];
$types = "";

// Only add fields if NOT empty
if ($price !== '') {
    $fields[] = "price = ?";
    $params[] = $price;
    $types .= "d";
}

if ($stock !== '') {
    $fields[] = "stock = ?";
    $params[] = $stock;
    $types .= "i";
}

if ($threshold !== '') {
    $fields[] = "threshold = ?";
    $params[] = $threshold;
    $types .= "i";
}

// Must update at least one field
if (empty($fields)) {
    http_response_code(400);
    echo json_encode(["message" => "No fields to update"]);
    exit;
}

// Add ISBN at the end
$params[] = $isbn;
$types .= "s";

$sql = "UPDATE books SET " . implode(", ", $fields) . " WHERE isbn = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

// Execute
if ($stmt->execute()) {
    echo json_encode(["message" => "Book updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Update failed"]);
}
