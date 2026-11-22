<?php
include "db.php";

$res = $conn->query("
SELECT SUM(total_price) AS total
FROM customer_orders
WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
");

echo json_encode($res->fetch_assoc());
?>
