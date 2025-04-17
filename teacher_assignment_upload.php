<?php
session_start();
include('config.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    die("Access denied.");
}

$message = '';

if (isset($_POST['upload_assignment'])) {
    $teacher_id = $_SESSION['user']['id'];
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $deadline = $_POST['deadline'];
    
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $upload_dir = 'uploads/assignments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $original_filename = basename($_FILES['assignment_file']['name']);
        $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        // Chỉ cho phép file txt và pdf
        $allowed_ext = array('txt', 'pdf');
        
        if (!in_array($ext, $allowed_ext)) {
            $message = "Chỉ cho phép upload file txt hoặc pdf.";
        } else {
            // Đổi tên file thành dạng UID
            $new_filename = uniqid() . '.' . $ext;
            $target = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target)) {
                $sql = "INSERT INTO assignments (teacher_id, title, description, file_path, deadline)
                        VALUES ($teacher_id, '$title', '$description', '$target', '$deadline')";
    
                if ($conn->query($sql) === TRUE) {
                    $message = "Assignment uploaded successfully!";
                } else {
                    $message = "Error: " . $conn->error;
                }
            } else {
                $message = "File upload failed.";
            }
        }
    } else {
        $message = "Please select a file to upload.";
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Upload Assignment</title>
    <!-- Liên kết CSS chung -->
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="container">
    <!-- Header -->
    <header>
        <h1>Upload Assignment</h1>
        <nav>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="teacher_edit_student.php">Quản lý SV</a></li>
                <li><a href="teacher_view_submissions.php">Xem bài làm</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </header>

    <div class="content">
        <h2>Giao bài tập mới</h2>
        <?php if (!empty($message)): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form class="upload-form" method="post" enctype="multipart/form-data">
            <label for="title">Tiêu đề:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Mô tả:</label>
            <textarea name="description" id="description" rows="4"></textarea>

            <label for="deadline">Hạn nộp:</label>
            <input type="datetime-local" name="deadline" id="deadline">

            <label for="assignment_file">File bài tập:</label>
            <input type="file" name="assignment_file" id="assignment_file" required>

            <input class="button" type="submit" name="upload_assignment" value="Upload">
        </form>
    </div>
</div>
</body>
</html>
