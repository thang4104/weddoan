<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$videos = $conn->query("SELECT * FROM videos WHERE uploaded_by = '$email' ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ người dùng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="bg-white p-4 rounded shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="bi bi-person-circle me-2"></i>Hồ sơ cá nhân</h2>
                <a href="logout.php" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
            </div>

            <div class="row align-items-center mb-4">
                <div class="col-md-3 text-center">
                    <img src="<?= $user['avatar'] ? htmlspecialchars($user['avatar']) : 'uploads/avatars/default_avatar.png' ?>" alt="Avatar" class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                </div>
                <div class="col-md-9">
                    <h4 class="fw-semibold"><?= htmlspecialchars($user['name']) ?></h4>
                    <p class="mb-1"><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($user['email']) ?></p>
                    <?php if (!empty($user['birthdate'])): ?>
                        <p class="mb-1"><i class="bi bi-cake-fill"></i> <?= date('d/m/Y', strtotime($user['birthdate'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user['gender'])): ?>
                        <p class="mb-1"><i class="bi bi-gender-ambiguous"></i> <?= htmlspecialchars($user['gender']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user['profession'])): ?>
                        <p class="mb-1"><i class="bi bi-briefcase-fill"></i> <?= htmlspecialchars($user['profession']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user['bio'])): ?>
                        <p class="mb-0"><i class="bi bi-file-text-fill"></i> <?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                    <?php endif; ?>
                    <div class="mt-3">
                        <a href="edit_profile.php" class="btn btn-primary btn-sm me-2"><i class="bi bi-pencil-square"></i> Sửa thông tin</a>
                        <a href="change_password.php" class="btn btn-warning btn-sm"><i class="bi bi-key"></i> Đổi mật khẩu</a>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
                    </div>
                </div>
            </div>

            <hr>

            <h5 class="mb-3"><i class="bi bi-collection-play-fill"></i> Video đã tải lên</h5>
            <div class="row g-3">
                <?php if ($videos->num_rows > 0): ?>
                    <?php while ($video = $videos->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="play_video.php?video=<?= urlencode($video['file_path']) ?>" class="text-decoration-none text-primary">
                                            <?= htmlspecialchars($video['title']) ?>
                                        </a>
                                    </h6>
                                    <p class="card-text"><small class="text-muted"><i class="bi bi-calendar-event"></i> <?= $video['uploaded_at'] ?></small></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Bạn chưa tải lên video nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
