<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}

$filterEmail = $_GET['filter_email'] ?? '';
$filterDate = $_GET['filter_date'] ?? '';

$query = "SELECT id, email, approved, created_at FROM users WHERE role = 'user'";
$conditions = [];

if ($filterEmail) {
    $conditions[] = "email LIKE '%$filterEmail%'";
}
if ($filterDate) {
    $conditions[] = "DATE(created_at) = '$filterDate'";
}
if ($conditions) {
    $query .= " AND " . implode(" AND ", $conditions);
}
$query .= " ORDER BY id DESC";
$result = $conn->query($query);

$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];
$approvedUsers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user' AND approved=1")->fetch_assoc()['total'];
$pendingUsers = $totalUsers - $approvedUsers;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Quản lý người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>🔧 Trang quản trị</h3>
        <div>
            👤 Xin chào, <?= $_SESSION['name'] ?? 'Admin' ?>
            <a href="logout.php" class="btn btn-sm btn-danger ms-2">Đăng xuất</a>
        </div>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success"> <?= $_SESSION['msg']; unset($_SESSION['msg']); ?> </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4" id="adminTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#users">Người dùng</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#reports">Video báo cáo</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#hidden">Video ẩn</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#stats">Thống kê</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="users">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-4">
                    <input type="text" name="filter_email" value="<?= htmlspecialchars($filterEmail) ?>" class="form-control" placeholder="Lọc theo email">
                </div>
                <div class="col-md-3">
                    <input type="date" name="filter_date" value="<?= htmlspecialchars($filterDate) ?>" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </div>
            </form>

            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td class="<?= $row['approved'] ? 'text-success' : 'text-danger' ?>">
                                <?= $row['approved'] ? 'Đã duyệt' : 'Chờ duyệt' ?>
                            </td>
                            <td>
                                <?php if (!$row['approved']): ?>
                                    <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-success">Phê duyệt</a>
                                <?php else: ?>
                                    <a href="?unapprove=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Thu hồi</a>
                                <?php endif; ?>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xác nhận xoá tài khoản?')">Xoá</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="reports">
            <h5>🚨 Video bị báo cáo</h5>
            <table class="table table-bordered">
                <thead><tr><th>Tiêu đề</th><th>Người đăng</th><th>Đường dẫn</th><th>Hành động</th></tr></thead>
                <tbody>
                    <?php
                    $reportResult = $conn->query("SELECT id, title, file_path, uploaded_by FROM videos WHERE is_reported = 1 AND is_hidden = 0");
                    while ($video = $reportResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($video['title']) ?></td>
                            <td><?= htmlspecialchars($video['uploaded_by']) ?></td>
                            <td><?= htmlspecialchars($video['file_path']) ?></td>
                            <td>
                                <a href="hide_video.php?id=<?= $video['id'] ?>" class="btn btn-sm btn-warning">Ẩn</a>
                                <a href="delete_video.php?id=<?= $video['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xoá video này?')">Xoá</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="hidden">
            <h5>🙈 Video đã bị ẩn</h5>
            <table class="table table-bordered">
                <thead><tr><th>Tiêu đề</th><th>Người đăng</th><th>Đường dẫn</th><th>Hành động</th></tr></thead>
                <tbody>
                    <?php
                    $hiddenVideos = $conn->query("SELECT id, title, file_path, uploaded_by FROM videos WHERE is_hidden = 1");
                    while ($video = $hiddenVideos->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($video['title']) ?></td>
                            <td><?= htmlspecialchars($video['uploaded_by']) ?></td>
                            <td><?= htmlspecialchars($video['file_path']) ?></td>
                            <td>
                                <a href="unhide_video.php?id=<?= $video['id'] ?>" class="btn btn-sm btn-success">Hiện lại</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="stats">
            <h5>📊 Thống kê người dùng</h5>
            <canvas id="userChart" width="400" height="200"></canvas>
            <script>
                const ctx = document.getElementById('userChart');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Đã duyệt', 'Chờ duyệt'],
                        datasets: [{
                            label: 'Người dùng',
                            data: [<?= $approvedUsers ?>, <?= $pendingUsers ?>],
                            backgroundColor: ['#28a745', '#ffc107'],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'top' } }
                    }
                });
            </script>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
