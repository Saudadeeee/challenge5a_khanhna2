<?php
session_start();
include('config.php');

// Kiểm tra quyền giáo viên
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
if ($user['role'] != 'teacher') {
    die("Truy cập bị từ chối!");
}

// Xử lý thêm challenge
$message = '';

// Xử lý xóa challenge
if (isset($_GET['delete_id'])) {
    $challenge_id = (int)$_GET['delete_id'];
    $sql = "DELETE FROM challenges WHERE id = $challenge_id AND teacher_id = {$user['id']}";
    if ($conn->query($sql)) {
        $message = "Xóa challenge thành công!";
    } else {
        $message = "Lỗi khi xóa: " . $conn->error;
    }
}

// Lấy danh sách challenge
$sql = "SELECT * FROM challenges WHERE teacher_id = {$user['id']}";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Challenge</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .add-button {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .action-link {
            color: #007bff;
            text-decoration: none;
            margin-right: 10px;
        }
        .action-link.delete {
            color: #dc3545;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Challenges</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="challenge_create.php" class="add-button">+ Thêm Challenge</a></li>
                    <li><a href="teacher_assignments.php">Quản lý bài tập</a></li>
                    <li><a href="manage_student.php">Quản lý SV</a></li>
                    <li><a href="logout.php">Đăng xuất</a></li>
                </ul>
            </nav>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'thành công') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gợi ý</th>
                    <th>File Path</th>
                    <th>Submissions</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars(substr($row['challenge_hint'], 0, 50)) . (strlen($row['challenge_hint']) > 50 ? '...' : '') ?></td>
                            <td><?= htmlspecialchars($row['file_path']) ?></td>
                            <td>
                                <a href="view_submissions.php?challenge_id=<?= $row['id'] ?>" 
                                class="action-link">
                                    Xem submissions (<?= get_submission_count($row['id']) ?>)
                                </a>
                            </td>
                            <td>
                                <a href="challenge_edit.php?challenge_id=<?= $row['id'] ?>" 
                                class="action-link">
                                    Sửa
                                </a>
                                <a href="?delete_id=<?= $row['id'] ?>" 
                                class="action-link delete" 
                                onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                    Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Chưa có challenge nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Hàm đếm số lượng submission
function get_submission_count($challenge_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM challenge_attempts WHERE challenge_id = " . intval($challenge_id);
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['count'];
    }
    return 0;
}
?>