<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("
    SELECT vh.watched_at, v.title, v.file_path
    FROM watch_history vh
    JOIN videos v ON vh.video_id = v.id
    WHERE vh.user_email = ?
    ORDER BY vh.watched_at DESC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$history = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử xem</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .video-card {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            padding: 15px;
            transition: background-color 0.2s;
            position: relative;
        }

        .video-card:hover {
            background-color: #f1f1f1;
        }

        .video-thumbnail {
            width: 180px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            background-color: #000;
        }

        .video-info {
            flex-grow: 1;
        }

        .video-info a {
            font-size: 16px;
            font-weight: 600;
            color: #0d6efd;
            text-decoration: none;
        }

        .video-info a:hover {
            text-decoration: underline;
        }

        .watched-time {
            font-size: 14px;
            color: #666;
        }

        .delete-btn {
            color: #dc3545;
            background: none;
            border: none;
            font-size: 20px;
        }

        .delete-btn:hover {
            color: #bd2130;
        }

        @media (max-width: 576px) {
            .video-card {
                flex-direction: column;
                text-align: center;
            }

            .video-thumbnail {
                width: 100%;
                height: 180px;
            }

            .delete-btn {
                position: absolute;
                top: 10px;
                right: 15px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-house-door"></i> Trang chủ
        </a>
        <span class="navbar-text ms-auto fw-semibold">Lịch sử xem</span>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <h3 class="mb-4 text-center"><i class="bi bi-clock-history"></i> Lịch sử xem gần đây</h3>

    <?php if ($history->num_rows > 0): ?>
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-outline-danger btn-sm" onclick="deleteAllHistory()">
                🧹 Xóa toàn bộ lịch sử
            </button>
        </div>

        <div id="history-list" class="vstack gap-3">
            <?php while ($row = $history->fetch_assoc()): ?>
                <div class="video-card" data-file="<?= htmlspecialchars($row['file_path']) ?>">
                    <a href="play_video.php?video=<?= urlencode($row['file_path']) ?>">
                        <video class="video-thumbnail"
                            muted
                            loop
                            preload="metadata"
                            onmouseover="this.play()"
                            onmouseout="this.pause(); this.currentTime = 0;">
                            <source src="uploads/<?= htmlspecialchars($row['file_path']) ?>" type="video/mp4">
                        </video>
                    </a>
                    <div class="video-info">
                        <a href="play_video.php?video=<?= urlencode($row['file_path']) ?>">
                            <?= htmlspecialchars($row['title']) ?>
                        </a>
                        <div class="watched-time mt-1">
                            <i class="bi bi-clock"></i> <?= date("H:i d/m/Y", strtotime($row['watched_at'])) ?>
                        </div>
                    </div>
                    <button class="delete-btn" title="Xóa video này" onclick="deleteItem(this)">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            Bạn chưa xem video nào.
        </div>
    <?php endif; ?>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
function deleteItem(btn) {
    const card = btn.closest('.video-card');
    const filePath = card.getAttribute('data-file');

    if (confirm("Bạn có chắc chắn muốn xóa video này khỏi lịch sử?")) {
        fetch('delete_history_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'file_path=' + encodeURIComponent(filePath)
        })
        .then(res => res.text())
        .then(msg => {
            if (msg.includes("Đã xóa")) card.remove();
            else alert("Lỗi: " + msg);
        });
    }
}

function deleteAllHistory() {
    if (!confirm("Bạn có chắc chắn muốn xóa toàn bộ lịch sử xem?")) return;

    fetch('delete_all_history.php', { method: 'POST' })
    .then(res => res.text())
    .then(msg => {
        if (msg.includes("Đã xóa")) {
            document.getElementById('history-list').innerHTML = `
                <div class="alert alert-success text-center">Đã xóa toàn bộ lịch sử.</div>
            `;
        } else {
            alert("Lỗi: " + msg);
        }
    });
}
</script>
</body>
</html>
