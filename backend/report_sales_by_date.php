<?php
header("Content-Type: application/json");
include "db.php";

$date = $_GET['date'];

$res = $conn->query("
SELECT SUM(total_price) AS total
FROM customer_orders
WHERE order_date='$date'
");

echo json_encode($res->fetch_assoc());
