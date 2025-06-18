<?php
session_start();
require 'config.php';

if ($_SESSION['email'] !== 'admin@gmail.com') {
    exit('Không có quyền truy cập');
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Lấy file_path để xóa file thật
    $stmt = $conn->prepare("SELECT file_path FROM videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($filePath);
    $stmt->fetch();
    $stmt->close();

    // Xóa khỏi DB
    $conn->query("DELETE FROM videos WHERE id = $id");

    // Xóa file khỏi thư mục uploads
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

header("Location: admin.php");
exit();
?>
