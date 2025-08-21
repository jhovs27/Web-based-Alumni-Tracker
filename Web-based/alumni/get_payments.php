<?php
session_start();
require_once '../admin/config/database.php';

// Check if user is logged in as alumni
if (!isset($_SESSION['is_alumni']) || !$_SESSION['is_alumni']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $alumni_id = $_SESSION['alumni_id'];
    $student_no = $_SESSION['alumni_student_no'];
    
    // Get payment statistics
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_paid,
            SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending,
            COUNT(*) as total_payments
        FROM alumni_payments 
        WHERE alumni_id = ?
    ");
    $stmt->execute([$alumni_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get payment history
    $stmt = $conn->prepare("
        SELECT * FROM alumni_payments 
        WHERE alumni_id = ? 
        ORDER BY payment_date DESC 
        LIMIT 10
    ");
    $stmt->execute([$alumni_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payment methods
    $stmt = $conn->prepare("SELECT * FROM payment_methods WHERE is_active = 1");
    $stmt->execute();
    $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_paid' => floatval($stats['total_paid'] ?? 0),
            'total_pending' => floatval($stats['total_pending'] ?? 0),
            'total_payments' => intval($stats['total_payments'] ?? 0)
        ],
        'payments' => $payments,
        'payment_methods' => $payment_methods
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 