<?php
session_start();
include('config.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    die("Access denied.");
}

$message = '';

if(!isset($_GET['challenge_id'])) {
    die("Challenge id is required.");
}

$chall_id = $_GET["challenge_id"];

$stmt = $conn->prepare("SELECT * FROM challenges WHERE id = ? AND teacher_id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$teacher_id = $_SESSION['user']['id'];
$stmt->bind_param("ii", $chall_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Challenge not found or you don't have permission to edit it.");
}

$challenge = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['challenge_edit'])) {
    $challenge_hint = $conn->real_escape_string($_POST['challenge_hint']);
    
    if (isset($_FILES['challenge_file']) && $_FILES['challenge_file']['error'] == 0) {
        $upload_dir = 'uploads/challenges/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = basename($_FILES['challenge_file']['name']);
        $target = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['challenge_file']['tmp_name'], $target)) {
            $file_content_raw = file_get_contents($target);
            $file_content = $conn->real_escape_string($file_content_raw);
            
            $sql = "UPDATE challenges SET challenge_hint=?, file_path=?, file_content=? WHERE id=? AND teacher_id=?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                $message = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param("sssii", $challenge_hint, $target, $file_content_raw, $chall_id, $teacher_id);
                
                if ($stmt->execute()) {
                    $message = "Challenge updated successfully!";
                    
                    $stmt = $conn->prepare("SELECT * FROM challenges WHERE id = ?");
                    $stmt->bind_param("i", $chall_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $challenge = $result->fetch_assoc();
                } else {
                    $message = "Database error: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $message = "File upload failed.";
        }
    } else {
     
        $sql = "UPDATE challenges SET challenge_hint=? WHERE id=? AND teacher_id=?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            $message = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("sii", $challenge_hint, $chall_id, $teacher_id);
            
            if ($stmt->execute()) {
                $message = "Challenge hint updated successfully!";
             
                $stmt = $conn->prepare("SELECT * FROM challenges WHERE id = ?");
                $stmt->bind_param("i", $chall_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $challenge = $result->fetch_assoc();
            } else {
                $message = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Edit Challenge</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        .challenge-form {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .challenge-form h2 {
            text-align: center;
            margin-bottom: 15px;
        }
        .challenge-form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .challenge-form textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }
        .challenge-form input[type="file"] {
            margin-top: 5px;
        }
        .challenge-form input[type="submit"] {
            display: block;
            width: 200px;
            margin: 20px auto 0;
            padding: 10px;
            background: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .challenge-form input[type="submit"]:hover {
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
        .current-file {
            background: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Edit Challenge</h1>
        <nav>
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="teacher_challenges.php">Quản lý Challenges</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </header>
    
    <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="challenge-form">
        <h2>Sửa Challenge</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $chall_id ?>">
            
            <label for="challenge_hint">Gợi ý Challenge:</label>
            <textarea name="challenge_hint" id="challenge_hint" rows="4" required><?php echo htmlspecialchars($challenge['challenge_hint']); ?></textarea>
            
            <div class="current-file">
                <p><strong>File hiện tại:</strong> <?php echo htmlspecialchars($challenge['file_path']); ?></p>
            </div>
            
            <label for="challenge_file">File Challenge mới (tùy chọn):</label>
            <input type="file" name="challenge_file" id="challenge_file" accept=".txt">
            <small>* Chỉ chọn file nếu muốn thay thế file hiện tại</small>
            
            <input type="submit" name="challenge_edit" value="Cập nhật">
        </form>
    </div>
</div>
</body>
</html>
