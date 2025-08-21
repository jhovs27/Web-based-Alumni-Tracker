<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../admin/config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Log the request for debugging
error_log("Simple AJAX request received: " . json_encode($_POST));

// Check if user is logged in as program chair
if (!isset($_SESSION['chair_username'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access - No chair_username in session'
    ]);
    exit();
}

// Get parameters
$search = isset($_POST['search']) ? $_POST['search'] : '';
$school_year = isset($_POST['school_year']) ? $_POST['school_year'] : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Simple test query
    $query = "SELECT s.StudentNo, s.LastName, s.FirstName, s.MiddleName, s.Sex, s.ContactNo 
              FROM students s 
              LIMIT 5";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate simple HTML
    $html = '<div class="test-result">';
    $html .= '<h3>Test Results (' . count($result) . ' records)</h3>';
    $html .= '<table border="1">';
    $html .= '<tr><th>Student No</th><th>Name</th><th>Sex</th><th>Contact</th></tr>';
    
    foreach ($result as $row) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['StudentNo']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['LastName'] . ', ' . $row['FirstName']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['Sex']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['ContactNo']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    $html .= '<p>Search: ' . htmlspecialchars($search) . '</p>';
    $html .= '<p>School Year: ' . htmlspecialchars($school_year) . '</p>';
    $html .= '<p>Entries: ' . $entries_per_page . '</p>';
    $html .= '<p>Page: ' . $current_page . '</p>';
    $html .= '</div>';
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'total_records' => count($result),
        'current_page' => $current_page,
        'total_pages' => 1
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 