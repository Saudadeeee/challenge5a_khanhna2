<?php
session_start();
include('config.php');

// Chỉ giáo viên mới được truy cập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    die("Access denied.");
}

// Xử lý cập nhật và xóa sinh viên
if (isset($_POST['delete'])) {
    $student_id = intval($_POST['student_id']);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        $message = "Student deleted.";
    } else {
        $message = "Error deleting student: " . $stmt->error;
    }

    $stmt->close();
}

if (isset($_POST['update'])) {
    $student_id = intval($_POST['student_id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE users SET email = ?, phone = ?, full_name = ? WHERE id = ? AND role = 'student'");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssi", $email, $phone, $name, $student_id);

    if ($stmt->execute()) {
        $message = "Student info updated.";
    } else {
        $message = "Error updating student info: " . $stmt->error;
    }

    $stmt->close();

}

$student_id_param = '';
if (isset($_GET['student_id'])) {
    $student_id_param = intval($_GET['student_id']);
}

$sql = "SELECT full_name, email, phone from users WHERE role='student' and id=$student_id_param";
$res = $conn->query($sql);
$row = $res->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Teacher - Edit Student</title>
   
    <link rel="stylesheet" href="public/style.css">
    
    <style>
        .edit-form {
            max-width: 500px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .edit-form h2 {
            text-align: center;
            margin-bottom: 15px;
        }
        .edit-form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .edit-form input[type="text"],
        .edit-form input[type="email"],
        .edit-form input[type="name"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .edit-form .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .edit-form input[type="submit"] {
            width: 48%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        .update-btn {
            background: #28a745;
        }
        .update-btn:hover {
            background: #218838;
        }
        .delete-btn {
            background: #dc3545;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .message {
            max-width: 500px;
            margin: 10px auto;
            text-align: center;
            padding: 10px;
            background: #dff0d8;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            color: #3c763d;
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
                <li><a href="teacher_view_submissions.php">Xem bài làm</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </header>

    <?php if (isset($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="edit-form">
        <h2>Cập nhật / Xóa Sinh viên</h2>
        <form method="post">
            <input type="text" name="student_id" id="student_id" value="<?php echo htmlspecialchars($student_id_param); ?>" hidden required>
            
            <label for="name">Name</label>
            <input type="name" name="name" id="name" value="<?php echo $row['full_name'] ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo $row['email'] ?>" required>
            
            <label for="phone">Phone number</label>
            <input type="text" name="phone" id="phone" value="<?php echo $row['phone'] ?>" required>
            
            <div class="button-group">
                <input class="update-btn" type="submit" name="update" value="Update">
                <input class="delete-btn" type="submit" name="delete" value="Delete">
            </div>
        </form>
    </div>
</div>
</body>
</html>
