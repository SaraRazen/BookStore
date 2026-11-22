<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

// Assign variables
$isbn = trim($data['isbn'] ?? '');
$title = trim($data['title'] ?? '');
$publisher_id = $data['publisher_id'] ?? '';
$year = $data['publication_year'] ?? '';
$category = $data['category'] ?? '';
$price = $data['price'] ?? '';
$stock = $data['stock'] ?? '';
$threshold = $data['threshold'] ?? '';

// Required fields
if (
    $isbn === '' || $title === '' || $publisher_id === '' || $year === '' ||
    $category === '' || $price === '' || $stock === '' || $threshold === ''
) {
    http_response_code(400);
    echo json_encode(["message" => "All fields are required"]);
    exit;
}

// Check ISBN duplicate
$check = $conn->prepare("SELECT isbn FROM books WHERE isbn = ?");
$check->bind_param("s", $isbn);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["message" => "Book with this ISBN already exists"]);
    exit;
}

// Check publisher exists
$pub = $conn->prepare("SELECT publisher_id FROM publishers WHERE publisher_id = ?");
$pub->bind_param("i", $publisher_id);
$pub->execute();
$pub->store_result();

if ($pub->num_rows === 0) {
    http_response_code(400);
    echo json_encode(["message" => "Publisher does not exist"]);
    exit;
}

// Validate publication year
$currentYear = date("Y");
if (!ctype_digit(strval($year)) || $year < 1000 || $year > $currentYear) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid publication year"]);
    exit;
}

// Validate category
$allowed = ['Science','Art','Religion','History','Geography'];
if (!in_array($category, $allowed)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid category"]);
    exit;
}

// Numeric validation
if (!is_numeric($price) || $price <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "Price must be a positive number"]);
    exit;
}

if (!ctype_digit(strval($stock))) {
    http_response_code(400);
    echo json_encode(["message" => "Stock must be a non-negative integer"]);
    exit;
}

if (!ctype_digit(strval($threshold))) {
    http_response_code(400);
    echo json_encode(["message" => "Threshold must be a non-negative integer"]);
    exit;
}

// Insert
$sql = "INSERT INTO books
(isbn, title, publisher_id, publication_year, price, category, stock, threshold)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssiidsii",
    $isbn,
    $title,
    $publisher_id,
    $year,
    $price,
    $category,
    $stock,
    $threshold
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Book added successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Database error"]);
}
