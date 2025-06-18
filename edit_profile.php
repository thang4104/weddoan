<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

$email = $_SESSION['email'];
$message = "";

// Xử lý xóa avatar
if (isset($_POST['delete_avatar'])) {
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!empty($result['avatar']) && file_exists($result['avatar']) && strpos($result['avatar'], 'default_avatar.png') === false) {
        unlink($result['avatar']);
    }

    $stmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    $message = "🗑️ Ảnh đại diện đã được xoá.";
}

// Cập nhật hồ sơ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && !isset($_POST['delete_avatar'])) {
    $newName = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $profession = trim($_POST['profession']);

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newFileName = "uploads/avatars/" . time() . "_avatar." . $ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], $newFileName);

        $stmt = $conn->prepare("UPDATE users SET name = ?, bio = ?, avatar = ?, birthdate = ?, gender = ?, profession = ? WHERE email = ?");
        $stmt->bind_param("sssssss", $newName, $bio, $newFileName, $birthdate, $gender, $profession, $email);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, bio = ?, birthdate = ?, gender = ?, profession = ? WHERE email = ?");
        $stmt->bind_param("ssssss", $newName, $bio, $birthdate, $gender, $profession, $email);
    }

    $stmt->execute();
    $stmt->close();

    $_SESSION['name'] = $newName;
    $message = "✅ Cập nhật hồ sơ thành công.";
}

// Lấy thông tin
$stmt = $conn->prepare("SELECT name, bio, avatar, birthdate, gender, profession FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa hồ sơ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            font-family: 'Segoe UI', sans-serif;
        }
        .profile-card {
            max-width: 720px;
            margin: auto;
            background-color: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        .avatar-container {
            text-align: center;
        }
        .avatar-preview {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #dee2e6;
            transition: 0.3s ease;
        }
        .avatar-preview:hover {
            box-shadow: 0 0 12px #0d6efd;
            transform: scale(1.02);
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="profile-card">
        <h3 class="text-center mb-4">👤 Cập nhật hồ sơ cá nhân</h3>

        <?php if ($message): ?>
            <div class="alert alert-info text-center"><?= $message ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="avatar-container mb-4">
                <img src="<?= $user['avatar'] ? htmlspecialchars($user['avatar']) : 'uploads/avatars/default_avatar.png' ?>" id="avatarPreview" class="avatar-preview mb-2">
                <div class="mt-2">
                    <input type="file" name="avatar" class="form-control" accept="image/*" onchange="previewAvatar(this)">
                </div>
                <?php if (!empty($user['avatar']) && strpos($user['avatar'], 'default_avatar.png') === false): ?>
                    <div class="mt-2">
                        <button type="submit" name="delete_avatar" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xoá ảnh đại diện?')">🗑️ Xoá ảnh đại diện</button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="name" class="form-label">📛 Họ và tên</label>
                    <input type="text" class="form-control" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="birthdate" class="form-label">🎂 Ngày sinh</label>
                    <input type="date" class="form-control" name="birthdate" value="<?= $user['birthdate'] ?>">
                </div>
            </div>

            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="gender" class="form-label">🚻 Giới tính</label>
                    <select class="form-select" name="gender">
                        <option value="Nam" <?= $user['gender'] == 'Nam' ? 'selected' : '' ?>>Nam</option>
                        <option value="Nữ" <?= $user['gender'] == 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                        <option value="Khác" <?= $user['gender'] == 'Khác' ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="profession" class="form-label">💼 Nghề nghiệp</label>
                    <input type="text" class="form-control" name="profession" value="<?= htmlspecialchars($user['profession']) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="bio" class="form-label">📝 Giới thiệu bản thân</label>
                <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="profile.php" class="btn btn-secondary">⬅ Về trang cá nhân</a>
                <button type="submit" class="btn btn-primary">💾 Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewAvatar(input) {
        const file = input.files[0];
        if (file) {
            const preview = document.getElementById("avatarPreview");
            preview.src = URL.createObjectURL(file);
        }
    }
</script>
</body>
</html>
