<?php
session_start();
include('config.php');

// Redirect to index if already logged in
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$success = false;

if (isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $role = 'student'; // Default role for new registrations
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $message = "Vui lòng điền đầy đủ thông tin bắt buộc.";
    } elseif ($password !== $confirm_password) {
        $message = "Mật khẩu xác nhận không khớp.";
    } else {
        // Check if username already exists
        $sql_check = "SELECT id FROM users WHERE username = '$username'";
        $result_check = $conn->query($sql_check);
        
        if ($result_check->num_rows > 0) {
            $message = "Tên đăng nhập đã tồn tại, vui lòng chọn tên khác.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (username, password, full_name, email, phone, role) 
                    VALUES ('$username', '$hashed_password', '$full_name', '$email', '$phone', '$role')";
            
            if ($conn->query($sql)) {
                $message = "Đăng ký thành công! Bạn có thể đăng nhập.";
                $success = true;
            } else {
                $message = "Lỗi: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .register-form label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .register-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .register-form input[type="submit"] {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            padding: 12px;
            font-size: 16px;
            margin-top: 10px;
        }
        
        .register-form input[type="submit"]:hover {
            background: #0056b3;
        }
        
        .message {
            margin: 20px 0;
            padding: 10px;
            border-radius: 4px;
        }
        
        .message.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .message.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
        
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Đăng Ký Tài Khoản</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="register-form">
            <label for="username">Tên đăng nhập <span class="required">*</span></label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Mật khẩu <span class="required">*</span></label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <label for="full_name">Họ và tên <span class="required">*</span></label>
            <input type="text" id="full_name" name="full_name" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email">
            
            <label for="phone">Số điện thoại</label>
            <input type="tel" id="phone" name="phone">
            
            <input type="submit" name="register" value="Đăng Ký">
        </form>
        
        <div class="footer">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
        </div>
    </div>
</body>
</html>
