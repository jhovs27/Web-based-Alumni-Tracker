<?php
require_once 'database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS alumni (
        id INT AUTO_INCREMENT PRIMARY KEY,
        alumni_id VARCHAR(20) NOT NULL,
        fullname VARCHAR(150) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(30),
        address VARCHAR(255),
        password_hash VARCHAR(255) NOT NULL,
        program VARCHAR(100),
        year_graduated INT,
        employment_status VARCHAR(30),
        company_name VARCHAR(100),
        position VARCHAR(100),
        business_name VARCHAR(100),
        business_location VARCHAR(100),
        study_level VARCHAR(50),
        study_type VARCHAR(100),
        student_no VARCHAR(20),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $conn->exec($sql);
    echo "Alumni table created successfully or already exists!";
} catch(PDOException $e) {
    echo "Error creating alumni table: " . $e->getMessage();
}
?> 