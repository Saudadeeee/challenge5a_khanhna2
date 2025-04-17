<?php
session_start();
include('config.php');

// Kiểm tra quyền giáo viên
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
if ($user['role'] != 'teacher' && $user['role'] != 'admin') {
    die("Truy cập bị từ chối! Chỉ giáo viên hoặc quản trị viên mới được phép xem bài nộp.");
}

// Lấy challenge_id từ GET
if (!isset($_GET['challenge_id'])) {
    die("Challenge ID is required.");
}
$challenge_id = intval($_GET['challenge_id']);

// Lấy thông tin các lần nộp bài của sinh viên cho challenge này
$sql = "SELECT ca.id, ca.student_id, ca.submitted_answer, ca.is_correct, u.full_name AS student_name
        FROM challenge_attempts ca
        JOIN users u ON ca.student_id = u.id
        WHERE ca.challenge_id = ?
        ORDER BY ca.submitted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $challenge_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $message = "Chưa có bài nộp nào cho challenge này.";
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Submissions for Challenge</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: #007bff;
            color: #fff;
            padding: 1rem;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
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
            <h1>Danh sách bài nộp cho Challenge ID: <?= htmlspecialchars($challenge_id) ?></h1>
            <nav>
                <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="profile.php?id=<?php echo $_SESSION['user']['id'] ?>">Hồ sơ</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
                </ul>
            </nav>
        </header>

        <?php if (isset($message)): ?>
            <div class="message <?= isset($message) && strpos($message, 'Chưa có') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Submitted Answer</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($result) && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['submitted_answer'])) ?></td>
                            <td>
                                <?= $row['is_correct'] ? 'Correct' : 'Incorrect' ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$stmt->close();
?>
