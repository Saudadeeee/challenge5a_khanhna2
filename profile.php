<?php
session_start();
include('config.php');

// Lấy id của người dùng cần xem profile từ GET parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No user id provided.");
}
$profile_user_id = intval($_GET['id']);

// Xử lý xóa tin nhắn nếu có submit từ form (delete_message)
$delete_message_feedback = '';
if (isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    // Lấy thông tin tin nhắn cần xóa
    $sql = "SELECT * FROM messages WHERE id = $message_id LIMIT 1";
    $result_msg = $conn->query($sql);
    if ($result_msg && $result_msg->num_rows > 0) {
        $msg = $result_msg->fetch_assoc();
        // Nếu người dùng đăng nhập và là người gửi hoặc người nhận của tin nhắn này, cho phép xóa
        $logged_in_id = isset($_SESSION['user']['id']) ? intval($_SESSION['user']['id']) : 0;
        if ($logged_in_id == $msg['sender_id'] || $logged_in_id == $msg['receiver_id']) {
            $sql_delete = "DELETE FROM messages WHERE id = $message_id";
            if ($conn->query($sql_delete)) {
                $delete_message_feedback = "Tin nhắn đã được xóa.";
            } else {
                $delete_message_feedback = "Lỗi khi xóa tin nhắn: " . $conn->error;
            }
        } else {
            $delete_message_feedback = "Bạn không có quyền xóa tin nhắn này.";
        }
    }
}

// Xử lý gửi tin nhắn (send_message)
$send_message_feedback = '';
if (isset($_POST['send_message'])) {
    // Người gửi là user đang đăng nhập
    if (!isset($_SESSION['user'])) {
        $send_message_feedback = "Bạn cần đăng nhập để gửi tin nhắn.";
    } else {
        $sender_id = $_SESSION['user']['id'];
        $receiver_id = $profile_user_id; // Chủ của profile này
        // Nội dung tin nhắn
        $content = $conn->real_escape_string($_POST['content']);
        
        $sql_insert = "INSERT INTO messages (sender_id, receiver_id, content) 
                       VALUES ($sender_id, $receiver_id, '$content')";
        if ($conn->query($sql_insert)) {
            $send_message_feedback = "Tin nhắn đã được gửi!";
        } else {
            $send_message_feedback = "Lỗi khi gửi tin nhắn: " . $conn->error;
        }
    }
}

// Lấy thông tin profile của người dùng từ bảng users
$sql_user = "SELECT * FROM users WHERE id = $profile_user_id LIMIT 1";
$result_user = $conn->query($sql_user);
if (!$result_user || $result_user->num_rows == 0) {
    die("User not found.");
}
$user = $result_user->fetch_assoc();

// Lấy danh sách tin nhắn đã nhận (messages có receiver_id = profile_user_id)
$sql_messages = "SELECT m.*, u.full_name AS sender_name 
                 FROM messages m 
                 JOIN users u ON m.sender_id = u.id 
                 WHERE m.receiver_id = $profile_user_id 
                 ORDER BY m.created_at DESC";
$result_messages = $conn->query($sql_messages);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ Sơ - <?php echo htmlspecialchars($user['full_name']); ?></title>
    <!-- Liên kết CSS chung -->
    <link rel="stylesheet" href="public/style.css">
    <!-- CSS riêng cho trang profile -->
    <style>
        .profile-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .profile-header img.avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
        }
        .profile-info {
            font-size: 16px;
        }
        .profile-info p {
            margin: 5px 0;
        }
        .edit-profile-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ffc107;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        .edit-profile-btn:hover {
            background: #e0a800;
        }
        .messages-section {
            margin-top: 30px;
        }
        .messages-section h2 {
            margin-bottom: 15px;
            font-size: 20px;
        }
        .message-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message-item:last-child {
            border-bottom: none;
        }
        .message-details {
            max-width: 80%;
        }
        .message-details p {
            margin: 3px 0;
        }
        .message-actions form {
            display: inline;
        }
        .message-actions input[type="submit"] {
            background: #dc3545;
            border: none;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .message-actions input[type="submit"]:hover {
            background: #c82333;
        }
        .message {
            background: #dff0d8; /* Xanh nhạt */
            border: 1px solid #c3e6cb;
            color: #3c763d;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: bold;
        }
        .message.error {
            background: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }
        /* Form gửi tin nhắn */
        .send-message-form {
            margin-top: 20px;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 4px;
            background: #fafafa;
        }
        .send-message-form label {
            font-weight: bold;
        }
        .send-message-form textarea {
            width: 100%;
            height: 60px;
            margin-top: 5px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            padding: 6px;
        }
        .send-message-form input[type="submit"] {
            background: #007bff;
            color: #fff;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .send-message-form input[type="submit"]:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Hồ Sơ Người Dùng</h1>
        <nav>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="profile.php?id=<?php echo $_SESSION['user']['id']; ?>">Hồ sơ của tôi</a></li>
                    <li><a href="logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="login.php">Đăng nhập</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <div class="profile-container">
        <!-- Nếu là chính user đang xem profile, có nút Edit -->
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $profile_user_id): ?>
            <a class="edit-profile-btn" href="edit_profile.php?id=<?php echo $profile_user_id; ?>">Edit</a>
        <?php endif; ?>
        
        <!-- Phần thông tin người dùng -->
        <div class="profile-header">
            <?php if (!empty($user['avatar'])): ?>
                <img class="avatar" src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar của <?php echo htmlspecialchars($user['full_name']); ?>">
            <?php else: ?>
                <img class="avatar" src="public/no-avatar.png" alt="No Avatar">
            <?php endif; ?>
            <div class="profile-info">
                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                <?php
                    $role_user = htmlspecialchars($user['role']);
                    if ($user['role'] == 'teacher') {
                        echo "<p><strong>Role:</strong> Teacher</p>";
                    }
                    else {
                        echo "<p><strong>Role:</strong> Student</p>";
                    }
                ?>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            </div>
        </div>
        
        <!-- Thông báo xóa tin nhắn -->
        <?php if (!empty($delete_message_feedback)): ?>
            <div class="message"><?php echo htmlspecialchars($delete_message_feedback); ?></div>
        <?php endif; ?>
        
        <!-- Thông báo gửi tin nhắn -->
        <?php if (!empty($send_message_feedback)): ?>
            <div class="message"><?php echo htmlspecialchars($send_message_feedback); ?></div>
        <?php endif; ?>

        <!-- Nếu người dùng đăng nhập và KHÔNG phải chủ profile, cho phép gửi tin nhắn -->
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] != $profile_user_id): ?>
        <div class="send-message-form">
            <form method="post">
                <label for="content">Gửi tin nhắn đến <?php echo htmlspecialchars($user['full_name']); ?>:</label>
                <textarea name="content" id="content" placeholder="Nhập nội dung tin nhắn..."></textarea>
                <input type="submit" name="send_message" value="Gửi tin nhắn">
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Phần hiển thị tin nhắn gửi đến người dùng này -->
        <div class="messages-section">
            <h2>Tin nhắn</h2>
            <?php if ($result_messages && $result_messages->num_rows > 0): ?>
                <?php while ($msg = $result_messages->fetch_assoc()): ?>
                    <div class="message-item">
                        <div class="message-details">
                            <p><strong>Người gửi:</strong> <?php echo htmlspecialchars($msg['sender_name']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($msg['content'])); ?></p>
                            <p><em>Gửi lúc: <?php echo htmlspecialchars($msg['created_at']); ?></em></p>
                        </div>
                        <div class="message-actions">
                            <?php
                            // Nếu người dùng đăng nhập và là người gửi hoặc người nhận tin nhắn, hiển thị nút xóa
                            $logged_in_id = isset($_SESSION['user']['id']) ? intval($_SESSION['user']['id']) : 0;
                            if ($logged_in_id == intval($msg['sender_id']) || $logged_in_id == intval($msg['receiver_id'])):
                            ?>
                                <form method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tin nhắn này?');">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <input type="submit" name="delete_message" value="Xóa">
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không có tin nhắn nào.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
