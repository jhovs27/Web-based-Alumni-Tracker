<?php
require_once 'database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS alumni_events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_title VARCHAR(255) NOT NULL,
        event_description TEXT,
        event_type ENUM('Reunion', 'Seminar', 'Webinar', 'Career Fair', 'Outreach', 'Other') NOT NULL,
        event_category ENUM('Academic', 'Social', 'Career', 'Other') NULL,
        start_datetime DATETIME NOT NULL,
        end_datetime DATETIME NOT NULL,
        physical_address TEXT NULL,
        online_link VARCHAR(255) NULL,
        timezone VARCHAR(50) NOT NULL,
        poster_image VARCHAR(255) NULL,
        event_document VARCHAR(255) NULL,
        registration_required BOOLEAN DEFAULT TRUE,
        max_attendees INT NULL,
        allow_guests BOOLEAN DEFAULT FALSE,
        auto_confirm BOOLEAN DEFAULT FALSE,
        contact_person VARCHAR(100) NOT NULL,
        contact_email VARCHAR(100) NOT NULL,
        contact_phone VARCHAR(20) NULL,
        visibility ENUM('Public', 'Private') DEFAULT 'Public',
        status ENUM('Draft', 'Active', 'Cancelled', 'Completed') DEFAULT 'Draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $conn->exec($sql);
    echo "Table alumni_events created successfully";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 