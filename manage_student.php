<?php
session_start();
include('config.php');

// Chỉ giáo viên mới được truy cập trang quản lý sinh viên
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    die("Access denied.");
}

$message = '';

// Xử lý xóa sinh viên nếu form được submit
if (isset($_POST['delete']) && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    $sql = "DELETE FROM users WHERE id = $student_id AND role = 'student'";
    if ($conn->query($sql)) {
        $message = "Sinh viên đã được xóa.";
    } else {
        $message = "Lỗi khi xóa sinh viên: " . $conn->error;
    }
}

// Truy vấn danh sách sinh viên (role = student)
$sql = "SELECT id, full_name, email FROM users WHERE role = 'student' ORDER BY full_name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sinh viên</title>
    <!-- Liên kết tới file CSS chung -->
    <link rel="stylesheet" href="public/style.css">
    <!-- CSS riêng cho trang manage_student -->
    <style>
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .student-table th,
        .student-table td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        .student-table th {
            background: #f2f2f2;
        }
        .action-buttons {
            text-align: center;
        }
        .action-buttons a,
        .action-buttons form {
            display: inline-block;
            margin-left: 5px;
        }
        .action-buttons form {
            margin: 0;
        }
        .action-buttons input[type="submit"] {
            background: #dc3545;
            border: none;
            padding: 5px 10px;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .action-buttons input[type="submit"]:hover {
            background: #c82333;
        }
        .action-buttons a.button {
            background: #28a745;
            padding: 5px 10px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .action-buttons a.button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Quản lý Sinh viên</h1>
        <nav>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="teacher_assignment_upload.php">Giao bài tập</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </header>
    
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <h2>Danh sách Sinh viên</h2>
    <table class="student-table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Profile</th>
            <th>Actions</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><a href="/profile.php?id=<?php echo $row['id']?>">Profile</a></td>
                <td class="action-buttons">
                    <!-- Nút Sửa: chuyển hướng sang trang sửa, truyền student_id qua GET -->
                    <a class="button" href="teacher_edit_student.php?student_id=<?php echo $row['id']; ?>">Sửa</a>
                    <!-- Nút Xóa: sử dụng form POST để xóa -->
                    <form method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?');">
                        <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                        <input type="submit" name="delete" value="Xóa">
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">Không có sinh viên nào.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
