<?php
session_start();
include('config.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'student') {
    die("Access denied.");
}

if (!isset($_GET['challenge_id'])) {
    die("Challenge ID is required.");
}

$challenge_id = intval($_GET['challenge_id']);
$user_id = $_SESSION['user']['id'];

$sql = "SELECT * FROM challenges WHERE id = $challenge_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Challenge not found.");
}

$challenge = $result->fetch_assoc();
$correct_answer = pathinfo($challenge['file_path'], PATHINFO_FILENAME);

$status_message = '';
$status_class = '';

if (isset($_POST['submit_answer'])) {
    $submitted_answer = trim($_POST['answer']);
    $is_correct = ($submitted_answer == $correct_answer) ? 1 : 0;
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO challenge_attempts (challenge_id, student_id, submitted_answer, is_correct) VALUES (?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iisi", $challenge_id, $user_id, $submitted_answer, $is_correct);
    
    if ($stmt->execute()) {
        if ($is_correct) {
            $file_content = file_get_contents($challenge['file_path']);
            $status_message = "Correct! Challenge content: <br><pre>" . htmlspecialchars($file_content) . "</pre>";
            $status_class = "status-correct";
        } else {
            $status_message = "Incorrect answer.";
            $status_class = "status-incorrect";
        }
    } else {
        $status_message = "Error submitting answer: " . $stmt->error;
        $status_class = "status-incorrect";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giải Challenge</title>
    <style>
        .container {
          max-width: 800px;
          margin: 20px auto;
          padding: 20px;
          background: #fff;
          border: 1px solid #ddd;
          border-radius: 8px;
          box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        header {
            background: #007bff;
            color: #fff;
            padding: 15px 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        header h1 {
            font-size: 24px;
            font-weight: normal;
            margin-right: 20px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 15px;
        }
        nav ul li {
            margin: 0;
        }
        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        nav ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

          h3 {
            text-align: center;
            color: #333;
            font-size: 24px;
          }

          .challenge-description {
            margin-bottom: 30px;
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
          }

          .challenge-description p {
            font-size: 16px;
            margin-bottom: 15px;
          }

          .show-hint {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            border: none;
          }

          .show-hint:hover {
            background-color: #218838;
          }

          .hint-content {
            display: none;
            background-color: #f8f9fa;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
          }

          .challenge-form {
            text-align: center;
            margin-top: 20px;
          }

          .challenge-form input[type="text"] {
            padding: 10px;
            width: 80%;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
          }

          .challenge-form input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            border: none;
            width: 80%;
            transition: background-color 0.3s;
          }

          .challenge-form input[type="submit"]:hover {
            background-color: #0056b3;
          }

          .message {
            margin-top: 20px;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
          }

          .message.status-correct {
            background-color: #d4edda;
            color: #3c763d;
            border: 1px solid #c3e6cb;
          }

          .message.status-incorrect {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
          }

          .message,
          .hint-content {
            opacity: 0;
            animation: fadeIn 1s forwards;
          }

          @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
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
                  <li><a href="profile.php?id=<?php echo $_SESSION['user']['id'] ?>">Hồ sơ</a></li>
                  <li><a href="logout.php">Đăng xuất</a></li>
              </ul>
          </nav>
      </header>

        <div class="challenge-description">
            <p><strong>Content: </strong><?php echo nl2br(htmlspecialchars($challenge['file_content'])); ?></p>
            <button class="show-hint" onclick="toggleHint()">Xem gợi ý</button>
            <div class="hint-content" id="hintContent" style="display: none;">
                <?php echo nl2br(htmlspecialchars($challenge['challenge_hint']));  ?>
            </div>
        </div>

        <div class="challenge-form">
            <form method="post">
                <label for="answer">Nhập đáp án:</label><br>
                <input type="text" name="answer" id="answer" required placeholder="Nhập đáp án của bạn"><br>
                <input type="submit" name="submit_answer" value="Nộp đáp án">
            </form>
        </div>

        <?php if (isset($status_message) && !empty($status_message)): ?>
            <div class="message <?php echo $status_class; ?>">
                <?php echo $status_message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleHint() {
            var hintContent = document.getElementById('hintContent');
            if (hintContent.style.display === "none" || hintContent.style.display === "") {
                hintContent.style.display = "block";
            } else {
                hintContent.style.display = "none";
            }
        }
    </script>
</body>
</html>
