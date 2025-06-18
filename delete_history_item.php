<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email']) || !isset($_POST['file_path'])) {
    http_response_code(400);
    echo "Dữ liệu không hợp lệ.";
    exit();
}

$email = $_SESSION['email'];
$file_path = $_POST['file_path'];

// Lấy video_id tương ứng
$stmt = $conn->prepare("SELECT id FROM videos WHERE file_path = ?");
$stmt->bind_param("s", $file_path);
$stmt->execute();
$stmt->bind_result($video_id);
$stmt->fetch();
$stmt->close();

if (!$video_id) {
    http_response_code(404);
    echo "Không tìm thấy video.";
    exit();
}

// Xóa lịch sử dòng tương ứng
$del = $conn->prepare("DELETE FROM watch_history WHERE user_email = ? AND video_id = ?");
$del->bind_param("si", $email, $video_id);
$del->execute();
$del->close();

echo "Đã xóa.";
