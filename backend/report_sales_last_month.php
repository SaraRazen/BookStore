<?php
header("Content-Type: application/json");
include "db.php";

$res = $conn->query("
SELECT SUM(total_price) AS total
FROM customer_orders
WHERE MONTH(order_date)=MONTH(CURDATE()-INTERVAL 1 MONTH)
");

echo json_encode($res->fetch_assoc());
