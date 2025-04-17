<?php
session_start();
include('config.php');

// Chỉ sinh viên mới được truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'student') {
    die("Access denied.");
}

$assignment_id = intval($_GET['assignment_id']);
$message = '';

if (isset($_POST['submit_assignment'])) {
    // Lấy assignment_id từ GET, vì URL chứa ?assignment_id=3
    $assignment_id = intval($_GET['assignment_id']); 
    $student_id = intval($_SESSION['user']['id']);

    // Kiểm tra hạn nộp của bài tập
    $stmt = $conn->prepare("SELECT deadline FROM assignments WHERE id = ?");
    if ($stmt === false) {
        $message = "Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $assignment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $message = "Bài tập không tồn tại.";
        } else {
            $assignment = $result->fetch_assoc();
            $deadline = $assignment['deadline'];
            $currentDate = new DateTime();
            $deadlineDate = new DateTime($deadline);
            if ($currentDate > $deadlineDate) {
                $message = "Deadline đã qua, không thể nộp bài.";
            }
        }
        $stmt->close();
    }

    // Nếu deadline chưa qua, tiếp tục xử lý upload file
    if (empty($message)) {
        if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
            $upload_dir = 'uploads/submissions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $original_filename = basename($_FILES['submission_file']['name']);
            $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            $allowed_ext = array('txt', 'pdf');
            $stmt = $conn->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
            if ($stmt === false) {
                $message = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param("ii", $assignment_id, $student_id);
                $stmt->execute();
                
                $stmt->store_result(); 
                
                if ($stmt->num_rows > 0) {
                    die("Sinh viên không thể submit nữa");
                }
            }
            if (!in_array($ext, $allowed_ext)) {
                $message = "Chỉ cho phép upload file định dạng txt hoặc pdf.";
            } else {
                // Đổi tên file để đảm bảo tính duy nhất
                $new_filename = uniqid() . '.' . $ext;
                $target = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target)) {
                    // Sử dụng prepared statement để chèn dữ liệu an toàn
                    $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)");
                    if ($stmt === false) {
                        $message = "Prepare failed: " . $conn->error;
                    } else {
                        $stmt->bind_param("iis", $assignment_id, $student_id, $target);
                        if ($stmt->execute()) {
                            $message = "Bài làm đã được nộp thành công!";
                        } else {
                            $message = "Lỗi khi lưu vào CSDL: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                } else {
                    $message = "Không thể upload file.";
                }
            }
        } else {
            $message = "Bạn chưa chọn file hoặc file bị lỗi.";
        }
    }
}


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nộp Bài</title>
    <!-- Liên kết tới file CSS chung -->
    <link rel="stylesheet" href="public/style.css">
    <!-- CSS riêng cho trang submission_upload -->
    <style>
        .submission-form {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .submission-form h2 {
            text-align: center;
            margin-bottom: 15px;
        }
        .submission-form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .submission-form input[type="file"] {
            margin-top: 5px;
        }
        .submission-form input[type="submit"] {
            display: block;
            width: 150px;
            margin: 20px auto 0;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .submission-form input[type="submit"]:hover {
            background: #0056b3;
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
        <h1>Nộp Bài Tập</h1>
        <nav>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="index.php?view=assignments">Bài tập</a></li>
                <li><a href="student_profile.php">Hồ sơ</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </header>

    <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, 'thành công') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="submission-form">
        <h2>Nộp Bài Tập</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="submission_file">Chọn file bài làm:</label>
            <input type="file" name="submission_file" id="submission_file" required>

            <input type="submit" name="submit_assignment" value="Nộp Bài">
        </form>
    </div>
</div>
</body>
</html>