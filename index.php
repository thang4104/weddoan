<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}
require 'config.php';
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Video</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <style>
        .video-card {
            transition: 0.3s ease;
        }
        .video-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .video-title {
            font-size: 16px;
            font-weight: 600;
            color: #0d6efd;
            text-decoration: none;
        }
        .video-title:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">🎬 Xin chào, <?= htmlspecialchars($_SESSION['name']) ?>!</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarItems">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarItems">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a href="upload.php" class="nav-link">📤 Tải video</a></li>
                <li class="nav-item"><a href="watch_history.php" class="nav-link">🕒 Lịch sử xem</a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link">👤 Hồ sơ</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-danger">🚪 Đăng xuất</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">

    <!-- Thanh tìm kiếm -->
    <form class="input-group mb-4" method="get" action="search.php">
        <input type="text" class="form-control" name="q" placeholder="🔍 Tìm kiếm video..." required>
        <button class="btn btn-outline-primary" type="submit">Tìm</button>
    </form>

    <h2 class="mb-3 fw-bold text-primary">📺 Danh sách video</h2>

    <div class="row g-4">
        <?php
        $stmt = $conn->prepare("
            SELECT videos.file_path, videos.title, users.name AS uploader_name
            FROM videos
            JOIN users ON videos.uploaded_by = users.email
            WHERE videos.is_hidden = 0
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()):
        ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card video-card h-100">
                <div class="ratio ratio-16x9">
                    <video src="<?= htmlspecialchars($row['file_path']) ?>#t=5" class="rounded-top" muted autoplay loop></video>
                </div>
                <div class="card-body">
                    <a class="video-title" href="play_video.php?video=<?= urlencode($row['file_path']) ?>">
                        <?= htmlspecialchars($row['title']) ?>
                    </a>
                    <p class="text-muted small mt-1 mb-0">👤 Người đăng: <?= htmlspecialchars($row['uploader_name']) ?></p>
                </div>
            </div>
        </div>
        <?php endwhile; $stmt->close(); ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
