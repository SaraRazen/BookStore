<?php
header("Content-Type: application/json");
include "db.php";

$user = $_GET['user_id'];

$res = $conn->query("
SELECT o.order_id, o.order_date, o.total_price,
GROUP_CONCAT(b.title SEPARATOR ', ') AS books
FROM customer_orders o
JOIN order_items oi ON o.order_id=oi.order_id
JOIN books b ON oi.isbn=b.isbn
WHERE o.user_id=$user
GROUP BY o.order_id
");

$data=[];
while($r=$res->fetch_assoc()) $data[]=$r;
echo json_encode($data);
