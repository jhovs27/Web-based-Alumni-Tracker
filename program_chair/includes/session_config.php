<?php
// Program Chair Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    session_start();
}

// Function to refresh session and update profile data
function refreshChairSession($conn) {
    if (isset($_SESSION['chair_id'])) {
        try {
            $stmt = $conn->prepare("SELECT * FROM program_chairs WHERE id = ?");
            $stmt->execute([$_SESSION['chair_id']]);
            $chair = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($chair) {
                $_SESSION['chair_name'] = $chair['full_name'];
                $_SESSION['profile_photo_path'] = $chair['profile_picture'] ?? null;
                $_SESSION['chair_email'] = $chair['email'];
                $_SESSION['chair_program'] = $chair['program'];
                $_SESSION['chair_designation'] = $chair['Designation'];
                $_SESSION['last_activity'] = time();
                return true;
            }
        } catch (Exception $e) {}
    }
    return false;
}

function isChairSessionValid() {
    $timeout = 86400; // 24 hours
    if (!isset($_SESSION['chair_id']) || !isset($_SESSION['is_chair'])) {
        return false;
    }
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

// Auto-refresh session every 30 minutes
if (isset($_SESSION['chair_id']) && (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > 1800)) {
    require_once __DIR__ . '/../../admin/config/database.php';
    refreshChairSession($conn);
}
?> 