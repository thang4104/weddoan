<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

if (isset($_POST['video_path'])) {
    $videoPath = $_POST['video_path'];
    $stmt = $conn->prepare("UPDATE videos SET is_reported = 1 WHERE file_path = ?");
    $stmt->bind_param("s", $videoPath);
    $stmt->execute();
    $stmt->close();
}

header("Location: play_video.php?video=" . urlencode($videoPath));
exit();
?>
