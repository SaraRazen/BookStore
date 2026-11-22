<?php
header("Content-Type: application/json");
include "db.php";

// Admin sees ALL orders
$res = $conn->query("
SELECT o.order_id, o.order_date, o.total_price,
       u.username AS customer,
       GROUP_CONCAT(CONCAT(b.title, ' (x', oi.quantity, ')') SEPARATOR ', ') AS books
FROM customer_orders o
JOIN users u ON o.user_id = u.user_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN books b ON oi.isbn = b.isbn
GROUP BY o.order_id, o.order_date, o.total_price, u.username
ORDER BY o.order_date DESC
");

$data = [];
while($r = $res->fetch_assoc()) {
    $r['total_price'] = floatval($r['total_price']); // Ensure number
    $data[] = $r;
}
echo json_encode($data);
?>