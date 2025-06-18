<?php
session_start();
require 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login_register.php");
    exit();
}

$email = $_SESSION['email'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "❌ Mật khẩu xác minh không khớp.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (password_verify($current, $result['password'])) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed, $email);
            $stmt->execute();
            $stmt->close();
            $message = "✅ Đổi mật khẩu thành công.";
        } else {
            $message = "❌ Mật khẩu hiện tại không chính xác.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f0f2f5;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-weight: 600;
        }

        .toggle-password {
            background: none;
            border: none;
        }

        #password-strength {
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 5px;
        }

        .strength-weak {
            color: #dc3545;
        }

        .strength-medium {
            color: #ffc107;
        }

        .strength-strong {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card mx-auto p-4" style="max-width: 500px;">
            <h3 class="card-title text-center mb-4">🔐 Đổi mật khẩu</h3>

            <?php if (!empty($message)): ?>
                <div class="alert <?= str_contains($message, '✅') ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="post" onsubmit="return validatePasswords()">
                <div class="mb-3">
                    <label class="form-label">🔑 Mật khẩu hiện tại</label>
                    <div class="input-group">
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                        <button class="btn toggle-password" type="button" data-target="current_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">🆕 Mật khẩu mới</label>
                    <div class="input-group">
                        <input type="password" id="new_password" name="new_password" class="form-control" required oninput="checkPasswordStrength(this.value)">
                        <button class="btn toggle-password" type="button" data-target="new_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="password-strength"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">✅ Xác minh mật khẩu mới</label>
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        <button class="btn toggle-password" type="button" data-target="confirm_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="passwordMatchError" class="text-danger mt-1" style="display:none;">❗ Mật khẩu không khớp.</div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="profile.php" class="btn btn-secondary">⬅ Quay lại hồ sơ</a>
                    <button type="submit" class="btn btn-primary">🔁 Đổi mật khẩu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validatePasswords() {
            const pass = document.getElementById("new_password").value;
            const confirm = document.getElementById("confirm_password").value;
            const error = document.getElementById("passwordMatchError");

            if (pass !== confirm) {
                error.style.display = "block";
                return false;
            }
            error.style.display = "none";
            return true;
        }

        // Toggle hiển thị mật khẩu
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = button.querySelector('i');
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = "password";
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });

        // Đánh giá độ mạnh của mật khẩu
        function checkPasswordStrength(password) {
            const strengthText = document.getElementById("password-strength");
            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            if (password.length === 0) {
                strengthText.textContent = '';
                strengthText.className = '';
            } else if (strength <= 2) {
                strengthText.textContent = '❗ Mật khẩu yếu';
                strengthText.className = 'strength-weak';
            } else if (strength === 3 || strength === 4) {
                strengthText.textContent = '⚠️ Mật khẩu trung bình';
                strengthText.className = 'strength-medium';
            } else {
                strengthText.textContent = '✅ Mật khẩu mạnh';
                strengthText.className = 'strength-strong';
            }
        }
    </script>
</body>
</html>
