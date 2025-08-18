<?php
// Include session configuration and validation
require_once 'includes/session_config.php';

// Check if admin session is valid
if (!isAdminSessionValid()) {
    header('Location: ../login.php');
    exit;
}

include 'config/database.php';
$admin_id = $_SESSION['admin_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'update_personal_info':
                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $mobile = trim($_POST['mobile_number']);
                $address = trim($_POST['address']);

                // Handle file upload
                $photo_path = $_POST['existing_photo'];
                if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
                    $target_dir = "uploads/profile_photos/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    $filename = uniqid() . '_' . basename($_FILES["profile_photo"]["name"]);
                    $target_file = $target_dir . $filename;
                    if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                        $photo_path = $target_file;
                        
                        // Update session with new profile photo
                        $_SESSION['profile_photo_path'] = $photo_path;
                    } else {
                        throw new Exception("Error uploading file.");
                    }
                }
                
                $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, mobile_number = ?, address = ?, profile_photo_path = ? WHERE id = ?");
                $stmt->execute([$name, $email, $mobile, $address, $photo_path, $admin_id]);
                
                // Update session with new name
                $_SESSION['admin_name'] = $name;
                
                // Log profile update
                $log = $conn->prepare("INSERT INTO admin_access_logs (admin_id, action, action_time) VALUES (?, ?, NOW())");
                $log->execute([$admin_id, 'Updated profile information']);
                
                $_SESSION['message'] = 'Profile updated successfully.';
                $_SESSION['message_type'] = 'success';
                break;

            case 'update_credentials':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords do not match.");
                }

                $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
                $stmt->execute([$admin_id]);
                $admin = $stmt->fetch();

                if (!$admin || !password_verify($current_password, $admin['password'])) {
                    throw new Exception("Incorrect current password.");
                }

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $admin_id]);

                // Log password change
                $log = $conn->prepare("INSERT INTO admin_access_logs (admin_id, action, action_time) VALUES (?, ?, NOW())");
                $log->execute([$admin_id, 'Changed account password']);

                $_SESSION['message'] = 'Password updated successfully.';
                $_SESSION['message_type'] = 'success';
                break;
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }

    header("Location: profile.php");
    exit;
}

// Now, include the visual components
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';

// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Profile Settings', 'url' => 'profile.php', 'active' => true]
];

// Retrieve messages from session after potential redirect
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

// Fetch admin data for displaying on the page
try {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        throw new Exception("Admin user not found.");
    }

    // Set default values for any missing fields to avoid errors
    $admin = array_merge([
        'name' => 'N/A',
        'email' => 'N/A',
        'mobile_number' => '',
        'profile_photo_path' => 'uploads/profile_photos/default-avatar.png',
        'role' => 'Admin',
        'address' => '',
        'status' => 'Active',
        'last_login' => date('Y-m-d H:i:s')
    ], $admin);

    // Fetch activity logs (example)
    $log_stmt = $conn->prepare("SELECT action, action_time FROM admin_access_logs WHERE admin_id = ? ORDER BY action_time DESC LIMIT 20");
    $log_stmt->execute([$admin_id]);
    $logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = 'Error fetching data: ' . $e->getMessage();
    $message_type = 'danger';
    $admin = [];
    $logs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - SLSU Alumni</title>
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
            padding-top: 5rem;
        }

        @media (min-width: 1024px) {
            .main-container {
                margin-left: 16rem;
                width: calc(100% - 16rem);
            }
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            border: 1px solid #e2e8f0;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .profile-photo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #3b82f6;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }

        .profile-photo:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(59, 130, 246, 0.4);
        }

        .status-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 3px solid white;
            background: #10b981;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .profile-info {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .profile-role {
            color: #64748b;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .profile-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            background: #dcfce7;
            color: #166534;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .nav-tabs {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-tab {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-tab:hover {
            background: #f1f5f9;
            color: #3b82f6;
        }

        .nav-tab.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .profile-main {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-content {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
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
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
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

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .photo-upload-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px dashed #d1d5db;
            border-radius: 0.75rem;
            background: #f9fafb;
            transition: all 0.3s ease;
        }

        .photo-upload-container:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .photo-preview {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3b82f6;
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }

        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }

        .log-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
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
            font-size: 0.875rem;
            color: #64748b;
        }

        @media (max-width: 1024px) {
            .main-container {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }

            .profile-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .profile-sidebar {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 640px) {
            .section-content {
                padding: 1.5rem;
            }

            .photo-upload-container {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <?php include 'includes/breadcrumb.php'; ?>
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                Profile Settings
            </h1>
            <p class="text-gray-600 mt-1">Manage your account information and security settings</p>
        </div>

        <div class="profile-container">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-photo-container">
                    <img src="<?php echo htmlspecialchars($admin['profile_photo_path']); ?>" alt="Profile Photo" class="profile-photo" id="profile-photo-preview">
                    <div class="status-badge"></div>
                </div>

                <div class="profile-info">
                    <h2 class="profile-name"><?php echo htmlspecialchars($admin['name']); ?></h2>
                    <p class="profile-role"><?php echo htmlspecialchars($admin['role']); ?></p>
                    <div class="profile-status">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo htmlspecialchars($admin['status']); ?>
                    </div>
                </div>

                <nav class="nav-tabs">
                    <a href="#personal-info" class="nav-tab active" data-tab="personal-info">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Personal Information
                    </a>
                    <a href="#credentials" class="nav-tab" data-tab="credentials">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2m0 0V7a2 2 0 012-2m3 0a2 2 0 00-2-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Security Settings
                    </a>
                    <a href="#activity-log" class="nav-tab" data-tab="activity-log">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Activity Log
                    </a>
                </nav>
            </div>

            <!-- Profile Main Content -->
            <div class="profile-main">
                <!-- Alerts -->
                <?php if ($message): ?>
                    <div class="section-content">
                        <div class="alert alert-<?php echo $message_type === 'danger' ? 'danger' : 'success'; ?>">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <?php if ($message_type === 'success'): ?>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                <?php else: ?>
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                <?php endif; ?>
                            </svg>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Personal Information Tab -->
                <div id="personal-info" class="tab-content">
                    <div class="section-header">
                        <h3 class="section-title">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Personal Information
                        </h3>
                    </div>
                    <div class="section-content">
                        <form action="profile.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_personal_info">
                            <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($admin['profile_photo_path']); ?>">

                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Full Name
                                </label>
                                <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email Address
                                </label>
                                <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Mobile Number
                                </label>
                                <input type="text" name="mobile_number" class="form-input" value="<?php echo htmlspecialchars($admin['mobile_number']); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Address
                                </label>
                                <input type="text" name="address" class="form-input" value="<?php echo htmlspecialchars($admin['address']); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Profile Photo
                                </label>
                                <div class="photo-upload-container">
                                    <img src="<?php echo htmlspecialchars($admin['profile_photo_path']); ?>" alt="Current Photo" class="photo-preview" id="photo-upload-preview">
                                    <div class="flex-1">
                                        <input type="file" name="profile_photo" class="form-input" accept="image/png, image/jpeg" id="profile_photo">
                                        <p class="text-sm text-gray-500 mt-1">Choose a new profile photo (PNG or JPEG)</p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Security Settings Tab -->
                <div id="credentials" class="tab-content" style="display: none;">
                    <div class="section-header">
                        <h3 class="section-title">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2m0 0V7a2 2 0 012-2m3 0a2 2 0 00-2-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Security Settings
                        </h3>
                    </div>
                    <div class="section-content">
                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="update_credentials">

                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Current Password
                                </label>
                                <div class="password-wrapper">
                                    <input type="password" name="current_password" class="form-input" required>
                                    <i class="fas fa-eye password-toggle"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2m0 0V7a2 2 0 012-2m3 0a2 2 0 00-2-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    New Password
                                </label>
                                <div class="password-wrapper">
                                    <input type="password" name="new_password" class="form-input" required minlength="8">
                                    <i class="fas fa-eye password-toggle"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Confirm New Password
                                </label>
                                <div class="password-wrapper">
                                    <input type="password" name="confirm_password" class="form-input" required>
                                    <i class="fas fa-eye password-toggle"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Update Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Activity Log Tab -->
                <div id="activity-log" class="tab-content" style="display: none;">
                    <div class="section-header">
                        <h3 class="section-title">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Activity Log
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="activity-log">
                            <?php if (!empty($logs)): ?>
                                <?php foreach($logs as $log): ?>
                                    <div class="log-item">
                                        <div class="log-action"><?php echo htmlspecialchars($log['action']); ?></div>
                                        <div class="log-time">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <?php echo date('M j, Y g:i A', strtotime($log['action_time'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500">No recent activity found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabLinks = document.querySelectorAll('.nav-tab');
            const tabContents = document.querySelectorAll('.tab-content');

            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs and contents
                    tabLinks.forEach(l => l.classList.remove('active'));
                    tabContents.forEach(c => c.style.display = 'none');
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const targetTab = this.getAttribute('data-tab');
                    document.getElementById(targetTab).style.display = 'block';
                });
            });

            // Password visibility toggle
            const passwordToggles = document.querySelectorAll('.password-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const passwordInput = this.previousElementSibling;
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            });

            // Profile photo preview
            const photoInput = document.getElementById('profile_photo');
            const photoPreview = document.getElementById('photo-upload-preview');
            const mainPhotoPreview = document.getElementById('profile-photo-preview');

            if (photoInput && photoPreview) {
                photoInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            photoPreview.src = e.target.result;
                            mainPhotoPreview.src = e.target.result;
                        }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Auto-hide alerts
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
