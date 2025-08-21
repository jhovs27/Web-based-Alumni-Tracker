<?php
header('Content-Type: application/json');
require_once 'includes/session_config.php';
if (!isChairSessionValid()) {
    echo json_encode([
        'success' => false,
        'redirect' => '../login.php',
        'message' => 'Session expired'
    ]);
    exit;
}
require_once '../admin/config/database.php';
try {
    if (refreshChairSession($conn)) {
        echo json_encode([
            'success' => true,
            'profile_photo_path' => isset($_SESSION['profile_photo_path']) && $_SESSION['profile_photo_path'] ? (strpos($_SESSION['profile_photo_path'], 'ui-avatars.com') === false ? '../admin/chair-uploads/' . $_SESSION['profile_photo_path'] : $_SESSION['profile_photo_path']) : null,
            'chair_name' => $_SESSION['chair_name'] ?? 'Program Chair',
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