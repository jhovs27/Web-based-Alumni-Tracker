<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'enrolment_hinunangan';

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 