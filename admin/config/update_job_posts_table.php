<?php
include 'database.php';

// Add currency column to job_posts table
$query = "ALTER TABLE job_posts ADD COLUMN currency VARCHAR(10) DEFAULT 'USD' AFTER salary_max";

if (mysqli_query($conn, $query)) {
    echo "Currency column added successfully!";
} else {
    echo "Error adding currency column: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 