<?php
include 'database.php';

// Check if job_posts table exists
$table_check = "SHOW TABLES LIKE 'job_posts'";
$result = mysqli_query($conn, $table_check);

if (mysqli_num_rows($result) == 0) {
    echo "Table 'job_posts' does not exist. Creating table...<br>";
    
    // Create the table
    $create_table = "CREATE TABLE IF NOT EXISTS `job_posts` (
        `id` int NOT NULL AUTO_INCREMENT,
        `job_title` varchar(255) NOT NULL,
        `company_name` varchar(255) NOT NULL,
        `company_logo` varchar(255) DEFAULT NULL,
        `job_type` varchar(50) NOT NULL,
        `job_category` varchar(100) NOT NULL,
        `job_description` text NOT NULL,
        `qualifications` text NOT NULL,
        `salary_min` decimal(10,2) DEFAULT NULL,
        `salary_max` decimal(10,2) DEFAULT NULL,
        `location` varchar(255) NOT NULL,
        `deadline` date NOT NULL,
        `how_to_apply` text NOT NULL,
        `contact_email` varchar(255) NOT NULL,
        `contact_phone` varchar(50) DEFAULT NULL,
        `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
        `posted_date` datetime NOT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_status` (`status`),
        KEY `idx_posted_date` (`posted_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";

    if (mysqli_query($conn, $create_table)) {
        echo "Table 'job_posts' created successfully.<br>";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Table 'job_posts' exists.<br>";
    
    // Check table structure
    $columns = [
        'job_title', 'company_name', 'company_logo', 'job_type', 'job_category',
        'job_description', 'qualifications', 'salary_min', 'salary_max',
        'location', 'deadline', 'how_to_apply', 'contact_email', 'contact_phone',
        'status', 'posted_date', 'created_at', 'updated_at'
    ];
    
    $missing_columns = [];
    foreach ($columns as $column) {
        $check_column = "SHOW COLUMNS FROM job_posts LIKE '$column'";
        $result = mysqli_query($conn, $check_column);
        if (mysqli_num_rows($result) == 0) {
            $missing_columns[] = $column;
        }
    }
    
    if (!empty($missing_columns)) {
        echo "Missing columns: " . implode(', ', $missing_columns) . "<br>";
    } else {
        echo "All required columns exist.<br>";
    }
}

mysqli_close($conn);
?> 