<?php
session_start();
require_once 'config.php';

// XỬ LÝ ĐĂNG KÝ
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Kiểm tra email đã tồn tại chưa
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'Email đã được sử dụng';
        $_SESSION['active_form'] = 'register';
        header("Location: login_register.php");
        exit();
    }

    // Đăng ký: approved mặc định = 0
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, approved) VALUES (?, ?, ?, 'user', 0)");
    $stmt->bind_param("sss", $name, $email, $password);
    $stmt->execute();

    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'user';
    $_SESSION['approved'] = 0;

    $_SESSION['register_success'] = 'Đăng ký thành công, vui lòng chờ admin duyệt!';
    header("Location: login_register.php");
    exit();
}

// XỬ LÝ ĐĂNG NHẬP
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if ($user['approved'] != 1) {
                $_SESSION['login_error'] = 'Tài khoản của bạn chưa được admin duyệt.';
                $_SESSION['active_form'] = 'login';
                header("Location: login_register.php");
                exit();
            }

            // Đăng nhập thành công
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['approved'] = $user['approved'];

            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }
    }

    // Sai mật khẩu hoặc email
    $_SESSION['login_error'] = 'Sai email hoặc mật khẩu.';
    $_SESSION['active_form'] = 'login';
    header("Location: login_register.php");
    exit();
}
?>
