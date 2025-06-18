<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email']) || !isset($_POST['video_id']) || !isset($_POST['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$user_email = $_SESSION['email'];
$video_id = intval($_POST['video_id']);
$type = $_POST['type']; // 'like' hoặc 'dislike'

// Kiểm tra đã có like/dislike chưa
$stmt = $conn->prepare("SELECT type FROM likes WHERE user_email = ? AND video_id = ?");
$stmt->bind_param("si", $user_email, $video_id);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

if ($existing) {
    if ($existing['type'] === $type) {
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_email = ? AND video_id = ?");
        $stmt->bind_param("si", $user_email, $video_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE likes SET type = ? WHERE user_email = ? AND video_id = ?");
        $stmt->bind_param("ssi", $type, $user_email, $video_id);
        $stmt->execute();
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("INSERT INTO likes (user_email, video_id, type) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $user_email, $video_id, $type);
    $stmt->execute();
    $stmt->close();
}

$stmt = $conn->prepare("SELECT 
    SUM(type = 'like') as likeCount, 
    SUM(type = 'dislike') as dislikeCount 
    FROM likes WHERE video_id = ?");
$stmt->bind_param("i", $video_id);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc();
$stmt->close();

echo json_encode([
    'success' => true,
    'likeCount' => $count['likeCount'],
    'dislikeCount' => $count['dislikeCount'],
    'userReaction' => $existing && $existing['type'] === $type ? null : $type
]);
