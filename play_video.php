<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

if (!isset($_GET['video'])) {
    echo "Không có video để phát.";
    exit;
}

$videoFile = $_GET['video'];
$videoPath = realpath($videoFile);
$uploadsPath = realpath('uploads');
if (strpos($videoPath, $uploadsPath) !== 0 || !file_exists($videoPath)) {
    echo "Không tìm thấy video.";
    exit;
}

$stmt = $conn->prepare("SELECT id, title FROM videos WHERE file_path = ?");
$stmt->bind_param("s", $videoFile);
$stmt->execute();
$videoInfo = $stmt->get_result()->fetch_assoc();
$videoId = $videoInfo['id'];
$title = $videoInfo['title'];
$stmt->close();

$userEmail = $_SESSION['email'];
$stmt = $conn->prepare("INSERT INTO watch_history (user_email, video_id) VALUES (?, ?)");
$stmt->bind_param("si", $userEmail, $videoId);
$stmt->execute();
$stmt->close();

$conn->query("UPDATE videos SET view_count = view_count + 1 WHERE id = $videoId");

$stmt = $conn->prepare("SELECT view_count FROM videos WHERE id = ?");
$stmt->bind_param("i", $videoId);
$stmt->execute();
$stmt->bind_result($views);
$stmt->fetch();
$stmt->close();

$playlist = $conn->query("SELECT id, title, file_path FROM videos ORDER BY id ASC");
$playlistItems = [];
$currentIndex = 0;
$i = 0;
while ($row = $playlist->fetch_assoc()) {
    $playlistItems[] = $row;
    if ($row['file_path'] === $videoFile) {
        $currentIndex = $i;
    }
    $i++;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    $userName = $_SESSION['name'];
    $stmt = $conn->prepare("INSERT INTO comments (video_id, user_name, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $videoId, $userName, $comment);
    $stmt->execute();
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM comments WHERE video_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $videoId);
$stmt->execute();
$comments = $stmt->get_result();

$stmt = $conn->prepare("SELECT SUM(type = 'like'), SUM(type = 'dislike') FROM likes WHERE video_id = ?");
$stmt->bind_param("i", $videoId);
$stmt->execute();
$stmt->bind_result($likeCount, $dislikeCount);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT type FROM likes WHERE user_email = ? AND video_id = ?");
$stmt->bind_param("si", $_SESSION['email'], $videoId);
$stmt->execute();
$result = $stmt->get_result();
$userReaction = $result->fetch_assoc()['type'] ?? null;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body.dark-mode { background-color: #121212; color: white; }
        .video-container { position: relative; padding-top: 56.25%; }
        .video-container video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 10px; }
        .controls { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 1rem; }
        .playlist-video { height: 100px; object-fit: cover; border-radius: 5px; }
        .card .card-body { padding: 0.5rem; }
        .card-title { font-size: 0.9rem; line-height: 1.2; }
        .card { border: 1px solid #ddd; transition: transform 0.2s; }
        .card:hover { transform: scale(1.02); }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="index.php" class="btn btn-outline-secondary">⬅ Quay lại</a>
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">☰</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="profile.php">👤 Hồ sơ</a></li>
                <li><a class="dropdown-item" href="watch_history.php">🕒 Lịch sử xem</a></li>
                <li><a class="dropdown-item" href="logout.php" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">🔒 Đăng xuất</a></li>
            </ul>
        </div>
    </div>

    <h2 class="text-center mb-4">🎬 <?= htmlspecialchars($title) ?></h2>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body bg-dark">
                    <div class="video-container">
                        <video id="videoPlayer" controls autoplay onended="playNext()">
                            <source src="<?= htmlspecialchars($videoFile) ?>" type="video/mp4">
                        </video>
                    </div>
                    <div class="controls">
                        <button onclick="togglePlay()" class="btn btn-primary">▶️ / ⏸</button>
                        <button onclick="rewind(10)" class="btn btn-secondary">⏪ 10s</button>
                        <input type="range" id="seekBar" step="1" value="0">
                        <button onclick="forward(10)" class="btn btn-secondary">⏩ 10s</button>
                        <button onclick="toggleMute()" class="btn btn-warning">🔇 / 🔊</button>
                        <input type="range" id="volumeBar" min="0" max="1" step="0.05" value="1">
                        <button onclick="toggleFullscreen()" class="btn btn-outline-dark"><i class="bi bi-arrows-fullscreen"></i></button>
                        <select id="aspectRatioSelect" onchange="changeAspectRatio()" class="form-select w-auto">
                            <option value="16/9">16:9</option>
                            <option value="4/3">4:3</option>
                            <option value="1">1:1</option>
                        </select>
                        <select id="speedSelect" onchange="changeSpeed()" class="form-select w-auto">
                            <option value="0.5">0.5x</option>
                            <option value="1" selected>1x</option>
                            <option value="1.5">1.5x</option>
                            <option value="2">2x</option>
                        </select>
                        <button onclick="toggleDarkMode()" class="btn btn-dark">🌙</button>
                    </div>
                </div>
            </div>

            <div class="my-3">
                <strong>👁️ <?= $views ?> lượt xem</strong>
                <button id="likeBtn" class="btn btn-outline-success <?= $userReaction === 'like' ? 'active' : '' ?>">👍 <span id="likeCount"><?= $likeCount ?></span></button>
                <button id="dislikeBtn" class="btn btn-outline-danger <?= $userReaction === 'dislike' ? 'active' : '' ?>">👎 <span id="dislikeCount"><?= $dislikeCount ?></span></button>
                <form action="report_video.php" method="POST" class="d-inline-block ms-2">
                    <input type="hidden" name="video_path" value="<?= htmlspecialchars($videoFile) ?>">
                    <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Bạn có chắc muốn báo cáo video này?')">🚩 Báo cáo</button>
                </form>
            </div>

            <h4>💬 Bình luận</h4>
            <form method="post">
                <textarea name="comment" class="form-control mb-2" placeholder="Viết bình luận..." required></textarea>
                <button type="submit" class="btn btn-primary">Gửi</button>
            </form>
            <ul class="list-group mt-3">
                <?php while ($cmt = $comments->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($cmt['user_name']) ?>:</strong>
                        <?= nl2br(htmlspecialchars($cmt['content'])) ?>
                        <br><small class="text-muted"><?= $cmt['created_at'] ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="col-lg-4">
            <h5>Danh sách phát</h5>
            <div class="list-group">
                <?php foreach ($playlistItems as $index => $video): ?>
                <a href="play_video.php?video=<?= urlencode($video['file_path']) ?>" class="list-group-item list-group-item-action <?= $index == $currentIndex ? 'active' : '' ?>">
                    <div class="d-flex">
                        <div style="width: 120px;" class="me-2">
                            <video class="playlist-video" muted autoplay loop playsinline preload="metadata">
                                <source src="<?= htmlspecialchars($video['file_path']) ?>" type="video/mp4">
                            </video>
                        </div>
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($video['title']) ?></strong>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
const video = document.getElementById("videoPlayer");
const seekBar = document.getElementById("seekBar");
const volumeBar = document.getElementById("volumeBar");
const playlist = <?= json_encode(array_column($playlistItems, 'file_path')) ?>;
let currentIndex = <?= $currentIndex ?>;

function playNext() {
    if (currentIndex < playlist.length - 1) {
        window.location.href = "play_video.php?video=" + encodeURIComponent(playlist[currentIndex + 1]);
    }
}

function togglePlay() { video.paused ? video.play() : video.pause(); }
function rewind(sec) { video.currentTime = Math.max(0, video.currentTime - sec); }
function forward(sec) { video.currentTime = Math.min(video.duration, video.currentTime + sec); }
function toggleMute() { video.muted = !video.muted; }
function toggleFullscreen() {
    if (video.requestFullscreen) video.requestFullscreen();
    else if (video.webkitRequestFullscreen) video.webkitRequestFullscreen();
}
function changeAspectRatio() {
    const [w, h] = document.getElementById("aspectRatioSelect").value.split('/').map(Number);
    video.style.height = (video.offsetWidth * h / w) + 'px';
}
function changeSpeed() {
    video.playbackRate = parseFloat(document.getElementById("speedSelect").value);
}
function toggleDarkMode() {
    document.body.classList.toggle("dark-mode");
}
video.addEventListener("loadedmetadata", () => { seekBar.max = video.duration; });
video.addEventListener("timeupdate", () => { seekBar.value = video.currentTime; });
seekBar.addEventListener("input", () => { video.currentTime = seekBar.value; });
volumeBar.addEventListener("input", () => { video.volume = volumeBar.value; });

document.getElementById("likeBtn").onclick = () => sendReaction('like');
document.getElementById("dislikeBtn").onclick = () => sendReaction('dislike');

function sendReaction(type) {
    fetch("toggle_like.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `video_id=<?= $videoId ?>&type=${type}`
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("likeCount").textContent = data.likeCount;
        document.getElementById("dislikeCount").textContent = data.dislikeCount;
        document.getElementById("likeBtn").classList.toggle("active", data.userReaction === 'like');
        document.getElementById("dislikeBtn").classList.toggle("active", data.userReaction === 'dislike');
    });
}
</script>
</body>
</html>
