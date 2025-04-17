<?php
include('config.php');

// This is a utility script to quickly update passwords for any user
// IMPORTANT: Remove this file from your server after use!

// For security, only allow this to run from the command line or localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) && php_sapi_name() !== 'cli') {
    die("For security reasons, this script can only be run locally.");
}

// Update a specific user's password
function update_user_password($username, $new_password) {
    global $conn;
    
    // Generate hash for new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update the user's password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    if (!$stmt) {
        echo "Prepare error: " . $conn->error . "\n";
        return;
    }
    
    $stmt->bind_param("ss", $hashed_password, $username);
    
    if ($stmt->execute()) {
        echo "Password updated successfully for user: $username<br>";
        echo "New password hash: $hashed_password<br>";
    } else {
        echo "Error updating password: " . $stmt->error . "<br>";
    }
    
    $stmt->close();
}

// Update all users to have the same password
function update_all_passwords($new_password) {
    global $conn;
    
    // Generate hash for new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update all users
    $stmt = $conn->prepare("UPDATE users SET password = ?");
    if (!$stmt) {
        echo "Prepare error: " . $conn->error . "\n";
        return;
    }
    
    $stmt->bind_param("s", $hashed_password);
    
    if ($stmt->execute()) {
        echo "All user passwords updated successfully to: '$new_password'<br>";
        echo "New password hash: $hashed_password<br>";
        
        // Verify the password update with a test
        test_password('teacher1', $new_password);
    } else {
        echo "Error updating passwords: " . $stmt->error . "<br>";
    }
    
    $stmt->close();
}

// Display the bcrypt hash for a given password
function show_hash_for_password($password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Password: '$password'<br>";
    echo "Generated hash: $hash<br>";
    echo "Verification test: " . (password_verify($password, $hash) ? "PASS" : "FAIL") . "<br>";
}

// Test if a username/password combination works
function test_password($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    if (!$stmt) {
        echo "Prepare error: " . $conn->error . "\n";
        return;
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "Test failed: User $username not found<br>";
        return;
    }
    
    $row = $result->fetch_assoc();
    $stored_hash = $row['password'];
    
    echo "<h3>Password Test for $username</h3>";
    echo "Input password: $password<br>";
    echo "Stored hash: $stored_hash<br>";
    echo "Verification result: " . (password_verify($password, $stored_hash) ? "PASS ✅" : "FAIL ❌") . "<br>";
    
    $stmt->close();
}

// Show all users' password hashes for debugging
function show_all_users() {
    global $conn;
    
    $result = $conn->query("SELECT username, password FROM users");
    
    echo "<h3>Current User Password Hashes</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Username</th><th>Password Hash</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['password']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Automatically fix all passwords when page is loaded (Remove this after fixing)
echo "<h1>Automatic Password Fix</h1>";
echo "<p>Setting all user passwords to '1'...</p>";
update_all_passwords('1');
echo "<p>Done! You should now be able to login with any account using password '1'</p>";
echo "<p>Please remove this automated section after fixing your passwords.</p>";

// Simple interface
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'update_user' && isset($_GET['username']) && isset($_GET['password'])) {
        update_user_password($_GET['username'], $_GET['password']);
    }
    elseif ($action === 'update_all' && isset($_GET['password'])) {
        update_all_passwords($_GET['password']);
    }
    elseif ($action === 'show_hash' && isset($_GET['password'])) {
        show_hash_for_password($_GET['password']);
    }
    elseif ($action === 'test_login' && isset($_GET['username']) && isset($_GET['password'])) {
        test_password($_GET['username'], $_GET['password']);
    } 
    elseif ($action === 'show_users') {
        show_all_users();
    }
    else {
        echo "Invalid action or missing parameters";
    }
    
    echo "<p><a href='update_password.php'>Return to main menu</a></p>";
}
else {
    // Show usage instructions
    echo "<h1>Password Management Utility</h1>";
    echo "<p>This tool helps update user passwords and debug login issues.</p>";
    
    echo "<h2>Update a specific user's password</h2>";
    echo "<a href='?action=update_user&username=teacher1&password=1'>Update teacher1 password to '1'</a><br><br>";
    
    echo "<h2>Update all users' passwords at once</h2>";
    echo "<a href='?action=update_all&password=1'>Set all passwords to '1'</a><br><br>";
    
    echo "<h2>Show bcrypt hash for a password</h2>";
    echo "<a href='?action=show_hash&password=1'>Show hash for password '1'</a><br><br>";
    
    echo "<h2>Test a login</h2>";
    echo "<a href='?action=test_login&username=teacher1&password=1'>Test login for teacher1 with password '1'</a><br><br>";
    
    echo "<h2>Show all users</h2>";
    echo "<a href='?action=show_users'>Show all users and their password hashes</a><br><br>";
}

// Debug information
echo "<hr>";
echo "<h3>PHP Version: " . phpversion() . "</h3>";
echo "<h3>Password Hashing Algorithm: " . PASSWORD_DEFAULT . "</h3>";
?>
