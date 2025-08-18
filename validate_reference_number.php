<?php
header('Content-Type: application/json');
require_once 'admin/config/database.php';

// Enable CORS for AJAX requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reference_number = trim($_POST['reference_number'] ?? '');
        
        if (empty($reference_number)) {
            echo json_encode([
                'success' => false,
                'message' => 'Reference number is required'
            ]);
            exit;
        }
        
        // Check if reference number already exists in the database
        $stmt = $conn->prepare("SELECT * FROM payment_references WHERE reference_number = ?");
        $stmt->execute([$reference_number]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'This reference number has already been used. Please use a different reference number.'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Reference number is valid and unique'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }
    
} catch (PDOException $e) {
    // Log the actual error for debugging
    error_log("Reference number validation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Reference number validation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 