<?php
$conn = new mysqli("localhost", "root", "", "exp8_store");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>