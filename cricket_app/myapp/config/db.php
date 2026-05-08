<?php
$conn = new mysqli("127.0.0.1", "root", "", "test_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>