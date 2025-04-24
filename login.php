<?php
session_start();
include('config.php');

// Check if database connection exists
if (!isset($conn) || $conn === null) {
    die("Database connection failed: Please check your config.php file");
}

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$message = '';

if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $is_verified = password_verify($password, $user['password']); 
        
        if ($is_verified) {
            $_SESSION['user'] = [
                'id'        => $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
                'phone'     => $user['phone'],
                'role'      => $user['role']
            ];
            header('Location: index.php');
            exit;
        } else {
            $message = "Mật khẩu không đúng!";
        }
    } else {
        $message = "Tài khoản không tồn tại!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <!-- Liên kết tới file CSS bên ngoài -->
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Đăng Nhập</h2>
        <form method="post" class="login-form">
            <label for="username">Tên đăng nhập</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" name="login" value="Đăng nhập">
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
        </form>
        <div class="footer">
            <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
        </div>
    </div>
</body>
</html>
