<?php
header("Content-Type: application/json");
include "db.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$d = json_decode(file_get_contents("php://input"), true);

$user = (int)$d['user_id'];
$cart = $d['cart'];
$card = $d['card'];
$exp  = $d['exp'];

if (empty($cart)) {
    http_response_code(400);
    echo json_encode(["message"=>"Cart is empty"]);
    exit;
}

if (!preg_match("/^[0-9]{16}$/", $card)) {
    http_response_code(400);
    echo json_encode(["message"=>"Invalid card number"]);
    exit;
}

/* Expiry validation */
if (!preg_match("/^(0[1-9]|1[0-2])\/[0-9]{2}$/", $exp)) {
    http_response_code(400);
    echo json_encode(["message"=>"Invalid expiry format"]);
    exit;
}

list($mm, $yy) = explode('/', $exp);
$expDate = strtotime("20$yy-$mm-01 +1 month");
if ($expDate < time()) {
    http_response_code(400);
    echo json_encode(["message"=>"Card expired"]);
    exit;
}

try {
    $conn->begin_transaction();

    /* Check stock BEFORE creating order */
    foreach ($cart as $i) {
        $isbn = $i['isbn'];
        $qty  = $i['qty'];

        $res = $conn->query("SELECT stock FROM books WHERE isbn='$isbn'");
        $row = $res->fetch_assoc();

        if (!$row || $row['stock'] < $qty) {
            throw new Exception("Not enough stock for $isbn");
        }
    }

    /* Create order */
    $conn->query("
        INSERT INTO customer_orders (user_id, order_date, total_price)
        VALUES ($user, CURDATE(), 0)
    ");
    $order_id = $conn->insert_id;

    $total = 0;

    foreach ($cart as $i) {
        $isbn = $i['isbn'];
        $qty  = $i['qty'];
        $price = $i['price'];

        $total += $price * $qty;

        $conn->query("
            INSERT INTO order_items (order_id, isbn, quantity, price)
            VALUES ($order_id, '$isbn', $qty, $price)
        ");
    }

    if ($total <= 0) {
        throw new Exception("Invalid order total");
    }

    $conn->query("
        UPDATE customer_orders
        SET total_price = $total
        WHERE order_id = $order_id
    ");

    $conn->commit();
    echo json_encode(["message"=>"Checkout successful"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(["message"=>$e->getMessage()]);
}
