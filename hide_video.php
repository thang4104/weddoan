<?php
session_start();
require 'config.php';

if ($_SESSION['email'] !== 'admin@gmail.com') {
    exit('Không có quyền truy cập');
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE videos SET is_hidden = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin.php");
exit();
?>
