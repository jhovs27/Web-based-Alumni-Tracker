<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// // Check if user is admin
// if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
//     header('Location: ../login.php');
//     exit;
// }

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
include 'includes/breadcrumb.php';
include 'config/database.php';

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_code':
                if (isset($_POST['access_code'])) {
                    $new_code = trim($_POST['access_code']);
                    if (!empty($new_code) && strlen($new_code) >= 4) {
                        // Store hashed code for security
                        $hashed_code = password_hash($new_code, PASSWORD_DEFAULT);
                        $admin_id = $_SESSION['user_id'] ?? 0;

                        try {
                            // Check if code exists
                            $stmt = $conn->prepare("SELECT id FROM admin_access_codes LIMIT 1");
                            $stmt->execute();
                            
                            if ($stmt->rowCount() > 0) {
                                // Update existing code
                                $update = $conn->prepare("UPDATE admin_access_codes SET code_hash = ?, code_plain = ?, updated_at = NOW(), updated_by = ? WHERE id = 1");
                                $update->execute([$hashed_code, $new_code, $admin_id]);
                            } else {
                                // Insert new code
                                $insert = $conn->prepare("INSERT INTO admin_access_codes (code_hash, code_plain, updated_at, updated_by, is_enabled) VALUES (?, ?, NOW(), ?, 1)");
                                $insert->execute([$hashed_code, $new_code, $admin_id]);
                            }
                            
                            // Log activity
                            $log = $conn->prepare("INSERT INTO admin_access_logs (admin_id, action, action_time) VALUES (?, 'Updated admin access code', NOW())");
                            $log->execute([$admin_id]);
                            
                            $message = 'Admin access code updated successfully.';
                            $message_type = 'success';
                        } catch (Exception $e) {
                            $message = 'Error updating access code: ' . $e->getMessage();
                            $message_type = 'error';
                        }
                    } else {
                        $message = 'Access code must be at least 4 characters long.';
                        $message_type = 'error';
                    }
                }
                break;

            case 'toggle_status':
                $new_status = $_POST['toggle_status'] === '1' ? 1 : 0;
                $admin_id = $_SESSION['user_id'] ?? 0;
                
                try {
                    $update = $conn->prepare("UPDATE admin_access_codes SET is_enabled = ?, updated_at = NOW(), updated_by = ? WHERE id = 1");
                    $update->execute([$new_status, $admin_id]);
                    
                    // Log activity
                    $action = $new_status ? 'Enabled admin access code' : 'Disabled admin access code';
                    $log = $conn->prepare("INSERT INTO admin_access_logs (admin_id, action, action_time) VALUES (?, ?, NOW())");
                    $log->execute([$admin_id, $action]);
                    
                    $message = 'Admin access code verification ' . ($new_status ? 'enabled' : 'disabled') . '.';
                    $message_type = 'success';
                } catch (Exception $e) {
                    $message = 'Error updating status: ' . $e->getMessage();
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Fetch current code and status
try {
    $stmt = $conn->prepare("SELECT code_plain, updated_at, is_enabled, updated_by FROM admin_access_codes WHERE id = 1");
    $stmt->execute();
    
    $current_code = 'SLSU-HC_ADMIN_2025'; // Default fallback
    $last_updated = date('Y-m-d H:i:s');
    $is_enabled = 1;
    $updated_by = '';
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $current_code = $row['code_plain'];
        $last_updated = $row['updated_at'];
        $is_enabled = $row['is_enabled'];
        $updated_by = $row['updated_by'];
    }

    // Fetch admin name for last update
    $admin_name = 'System Admin';
    if ($updated_by) {
        $stmt = $conn->prepare("SELECT name FROM admins WHERE id = ?");
        $stmt->execute([$updated_by]);
        if ($admin = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $admin_name = $admin['name'];
        }
    }

    // Fetch activity log
    $log_stmt = $conn->prepare("SELECT l.action, l.action_time, a.name FROM admin_access_logs l LEFT JOIN admins a ON l.admin_id = a.id ORDER BY l.action_time DESC LIMIT 10");
    $log_stmt->execute();
    $logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $message = 'Error fetching data: ' . $e->getMessage();
    $message_type = 'error';
    $logs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access Code - SLSU Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .main-container {
            padding: 2rem;
            margin-left: 0;
            width: 100%;
            min-height: 100vh;
            padding-top: 10rem;
        }

        @media (min-width: 1024px) {
            .main-container {
                margin-left: 16rem;
                width: calc(100% - 16rem);
                padding-top: 8rem;
            }
        }

        .access-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            max-width: 600px;
            margin: 0 auto;
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .card-description {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.6;
        }

        .card-content {
            padding: 2rem;
        }

        .current-code-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .current-code {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e40af;
            background: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: 2px solid #3b82f6;
            margin: 1rem 0;
            letter-spacing: 2px;
        }

        .status-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .status-enabled {
            background: #dcfce7;
            color: #166534;
        }

        .status-disabled {
            background: #fee2e2;
            color: #991b1b;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .activity-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .activity-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .activity-log {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
        }

        .log-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s ease;
        }

        .log-item:hover {
            background: #f8fafc;
        }

        .log-item:last-child {
            border-bottom: none;
        }

        .log-action {
            font-weight: 500;
            color: #374151;
        }

        .log-time {
            font-size: 0.75rem;
            color: #64748b;
        }

        @media (max-width: 640px) {
            .main-container {
                padding: 1rem;
                margin-left: 0;
                width: 100%;
            }

            .card-header,
            .card-content {
                padding: 1.5rem;
            }

            .status-info {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .log-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Breadcrumb -->
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Settings', 'url' => ''],
            ['title' => 'Admin Access Code', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        Admin Access Code
                    </h1>
                    <p class="text-gray-600 mt-1">Manage the security code required for admin registration</p>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Main Card -->
        <div class="access-card">
            <div class="card-header">
                <h2 class="card-title">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Security Access Code
                </h2>
                <p class="card-description">
                    Set or update the code required for admin registration. Keep it secure and share only with authorized personnel.
                </p>
            </div>

            <div class="card-content">
                <!-- Alerts -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <?php if ($message_type === 'success'): ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            <?php else: ?>
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            <?php endif; ?>
                        </svg>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Current Code Display -->
                <div class="current-code-section">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Current Access Code</h3>
                    <div class="current-code"><?php echo htmlspecialchars($current_code); ?></div>
                    <div class="status-info">
                        <span>Last updated: <?php echo date('M j, Y g:i A', strtotime($last_updated)); ?></span>
                        <span class="status-badge <?php echo $is_enabled ? 'status-enabled' : 'status-disabled'; ?>">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <?php if ($is_enabled): ?>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                <?php else: ?>
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                <?php endif; ?>
                            </svg>
                            <?php echo $is_enabled ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                </div>

                <!-- Update Code Form -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="action" value="update_code">
                    <div class="form-group">
                        <label for="access_code" class="form-label">
                            <svg class="w-4 h-4 text-blue-600 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2m0 0V7a2 2 0 012-2m3 0a2 2 0 00-2-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            New Access Code
                        </label>
                        <input type="text" 
                               id="access_code" 
                               name="access_code" 
                               required 
                               minlength="4" 
                               maxlength="32" 
                               class="form-input" 
                               placeholder="Enter new access code">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Update Access Code
                    </button>
                </form>

                <!-- Toggle Status Form -->
                <form method="POST">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="toggle_status" value="<?php echo $is_enabled ? '0' : '1'; ?>">
                    <button type="submit" class="btn <?php echo $is_enabled ? 'btn-danger' : 'btn-success'; ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php if ($is_enabled): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                            <?php else: ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <?php endif; ?>
                        </svg>
                        <?php echo $is_enabled ? 'Disable' : 'Enable'; ?> Verification
                    </button>
                </form>

                <!-- Activity Log Section -->
                <?php if (!empty($logs)): ?>
                    <div class="activity-section">
                        <h3 class="activity-title">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Recent Activity
                        </h3>
                        <div class="activity-log">
                            <?php foreach($logs as $log): ?>
                                <div class="log-item">
                                    <div class="log-action"><?php echo htmlspecialchars($log['action']); ?></div>
                                    <div class="log-time">
                                        <?php echo date('M j, Y g:i A', strtotime($log['action_time'])); ?>
                                        <?php if ($log['name']): ?>
                                            <br><span class="text-xs">by <?php echo htmlspecialchars($log['name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });

        // Session keepalive
        setInterval(function() {
            fetch('session_refresh.php', { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (!data.success && data.redirect) {
                        window.location.href = data.redirect;
                    }
                })
                .catch(() => {});
        }, 5 * 60 * 1000);
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
