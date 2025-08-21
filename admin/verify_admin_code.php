<?php
header('Content-Type: application/json');
require_once 'config/database.php';
require_once 'includes/admin_access_functions.php';

// Check if admin access code is disabled
$stmt = $conn->prepare("SELECT is_enabled FROM admin_access_codes WHERE id = 1");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row && !$row['is_enabled']) {
    echo json_encode([
        'valid' => true,
        'message' => 'Admin access code verification is currently disabled.',
        'code_disabled' => true
    ]);
    exit;
}

$admin_code = trim($_POST['admin_code'] ?? '');
if ($admin_code === '') {
    echo json_encode(['valid' => false, 'message' => 'Admin access code is required.']);
    exit;
}

$result = verifyAdminAccessCode($admin_code, $conn);
echo json_encode([
    'valid' => $result['valid'],
    'message' => $result['message']
]); 