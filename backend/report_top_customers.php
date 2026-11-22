<?php
header("Content-Type: application/json");
include "db.php";

$res = $conn->query("
SELECT u.username, SUM(o.total_price) AS spent
FROM customer_orders o
JOIN users u ON o.user_id=u.user_id
WHERE o.order_date >= CURDATE()-INTERVAL 3 MONTH
GROUP BY u.user_id
ORDER BY spent DESC
LIMIT 5
");

$data=[];
while($r=$res->fetch_assoc()) $data[]=$r;
echo json_encode($data);
