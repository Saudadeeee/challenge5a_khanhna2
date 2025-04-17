<?php
session_start();
include('config.php');

// Chỉ giáo viên mới được truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    die("Access denied.");
}

$message = '';
$teacher_id = $_SESSION['user']['id'];

if (!isset($_GET['assignment_id'])) {
    die("Assignment ID is required.");
}

$assignment_id = intval($_GET['assignment_id']);

// Lấy thông tin bài tập để hiển thị trong form
$sql = "SELECT * FROM assignments WHERE id = $assignment_id AND teacher_id = $teacher_id LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $assignment = $result->fetch_assoc();
} else {
    die("Assignment not found or you don't have permission to edit this assignment.");
}

// Xử lý khi form được submit
if (isset($_POST['update_assignment'])) {
  
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $deadline = $_POST['deadline'];
    
    $file_path = $assignment['file_path'];
    
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
            
            $new_filename = uniqid() . '.' . $ext;
            $target = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target)) {
                $file_path = $target;
            } else {
                $message = "File upload failed.";
            }
        }
    }
    
    if (empty($message)) {
        $sql_update = "UPDATE assignments 
                       SET title = '$title', description = '$description', file_path = '$file_path', deadline = '$deadline' 
                       WHERE id = $assignment_id AND teacher_id = $teacher_id";
        if ($conn->query($sql_update)) {
            $message = "Assignment updated successfully!";
            
            $result = $conn->query("SELECT * FROM assignments WHERE id = $assignment_id AND teacher_id = $teacher_id LIMIT 1");
            if ($result && $result->num_rows > 0) {
                $assignment = $result->fetch_assoc();
            }
        } else {
            $message = "Error updating assignment: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Edit Assignment</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 15px;
        }
        .form-container label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container textarea,
        .form-container input[type="datetime-local"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container input[type="file"] {
            margin-top: 5px;
        }
        .form-container input[type="submit"] {
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
        }
        .message {
            max-width: 600px;
            margin: 10px auto;
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .message.success {
            background: #dff0d8;
            border: 1px solid #c3e6cb;
            color: #3c763d;
        }
        .message.error {
            background: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Edit Assignment</h1>
        <nav>
            <ul>
                <li><a href="teacher_assignments.php">Danh sách bài tập</a></li>
                <li><a href="teacher_assignment_upload.php">Giao bài tập mới</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </header>
    
    <!-- Hiển thị thông báo -->
    <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <h2>Edit Assignment</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="title">Tiêu đề:</label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
            
            <label for="description">Mô tả:</label>
            <textarea name="description" id="description" rows="5" required><?php echo htmlspecialchars($assignment['description']); ?></textarea>
            
            <label for="deadline">Deadline:</label>
            <input type="datetime-local" name="deadline" id="deadline" value="<?php echo date('Y-m-d\TH:i', strtotime($assignment['deadline'])); ?>" required>
            
            <label for="assignment_file">File (nếu muốn cập nhật file mới):</label>
            <input type="file" name="assignment_file" id="assignment_file">
            
            <input type="submit" name="update_assignment" value="Cập nhật bài tập">
        </form>
    </div>
</div>
</body>
</html>
