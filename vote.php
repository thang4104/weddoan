<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

if (!isset($_POST['video_id'], $_POST['type'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
    exit;
}

$videoId = (int)$_POST['video_id'];
$type = $_POST['type'];

if ($type === 'like') {
    $stmt = $conn->prepare("UPDATE videos SET likes = likes + 1 WHERE id = ?");
} elseif ($type === 'dislike') {
    $stmt = $conn->prepare("UPDATE videos SET dislikes = dislikes + 1 WHERE id = ?");
} else {
    echo json_encode(['success' => false, 'message' => 'Loại không hợp lệ']);
    exit;
}

$stmt->bind_param("i", $videoId);
$stmt->execute();
$stmt->close();

// Lấy lại lượt like/dislike mới
$stmt = $conn->prepare("SELECT likes, dislikes FROM videos WHERE id = ?");
$stmt->bind_param("i", $videoId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode([
    'success' => true,
    'likes' => $result['likes'],
    'dislikes' => $result['dislikes']
]);
