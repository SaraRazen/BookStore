<?php
header("Content-Type: application/json");
include "db.php";

$res = $conn->query("
SELECT b.title, SUM(oi.quantity) AS sold
FROM order_items oi
JOIN books b ON oi.isbn=b.isbn
JOIN customer_orders o ON oi.order_id=o.order_id
WHERE o.order_date >= CURDATE()-INTERVAL 3 MONTH
GROUP BY b.isbn
ORDER BY sold DESC
LIMIT 10
");

$data=[];
while($r=$res->fetch_assoc()) $data[]=$r;
echo json_encode($data);
