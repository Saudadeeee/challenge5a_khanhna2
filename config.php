<?php
// In Docker environment, the hostname is the service name
$servername = "db";
$username = "root";
$password = "1";
$dbname = "challenge5a";
$port = 3306;

$conn = null;
$connected = false;

// First try: Docker internal network connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    $connected = true;
    
} catch (Exception $e) {
    // Don't do anything yet, try alternative connection
}

// Second try: Local connection (if first try failed)
if (!$connected) {
    try {
        $conn = new mysqli('127.0.0.1', $username, $password, $dbname, 3334);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8");
        
    } catch (Exception $e2) {
        // Now show the error
        echo "<div style='padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h3>Database connection failed</h3>";
        echo "<p>" . $e2->getMessage() . "</p>";
        echo "<h4>Docker Container Status:</h4>";
        echo "<pre>";
        
        // Try to get Docker container status
        $docker_ps = @shell_exec('docker ps');
        echo $docker_ps ? htmlspecialchars($docker_ps) : "Could not execute 'docker ps'";
        
        echo "</pre>";
        echo "<h4>Troubleshooting steps:</h4>";
        echo "<ol>";
        echo "<li>Run <code>docker ps</code> to check if containers are running</li>";
        echo "<li>Verify <code>prog5_db</code> container is active</li>";
        echo "<li>Try restarting containers with <code>docker-compose down && docker-compose up -d</code></li>";
        echo "<li>Check MySQL logs with <code>docker logs prog5_db</code></li>";
        echo "<li>Make sure the MySQL port (3334) is not being used by another application</li>";
        echo "</ol>";
        echo "</div>";
        die();
    }
}
?>
