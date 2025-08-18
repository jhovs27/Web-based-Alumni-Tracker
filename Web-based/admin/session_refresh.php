<?php
// Session refresh endpoint for admin panel
header('Content-Type: application/json');

// Include session configuration
require_once 'includes/session_config.php';

// Check if admin session is valid
if (!isAdminSessionValid()) {
    echo json_encode([
        'success' => false,
        'redirect' => '../login.php',
        'message' => 'Session expired'
    ]);
    exit;
}

// Include database connection
require_once 'config/database.php';

try {
    // Refresh session and get updated data
    if (refreshAdminSession($conn)) {
        echo json_encode([
            'success' => true,
            'profile_photo_path' => $_SESSION['profile_photo_path'] ?? null,
            'admin_name' => $_SESSION['admin_name'] ?? 'Admin',
            'last_activity' => $_SESSION['last_activity'] ?? time()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'redirect' => '../login.php',
            'message' => 'Failed to refresh session'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'redirect' => '../login.php',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 