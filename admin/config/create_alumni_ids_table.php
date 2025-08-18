<?php
require_once 'database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS alumni_ids (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_no VARCHAR(20) NOT NULL,
        alumni_id VARCHAR(20) NOT NULL UNIQUE,
        graduation_year INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $conn->exec($sql);
    echo "Alumni IDs table created successfully or already exists!";
} catch(PDOException $e) {
    echo "Error creating alumni_ids table: " . $e->getMessage();
}
?> 