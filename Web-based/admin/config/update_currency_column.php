<?php
include 'database.php';

// Add currency column to job_posts table
$query = "ALTER TABLE job_posts ADD COLUMN currency VARCHAR(3) DEFAULT 'USD' AFTER salary_max";

if (mysqli_query($conn, $query)) {
    echo "Successfully added currency column to job_posts table.";
} else {
    echo "Error adding currency column: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 