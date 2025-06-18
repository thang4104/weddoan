<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    http_response_code(403);
    echo "Bạn chưa đăng nhập.";
    exit;
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("DELETE FROM watch_history WHERE user_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

echo "Đã xóa toàn bộ lịch sử.";
