<?php
$conn = new mysqli("localhost", "root", "", "bookstore");

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}
?>
