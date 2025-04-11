<?php
$servername = "db";
$username = "root";
$password = "1";
$dbname = "challenge5a";
$port = 3333;

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    try {
        $conn = new mysqli('127.0.0.1', $username, $password, $dbname, 3334);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8");
        
    } catch (Exception $e2) {
        die("Database connection failed: " . $e2->getMessage() . 
            "<br><br>Troubleshooting steps:<br>" .
            "1. Run <code>docker ps</code> to check if containers are running<br>" .
            "2. Verify <code>prog5_db</code> container is active<br>" . 
            "3. Try restarting containers with <code>docker-compose down && docker-compose up -d</code><br>" . 
            "4. Check MySQL logs with <code>docker logs prog5_db</code>");
    }
}
?>
