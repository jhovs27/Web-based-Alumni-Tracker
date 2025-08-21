<?php
include 'database.php';

// Update existing records to set default currency
$query = "UPDATE job_posts SET currency = 'USD' WHERE currency IS NULL OR currency = ''";

if (mysqli_query($conn, $query)) {
    echo "Successfully updated existing records with default currency (USD).";
} else {
    echo "Error updating records: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 