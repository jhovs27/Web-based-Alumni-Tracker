<?php
session_start();

echo "<h2>Alumni Dashboard Test (No Header)</h2>";

echo "<h3>Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h3>Session Check:</h3>";
$is_alumni = isset($_SESSION['is_alumni']) && $_SESSION['is_alumni'];
$has_alumni_id = isset($_SESSION['alumni_id']);
$session_valid = $is_alumni && $has_alumni_id;

echo "<p>is_alumni: " . ($is_alumni ? 'true' : 'false') . "</p>";
echo "<p>has_alumni_id: " . ($has_alumni_id ? 'true' : 'false') . "</p>";
echo "<p>Session valid: " . ($session_valid ? '✓ YES' : '✗ NO') . "</p>";

if ($session_valid) {
    echo "<h3>Welcome to Alumni Dashboard!</h3>";
    echo "<p>Hello, " . ($_SESSION['alumni_name'] ?? 'Alumni') . "!</p>";
    echo "<p>Your Alumni ID: " . ($_SESSION['alumni_alumni_id'] ?? 'N/A') . "</p>";
    echo "<p>Your Email: " . ($_SESSION['alumni_email'] ?? 'N/A') . "</p>";
    
    echo "<h3>Dashboard Content:</h3>";
    echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 5px;'>";
    echo "<h4>Recent Activities</h4>";
    echo "<ul>";
    echo "<li>No upcoming events</li>";
    echo "<li>No new job postings</li>";
    echo "<li>No recent updates</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>Navigation:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php'>Full Dashboard (with header)</a></li>";
    echo "<li><a href='../login.php'>Back to Login</a></li>";
    echo "<li><a href='../logout.php'>Logout</a></li>";
    echo "</ul>";
    
} else {
    echo "<h3>Session Invalid</h3>";
    echo "<p>You need to be logged in as an alumni to access this dashboard.</p>";
    echo "<p><a href='../login.php'>Go to Login</a></p>";
    
    echo "<h4>Debug Info:</h4>";
    echo "<p>Session variables present:</p>";
    echo "<ul>";
    echo "<li>is_alumni: " . (isset($_SESSION['is_alumni']) ? $_SESSION['is_alumni'] : 'NOT SET') . "</li>";
    echo "<li>alumni_id: " . (isset($_SESSION['alumni_id']) ? $_SESSION['alumni_id'] : 'NOT SET') . "</li>";
    echo "<li>alumni_name: " . (isset($_SESSION['alumni_name']) ? $_SESSION['alumni_name'] : 'NOT SET') . "</li>";
    echo "</ul>";
}
?> 