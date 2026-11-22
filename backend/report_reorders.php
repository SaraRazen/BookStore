<?php
header("Content-Type: application/json");
include "db.php";

$isbn = $_GET['isbn'];

$res = $conn->query("
SELECT COUNT(*) AS times
FROM publisher_orders
WHERE isbn='$isbn'
");

echo json_encode($res->fetch_assoc());
