<?php
session_start();
include('config.php');

// Nếu chưa đăng nhập thì chuyển hướng đến trang login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Trang Chủ</title>
  <!-- Liên kết tới file CSS trong thư mục public -->
  <link rel="stylesheet" href="public/style.css">
</head>
<body>
  <div class="container">
    <header>
      <h1>Chào mừng, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
      <nav>
        <ul>
          <li><a href="index.php">Trang chủ</a></li>
          <li><a href="profile.php?id=<?php echo $_SESSION['user']['id'] ?>">Hồ sơ</a></li>
          <li><a href="logout.php">Đăng xuất</a></li>
        </ul>
      </nav>
    </header>
    <div class="content">
      <?php if ($user['role'] == 'student'): ?>
        <h2>Các bài tập</h2>
        <?php
          $student_id = $user['id'];

          // Lấy danh sách bài tập, kèm theo xem sinh viên đã nộp bài chưa
          $sql = "SELECT a.*, s.id AS submission_id
                  FROM challenges a
                  LEFT JOIN submissions s 
                         ON a.id = s.assignment_id
                         AND s.student_id = $student_id
                  ORDER BY a.created_at DESC";

          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0):
        ?>
          <table class="assignment-table">
            <tr>
              <th>Tiêu đề</th>
              <th>Mô tả</th>
              <th>Deadline</th>
              <th>File</th>
              <th>Thao tác</th>
              <th>Trạng thái</th>
            </tr>
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
                <td>
                  <a class="button" href="submission_upload.php?assignment_id=<?php echo $row['id']; ?>">
                    Nộp bài
                  </a>
                </td>
                <td>
                  <?php if ($row['submission_id']): ?>
                    <span style="color: green; font-weight: bold;">Đã nộp</span>
                  <?php else: ?>
                    <span style="color: red; font-weight: bold;">Chưa nộp</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </table>
        <?php else: ?>
          <p>Hiện không có challenges nào.</p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
