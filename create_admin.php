<?php
require 'config.php';

$email = 'admin@gmail.com';
$password = 'admin123';
$role = 'admin';
$approved = 1;

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (email, password, role, approved) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $email, $hashed_password, $role, $approved);
$stmt->execute();

echo "Admin đã được tạo.";
?>
