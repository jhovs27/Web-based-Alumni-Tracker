<?php
session_start();

require_once '../admin/config/database.php';

// Debug: Check session
error_log("=== ALUMNI HEADER DEBUG ===");
error_log("Session data: " . print_r($_SESSION, true));
error_log("is_alumni: " . (isset($_SESSION['is_alumni']) ? $_SESSION['is_alumni'] : 'NOT SET'));
error_log("alumni_id: " . (isset($_SESSION['alumni_id']) ? $_SESSION['alumni_id'] : 'NOT SET'));

// Check if user is logged in as alumni
if (!isset($_SESSION['is_alumni']) || !isset($_SESSION['alumni_id'])) {
    error_log("Session check failed - redirecting to login");
    error_log("is_alumni: " . (isset($_SESSION['is_alumni']) ? $_SESSION['is_alumni'] : 'NOT SET'));
    error_log("alumni_id: " . (isset($_SESSION['alumni_id']) ? $_SESSION['alumni_id'] : 'NOT SET'));
    header('Location: ../login.php');
    exit;
}

// Check if login is not too old (optional security measure)
$login_timeout = 24 * 60 * 60; // 24 hours
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $login_timeout) {
    error_log("Session expired - redirecting to login");
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// Fetch alumni data from alumni table
$alumni_id = $_SESSION['alumni_id'];
try {
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ?");
    $stmt->execute([$alumni_id]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$alumni) {
        error_log("Alumni not found in database - ID: " . $alumni_id);
        session_destroy();
        header('Location: ../login.php');
        exit;
    }
    
    // Handle missing fields gracefully
    $alumni['first_name'] = $alumni['first_name'] ?? '';
    $alumni['last_name'] = $alumni['last_name'] ?? '';
    $alumni['email'] = $alumni['email'] ?? '';
    $alumni['student_no'] = $alumni['student_no'] ?? '';
    $alumni['course'] = $alumni['course'] ?? '';
    $alumni['employment_status'] = $alumni['employment_status'] ?? '';
    
    error_log("Alumni found: " . ($alumni['first_name'] . ' ' . $alumni['last_name']));
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    session_destroy();
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Alumni Portal - SLSU-HC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
            }
        }
        .main-content {
            transition: margin-left 0.3s ease-in-out;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .logo-circle {
            aspect-ratio: 1 !important;
            flex-shrink: 0 !important;
            object-fit: cover !important;
            border-radius: 50% !important;
        }
        
        /* Footer positioning fixes */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        footer {
            margin-top: auto;
        }
        
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 18rem; /* 288px = 18rem, matches new sidebar width */
            }
            footer {
                margin-left: 18rem; /* 288px = 18rem, matches new sidebar width */
                width: calc(100% - 18rem);
            }
        }
        
        @media (max-width: 438px) {
            .nav-header{
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50"> 