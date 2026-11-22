<?php
header("Content-Type: application/json");
include "db.php";

$q = $_GET['q'] ?? '';
$cat = $_GET['cat'] ?? '';
$pub = $_GET['pub'] ?? '';
$auth = $_GET['auth'] ?? '';

$sql = "
SELECT 
b.isbn,
b.title,
b.publication_year,
b.price,
b.stock,
b.category,
p.name AS publisher,
GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS authors
FROM books b
LEFT JOIN book_authors ba ON b.isbn = ba.isbn
LEFT JOIN authors a ON ba.author_id = a.author_id
LEFT JOIN publishers p ON b.publisher_id = p.publisher_id
WHERE
(b.title LIKE '%$q%' OR b.isbn LIKE '%$q%')
AND ('$cat'='' OR b.category='$cat')
AND ('$pub'='' OR p.name LIKE '%$pub%')
AND ('$auth'='' OR a.name LIKE '%$auth%')
GROUP BY b.isbn
";

$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode($data);
