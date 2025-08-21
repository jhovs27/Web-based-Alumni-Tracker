<?php
// Admin Session Configuration - must be included at the very beginning of admin files
if (session_status() === PHP_SESSION_NONE) {
    // Set session timeout to 24 hours (86400 seconds)
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    
    // Start session
    session_start();
}

// Function to refresh session and update profile data
function refreshAdminSession($conn) {
    if (isset($_SESSION['admin_id'])) {
        try {
            // Fetch updated admin data
            $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                // Update session with fresh data
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['profile_photo_path'] = $admin['profile_photo_path'] ?? null;
                $_SESSION['admin_status'] = $admin['status'] ?? 'active';
                
                // Update session timestamp
                $_SESSION['last_activity'] = time();
                
                return true;
            }
        } catch (Exception $e) {
            error_log("Error refreshing admin session: " . $e->getMessage());
        }
    }
    return false;
}

// Function to check if session is still valid
function isAdminSessionValid() {
    $timeout = 86400; // 24 hours
    
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
        return false;
    }
    
    if (isset($_SESSION['admin_status']) && $_SESSION['admin_status'] === 'suspended') {
        // Only allow access to dashboard (index.php) if suspended
        if (basename($_SERVER['PHP_SELF']) !== 'index.php') {
            header('Location: index.php');
            exit;
        }
    }
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

// Auto-refresh session every 30 minutes
if (isset($_SESSION['admin_id']) && (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > 1800)) {
    require_once __DIR__ . '/../config/database.php';
    refreshAdminSession($conn);
}
?> 