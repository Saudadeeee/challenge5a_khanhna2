<?php
session_start();
include('config.php');

if (!isset($_SESSION['user'])) {
    die("Access denied.");
}

$detail_user_id = intval($_GET['id']);

if (isset($_POST['send_message'])) {
    $sender_id = $_SESSION['user']['id'];
    $receiver_id = $detail_user_id;
    $content = $conn->real_escape_string($_POST['content']);
    $sql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES ($sender_id, $receiver_id, '$content')";
    $conn->query($sql);
    echo "Message sent.";
}

if (isset($_POST['update_message'])) {
    $message_id = intval($_POST['message_id']);
    $content = $conn->real_escape_string($_POST['content']);
    $sender_id = $_SESSION['user']['id'];
    $sql = "UPDATE messages SET content='$content' WHERE id=$message_id AND sender_id=$sender_id";
    $conn->query($sql);
    echo "Message updated.";
}

if (isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    $sender_id = $_SESSION['user']['id'];
    $sql = "DELETE FROM messages WHERE id=$message_id AND sender_id=$sender_id";
    $conn->query($sql);
    echo "Message deleted.";
}

$sql = "SELECT m.*, u.full_name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE receiver_id = $detail_user_id ORDER BY m.created_at DESC";
$result = $conn->query($sql);
?>
<html>
  <body>
    <h3>Messages for User ID <?php echo $detail_user_id; ?></h3>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div style="border-bottom:1px solid #ccc; margin-bottom:10px;">
        <strong><?php echo $row['sender_name']; ?></strong>:
        <?php echo $row['content']; ?> <em>(<?php echo $row['created_at']; ?>)</em>
        <?php if ($row['sender_id'] == $_SESSION['user']['id']): ?>
          <form method="post" style="display:inline;">
            <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
            <input type="text" name="content" value="<?php echo $row['content']; ?>">
            <input type="submit" name="update_message" value="Update">
          </form>
          <form method="post" style="display:inline;">
            <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
            <input type="submit" name="delete_message" value="Delete">
          </form>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
    
    <form method="post">
      <textarea name="content" rows="3" cols="50"></textarea><br>
      <input type="submit" name="send_message" value="Send Message">
    </form>
  </body>
</html>
