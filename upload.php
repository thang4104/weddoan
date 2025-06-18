<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['video'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $uploaded_by = $_SESSION['email'];

    $file_name = basename($_FILES['video']['name']);
    $target_dir = "uploads/";
    $target_file = $target_dir . time() . "_" . $file_name;

    if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO videos (file_path, title, description, uploaded_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $target_file, $title, $description, $uploaded_by);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    } else {
        $error = "❌ Tải lên thất bại.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tải Video</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 25px;
        }

        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        .submit-btn {
            background-color: #28a745;
            color: white;
        }

        .cancel-btn {
            background-color: #dc3545;
            color: white;
            margin-left: 10px;
        }

        .buttons {
            display: flex;
            justify-content: center;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📤 Tải video mới</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Tiêu đề video" required>
        <textarea name="description" rows="4" placeholder="Mô tả video..."></textarea>
        <input type="file" name="video" accept="video/mp4" required>

        <div class="buttons">
            <button type="submit" class="submit-btn">✅ Tải lên</button>
            <button type="button" class="cancel-btn" onclick="window.location.href='index.php'">❌ Huỷ</button>
        </div>
    </form>

    <div class="back-link">
        
    </div>
</div>

</body>
</html>
