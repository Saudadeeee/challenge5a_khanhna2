<?php
session_start();
include('config.php');

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Lấy thông tin người dùng từ database
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
    die("User not found.");
}
$user_data = $result->fetch_assoc();

$message = '';
$message_type = '';

// Xử lý form cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sinh viên không được thay đổi tên đăng nhập và họ tên
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    // Xử lý upload avatar
    $avatar_path = $user_data['avatar']; // Giữ nguyên avatar cũ nếu không upload ảnh mới
    
    // Kiểm tra có file avatar được upload không
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $upload_dir = 'uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_file_name = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Xóa avatar cũ nếu có
                if (!empty($avatar_path) && file_exists($avatar_path)) {
                    unlink($avatar_path);
                }
                $avatar_path = $upload_path;
            } else {
                $message = "Không thể upload avatar.";
                $message_type = "error";
            }
        } else {
            $message = "Chỉ cho phép upload file hình ảnh (jpg, jpeg, png, gif).";
            $message_type = "error";
        }
    }
    
    // Kiểm tra có URL avatar không
    if (isset($_POST['avatar_url']) && !empty($_POST['avatar_url'])) {
        $avatar_url = $_POST['avatar_url'];
        // Kiểm tra URL hợp lệ
        if (filter_var($avatar_url, FILTER_VALIDATE_URL)) {
            $avatar_path = $avatar_url;
        } else {
            $message = "URL avatar không hợp lệ.";
            $message_type = "error";
        }
    }
    
    // Cập nhật thông tin vào database
    if (empty($message)) {
        $sql = "UPDATE users SET email='$email', phone='$phone', avatar='$avatar_path' WHERE id=$user_id";
        if ($conn->query($sql)) {
            $message = "Cập nhật thông tin thành công!";
            $message_type = "success";
            
            // Cập nhật lại thông tin SESSION
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            
            // Refresh lại dữ liệu
            $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
            $user_data = $result->fetch_assoc();
        } else {
            $message = "Lỗi khi cập nhật: " . $conn->error;
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa hồ sơ</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        .profile-edit-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .profile-edit-form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="url"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group input[disabled] {
            background: #f9f9f9;
            cursor: not-allowed;
        }
        .avatar-preview {
            margin-top: 20px;
            text-align: center;
        }
        .avatar-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            border: 2px solid #007bff;
        }
        .submit-button {
            display: block;
            width: 150px;
            margin: 20px auto;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .submit-button:hover {
            background: #0056b3;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .or-separator {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Chỉnh sửa hồ sơ</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="profile.php?id=<?php echo $user_id; ?>">Xem hồ sơ</a></li>
                    <li><a href="logout.php">Đăng xuất</a></li>
                </ul>
            </nav>
        </header>
        
        <div class="profile-edit-container">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="avatar-preview">
                <?php if (!empty($user_data['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($user_data['avatar']); ?>" alt="Avatar">
                <?php else: ?>
                    <img src="public/no-avatar.png" alt="No Avatar">
                <?php endif; ?>
            </div>
            
            <form class="profile-edit-form" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                    <small>Tên đăng nhập không thể thay đổi</small>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Họ và tên:</label>
                    <input type="text" id="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" disabled>
                    <small>Họ và tên không thể thay đổi</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="avatar">Avatar (tải lên từ file):</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*">
                </div>
                
                <div class="or-separator">HOẶC</div>
                
                <div class="form-group">
                    <label for="avatar_url">Avatar (từ URL):</label>
                    <input type="url" id="avatar_url" name="avatar_url" placeholder="https://example.com/avatar.jpg">
                </div>
                
                <button type="submit" class="submit-button">Cập nhật</button>
            </form>
        </div>
    </div>
</body>
</html>
