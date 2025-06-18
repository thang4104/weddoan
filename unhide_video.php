<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    echo "Không có quyền truy cập.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Thiếu ID video.";
    exit();
}

$videoId = intval($_GET['id']);
$stmt = $conn->prepare("UPDATE videos SET is_hidden = 0 WHERE id = ?");
$stmt->bind_param("i", $videoId);
$stmt->execute();
$stmt->close();

$_SESSION['msg'] = "✅ Đã hiện lại video ID $videoId.";
header("Location: admin.php");
exit();
