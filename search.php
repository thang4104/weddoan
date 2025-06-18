<?php
require 'config.php';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM videos WHERE title LIKE ? OR description LIKE ?");
    $keyword = "%" . $search . "%";
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả cho "<?= htmlspecialchars($search) ?>"</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f5f5f5;
            color: #333;
        }

        h2 {
            margin-bottom: 20px;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        input[type="text"] {
            padding: 10px;
            width: 300px;
            border-radius: 8px;
            border: 1px solid #ccc;
            flex-grow: 1;
        }

        button {
            padding: 10px 16px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #004d99;
        }

        .video-results {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .video-card {
            width: 300px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .video-card:hover {
            transform: scale(1.02);
        }

        .video-thumb {
            width: 100%;
            height: 170px;
            background: #000;
        }

        .video-thumb video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-info {
            padding: 10px;
        }

        .video-info h4 {
            margin: 0;
            font-size: 16px;
            color: #0066cc;
        }

        .video-info p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666;
        }

        .back-link {
            margin-top: 30px;
            display: inline-block;
            color: #0066cc;
        }
    </style>
</head>
<body>

<h2>🔍 Kết quả tìm kiếm cho: "<?= htmlspecialchars($search) ?>"</h2>

<form class="search-bar" method="get" action="search.php">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm video..." required>
    <button type="submit"><i class="bi bi-search"></i> Tìm</button>
</form>

<?php if ($search !== ''): ?>
    <p>Có <strong><?= $results->num_rows ?></strong> kết quả:</p>
    <div class="video-results">
        <?php while ($row = $results->fetch_assoc()): ?>
            <div class="video-card">
                <div class="video-thumb">
                    <video src="<?= htmlspecialchars($row['file_path']) ?>#t=5" muted></video>
                </div>
                <div class="video-info">
                    <h4><a href="play_video.php?video=<?= urlencode($row['file_path']) ?>"><?= htmlspecialchars($row['title']) ?></a></h4>
                    <p><?= nl2br(htmlspecialchars(mb_strimwidth($row['description'], 0, 80, '...'))) ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<a class="back-link" href="index.php">⬅ Quay lại</a>

</body>
</html>
