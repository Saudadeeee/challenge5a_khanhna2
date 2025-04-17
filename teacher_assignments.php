<?php
session_start();
include('config.php');

// Chỉ giáo viên mới được truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    die("Access denied.");
}

$message = '';

// Nếu có yêu cầu xóa bài tập
if (isset($_POST['delete']) && isset($_POST['assignment_id'])) {
    $assignment_id = intval($_POST['assignment_id']);
    $teacher_id = $_SESSION['user']['id'];
    // Xóa bài tập chỉ khi đúng teacher_id
    $sql = "DELETE FROM assignments WHERE id = $assignment_id AND teacher_id = $teacher_id";
    if ($conn->query($sql)) {
        $message = "Đã xóa bài tập thành công.";
    } else {
        $message = "Lỗi khi xóa bài tập: " . $conn->error;
    }
}

// Lấy danh sách bài tập của giáo viên
$teacher_id = $_SESSION['user']['id'];
$sql = "SELECT * FROM assignments WHERE teacher_id = $teacher_id ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Bài Tập</title>
    <!-- Liên kết CSS chung -->
    <link rel="stylesheet" href="public/style.css">
    <!-- CSS riêng (nếu cần) -->
    <style>
        .assignment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .assignment-table th,
        .assignment-table td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        .assignment-table th {
            background: #f2f2f2;
        }
        .action-cell {
            white-space: nowrap; 
        }
        .action-cell form {
            display: inline-block;
            margin: 0;
        }
        .action-cell form input[type="submit"] {
            background: #dc3545;
            border: none;
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
        }
        .action-cell form input[type="submit"]:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Bài Tập Đã Giao</h1>
        <nav>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="teacher_assignment_upload.php">Giao bài tập mới</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </header>
    
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <h2>Danh sách Bài Tập của Bạn</h2>
    <table class="assignment-table">
        <thead>
            <tr>
                <th>Tiêu đề</th>
                <th>Mô tả</th>
                <th>Deadline</th>
                <th>File</th>
                <th>Thao tác</th>
                <th>Submissions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo htmlspecialchars($row['deadline']); ?></td>
                    <td>
                        <?php if (!empty($row['file_path'])): ?>
                            <a class="button" href="<?php echo $row['file_path']; ?>" download>Tải về</a>
                        <?php else: ?>
                            <em>Không có file</em>
                        <?php endif; ?>
                    </td>
                    <td class="action-cell">
                        
                        <a class="button" 
                           href="teacher_edit_assignment.php?assignment_id=<?php echo $row['id']; ?>">
                           Sửa
                        </a>
                        
                        <form method="post" 
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài tập này?');">
                            <input type="hidden" name="assignment_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" name="delete" value="Xóa">
                        </form>
                    </td>
                    <td>
                        <a class="button" href="/teacher_view_submissions.php?assignment_id=<?php echo $row['id'] ?>">Xem bai lam</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Chưa có bài tập nào.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
