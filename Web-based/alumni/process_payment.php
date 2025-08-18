<?php
session_start();
require_once '../admin/config/database.php';

// Check if user is logged in as alumni
if (!isset($_SESSION['is_alumni']) || !$_SESSION['is_alumni']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $alumni_id = $_SESSION['alumni_id'];
        $student_no = $_SESSION['alumni_student_no'];
        $payment_type = trim($_POST['payment_type']);
        $amount = floatval($_POST['amount']);
        $payment_method = trim($_POST['payment_method']);
        $notes = trim($_POST['notes'] ?? '');
        
        // Validate input
        if (empty($payment_type) || $amount <= 0 || empty($payment_method)) {
            throw new Exception('Please fill in all required fields');
        }
        
        // Generate reference number
        $reference_number = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Insert payment record
        $stmt = $conn->prepare("
            INSERT INTO alumni_payments 
            (alumni_id, student_no, payment_type, amount, payment_method, status, reference_number, notes) 
            VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)
        ");
        
        $stmt->execute([
            $alumni_id,
            $student_no,
            $payment_type,
            $amount,
            $payment_method,
            $reference_number,
            $notes
        ]);
        
        $payment_id = $conn->lastInsertId();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Payment request submitted successfully',
            'payment_id' => $payment_id,
            'reference_number' => $reference_number,
            'amount' => $amount,
            'payment_method' => $payment_method
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 