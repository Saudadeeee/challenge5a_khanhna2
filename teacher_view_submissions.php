<?php
session_start();
include('config.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    die("Access denied.");
}

$teacher_id = $_SESSION['user']['id'];
$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

$submissions = null;
if ($assignment_id > 0) {
    $sql = "SELECT s.*, u.full_name as student_name 
            FROM submissions s 
            JOIN users u ON s.student_id = u.id 
            WHERE s.assignment_id = $assignment_id 
            ORDER BY s.submitted_at DESC";
    $submissions = $conn->query($sql);
}

$sql_assignments = "SELECT id, title FROM assignments WHERE teacher_id = $teacher_id ORDER BY created_at DESC";
$assignments_result = $conn->query($sql_assignments);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xem Bài làm của Sinh viên</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        .filter-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filter-form select {
            width: 80%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        .filter-form input[type="submit"] {
            padding: 8px 16px;
            background: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s;
        }
        .filter-form input[type="submit"]:hover {
            background: #0056b3;
        }
        .submissions-list {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .submission-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .submission-item:last-child {
            border-bottom: none;
        }
        .submission-item a.button {
            margin-top: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Xem Bài làm của Sinh viên</h1>
        <nav>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="teacher_assignment_upload.php">Giao bài tập</a></li>
                <li><a href="manage_student.php">Quản lý sinh viên</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="filter-form">
        <form method="get" action="teacher_view_submissions.php">
            <label for="assignment_id">Chọn bài tập:</label>
            <select name="assignment_id" id="assignment_id" required>
                <option value="">-- Chọn bài tập --</option>
                <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                    <option value="<?php echo $assignment['id']; ?>" <?php if ($assignment['id'] == $assignment_id) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($assignment['title']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="submit" value="Lọc">
        </form>
    </div>
    
    <?php if ($assignment_id > 0): ?>
        <div class="submissions-list">
            <?php if ($submissions && $submissions->num_rows > 0): ?>
                <?php while ($row = $submissions->fetch_assoc()): ?>
                    <div class="submission-item">
                        <strong>Student:</strong> <?php echo htmlspecialchars($row['student_name']); ?><br>
                        <strong>Submitted At:</strong> <?php echo htmlspecialchars($row['submitted_at']); ?><br>
                        <a class="button" href="<?php echo $row['file_path']; ?>" download>Tải bài làm</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Chưa có bài làm nào cho bài tập này.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
