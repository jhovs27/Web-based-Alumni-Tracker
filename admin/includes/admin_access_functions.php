<?php
/**
 * Admin Access Code Verification Functions
 * These functions handle the verification of admin access codes during registration
 */

/**
 * Verify if the provided admin access code is valid
 * @param string $code The access code to verify
 * @param PDO $conn Database connection
 * @return array Array with 'valid' boolean and 'message' string
 */
function verifyAdminAccessCode($code, $conn) {
    try {
        // Check if admin access verification is enabled
        $stmt = $conn->prepare("SELECT code_hash, is_enabled FROM admin_access_codes WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return [
                'valid' => false,
                'message' => 'Admin access code system is not configured.'
            ];
        }
        
        if (!$result['is_enabled']) {
            return [
                'valid' => false,
                'message' => 'Admin access code verification is currently disabled.'
            ];
        }
        
        // Verify the code
        if (password_verify($code, $result['code_hash'])) {
            return [
                'valid' => true,
                'message' => 'Access code verified successfully.'
            ];
        } else {
            return [
                'valid' => false,
                'message' => 'Invalid admin access code.'
            ];
        }
        
    } catch (Exception $e) {
        return [
            'valid' => false,
            'message' => 'Error verifying access code: ' . $e->getMessage()
        ];
    }
}

/**
 * Log admin access code verification attempt
 * @param int $admin_id Admin ID
 * @param string $action Action performed
 * @param PDO $conn Database connection
 */
function logAdminAccessAction($admin_id, $action, $conn) {
    try {
        $stmt = $conn->prepare("INSERT INTO admin_access_logs (admin_id, action, action_time) VALUES (?, ?, NOW())");
        $stmt->execute([$admin_id, $action]);
    } catch (Exception $e) {
        // Log error silently to avoid breaking the main flow
        error_log("Failed to log admin access action: " . $e->getMessage());
    }
}

/**
 * Get current admin access code status
 * @param PDO $conn Database connection
 * @return array Status information
 */
function getAdminAccessStatus($conn) {
    try {
        $stmt = $conn->prepare("SELECT is_enabled, updated_at FROM admin_access_codes WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'enabled' => $result ? (bool)$result['is_enabled'] : false,
            'last_updated' => $result ? $result['updated_at'] : null
        ];
    } catch (Exception $e) {
        return [
            'enabled' => false,
            'last_updated' => null,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Check if admin access code verification is required
 * @param PDO $conn Database connection
 * @return bool True if verification is required
 */
function isAdminAccessRequired($conn) {
    $status = getAdminAccessStatus($conn);
    return $status['enabled'];
}
?>
