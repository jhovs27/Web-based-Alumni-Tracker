<?php
// Include session configuration and validation
require_once 'includes/session_config.php';

// Check if admin session is valid
if (!isAdminSessionValid()) {
    header('Location: ../login.php');
    exit;
}

require_once 'config/database.php';

// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => true]
];

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';

$admin_id = $_SESSION['admin_id'] ?? 0;

// Fetch summary counts
try {
    $graduates = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
} catch (Exception $e) { $graduates = 0; }

try {
    $alumni = $conn->query("SELECT COUNT(*) FROM alumni")->fetchColumn();
} catch (Exception $e) { $alumni = 0; }

try {
    $events = $conn->query("SELECT COUNT(*) FROM alumni_events")->fetchColumn();
} catch (Exception $e) { $events = 0; }

try {
    $job_posts = $conn->query("SELECT COUNT(*) FROM job_posts")->fetchColumn();
} catch (Exception $e) { $job_posts = 0; }

try {
    $alumni_ids_generated = $conn->query("SELECT COUNT(*) FROM alumni_ids")->fetchColumn();
} catch (Exception $e) { $alumni_ids_generated = 0; }

try {
    $program_chair_accounts = $conn->query("SELECT COUNT(*) FROM program_chairs")->fetchColumn();
} catch (Exception $e) { $program_chair_accounts = 0; }

try {
    $total_surveys = $conn->query("SELECT COUNT(*) FROM survey")->fetchColumn();
} catch (Exception $e) { $total_surveys = 0; }

// Fetch recent activity logs
$logs = [];
if ($admin_id) {
    $log_stmt = $conn->prepare("SELECT action, action_time FROM admin_access_logs WHERE admin_id = ? ORDER BY action_time DESC LIMIT 10");
    $log_stmt->execute([$admin_id]);
    $logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch latest published job posts (limit 2)
$latest_jobs = [];
try {
    $stmt = $conn->query("SELECT id, job_title, company_name, job_type, posted_date, job_description, location FROM job_posts WHERE status = 'published' ORDER BY posted_date DESC LIMIT 2");
    $latest_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Fetch latest published alumni events (limit 2)
$latest_events = [];
try {
    $stmt = $conn->query("SELECT id, event_title, start_datetime, event_description, event_type, physical_address, online_link FROM alumni_events WHERE status = 'published' ORDER BY start_datetime DESC LIMIT 2");
    $latest_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SLSU Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
        }
        
        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .quick-action-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }
        
        .quick-action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }
        
        .activity-item {
            border-left: 3px solid #e5e7eb;
            padding-left: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            border-left-color: #3b82f6;
            background-color: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-left: -0.75rem;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100 min-h-screen">
    <!-- Main Content -->
    <div class="main-content min-h-screen pt-16 lg:ml-64 transition-all duration-300">
        <div class="p-6">
            <?php include 'includes/breadcrumb.php'; ?>
            
            <?php if (isset($_SESSION['admin_status']) && $_SESSION['admin_status'] === 'suspended'): ?>
                <div class="mb-6">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Account Suspended</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>Your account is currently suspended. You cannot access most features. Please contact the system administrator for assistance.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Welcome Section -->
            <div class="mb-8">
                <div class="glass-effect rounded-2xl p-6 border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                                Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>! 👋
                            </h1>
                            <p class="text-gray-600">Here's what's happening with your alumni system today.</p>
                        </div>
                        <div class="hidden lg:block">
                            <div class="w-24 h-24 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Graduates -->
                <div class="dashboard-card p-6">
                    <div class="stat-icon bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1"><?php echo number_format($graduates); ?></div>
                    <div class="text-sm text-gray-600 font-medium">Total Graduates</div>
                    <div class="mt-2 flex items-center text-xs text-green-600">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Active records</span>
                    </div>
                </div>

                <!-- Registered Alumni -->
                <div class="dashboard-card p-6">
                    <div class="stat-icon bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1"><?php echo number_format($alumni); ?></div>
                    <div class="text-sm text-gray-600 font-medium">Registered Alumni</div>
                    <div class="mt-2 flex items-center text-xs text-green-600">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Platform users</span>
                    </div>
                </div>

                <!-- Alumni Events -->
                <div class="dashboard-card p-6">
                    <div class="stat-icon bg-purple-100 text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1"><?php echo number_format($events); ?></div>
                    <div class="text-sm text-gray-600 font-medium">Alumni Events</div>
                    <div class="mt-2 flex items-center text-xs text-purple-600">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Total events</span>
                    </div>
                </div>

                <!-- Job Posts -->
                <div class="dashboard-card p-6">
                    <div class="stat-icon bg-orange-100 text-orange-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2m8 0H8m8 0v2a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2V8m8 0V6a2 2 0 0 0-2-2H10a2 2 0 0 0-2 2v2"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1"><?php echo number_format($job_posts); ?></div>
                    <div class="text-sm text-gray-600 font-medium">Job Opportunities</div>
                    <div class="mt-2 flex items-center text-xs text-orange-600">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                        <span>Available positions</span>
                    </div>
                </div>
            </div>

            <!-- Secondary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Alumni IDs Generated -->
                <div class="dashboard-card p-6">
                    <div class="stat-icon bg-indigo-100 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m-4 0a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-4 0v2m4-2v2"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1"><?php echo number_format($alumni_ids_generated); ?></div>
                    <div class="text-sm text-gray-600 font-medium">Alumni IDs Generated</div>
                </div>

                <!-- Program Chair Accounts -->
                <div class="dashboard-card p-6">
                    <div class="stat-icon bg-teal-100 text-teal-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1"><?php echo number_format($program_chair_accounts); ?></div>
                    <div class="text-sm text-gray-600 font-medium">Program Chair Accounts</div>
                </div>

                <!-- Total Surveys -->
                <div class="dashboard-card p-6">
                    <div class="stat-icon bg-pink-100 text-pink-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1"><?php echo number_format($total_surveys); ?></div>
                    <div class="text-sm text-gray-600 font-medium">Total Surveys</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-8">
                <div class="glass-effect rounded-2xl p-6 border border-white/20">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Quick Actions
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="graduate-lists.php" class="quick-action-card group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Manage Graduates</div>
                                    <div class="text-sm text-gray-500">View and manage graduate records</div>
                                </div>
                            </div>
                        </a>

                        <a href="create-posts.php" class="quick-action-card group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Create Job Post</div>
                                    <div class="text-sm text-gray-500">Add new job opportunities</div>
                                </div>
                            </div>
                        </a>

                        <a href="create-event.php" class="quick-action-card group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Create Event</div>
                                    <div class="text-sm text-gray-500">Schedule alumni events</div>
                                </div>
                            </div>
                        </a>

                        <a href="alumni-id.php" class="quick-action-card group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m-4 0a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-4 0v2m4-2v2"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Generate Alumni ID</div>
                                    <div class="text-sm text-gray-500">Create alumni identification</div>
                                </div>
                            </div>
                        </a>

                        <a href="reports.php" class="quick-action-card group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">View Reports</div>
                                    <div class="text-sm text-gray-500">Analytics and insights</div>
                                </div>
                            </div>
                        </a>

                        <a href="profile.php" class="quick-action-card group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.128 2.004.128 3 0"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Profile Settings</div>
                                    <div class="text-sm text-gray-500">Manage your account</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Latest Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Latest Events -->
                <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                </svg>
                                Latest Alumni Events
                            </h2>
                            <a href="manage-events.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                                View all →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if (empty($latest_events)): ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-sm">No events found.</p>
                                <p class="text-gray-400 text-xs mt-1">Create your first event to get started!</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($latest_events as $event): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-2">
                                                        <?php echo htmlspecialchars($event['event_type'] ?? 'Event'); ?>
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        <?php echo date('M d, Y g:i A', strtotime($event['start_datetime'])); ?>
                                                    </span>
                                                </div>
                                                <h3 class="font-semibold text-gray-900 mb-1">
                                                    <?php echo htmlspecialchars($event['event_title']); ?>
                                                </h3>
                                                <p class="text-sm text-gray-600 mb-2">
                                                    <?php echo htmlspecialchars(substr($event['event_description'] ?? '', 0, 100)) . (strlen($event['event_description'] ?? '') > 100 ? '...' : ''); ?>
                                                </p>
                                                <div class="flex items-center text-xs text-gray-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <?php if (!empty($event['physical_address'])): ?>
                                                        <span><?php echo htmlspecialchars($event['physical_address']); ?></span>
                                                    <?php elseif (!empty($event['online_link'])): ?>
                                                        <span>Online Event</span>
                                                    <?php else: ?>
                                                        <span>Location TBA</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <a href="edit-event.php?id=<?php echo $event['id']; ?>" 
                                                   class="inline-flex items-center px-3 py-1.5 border border-purple-300 text-xs font-medium rounded-md text-purple-700 bg-purple-50 hover:bg-purple-100 transition-colors duration-200">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Latest Job Posts -->
                <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2m8 0H8m8 0v2a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2V8m8 0V6a2 2 0 0 0-2-2H10a2 2 0 0 0-2 2v2"/>
                                </svg>
                                Latest Job Posts
                            </h2>
                            <a href="manage-posts.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                                View all →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if (empty($latest_jobs)): ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2m8 0H8m8 0v2a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2V8m8 0V6a2 2 0 0 0-2-2H10a2 2 0 0 0-2 2v2"/>
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-sm">No job posts found.</p>
                                <p class="text-gray-400 text-xs mt-1">Create your first job post to get started!</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($latest_jobs as $job): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">
                                                        <?php echo htmlspecialchars($job['company_name']); ?>
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        <?php echo date('M d, Y', strtotime($job['posted_date'])); ?>
                                                    </span>
                                                </div>
                                                <h3 class="font-semibold text-gray-900 mb-1">
                                                    <?php echo htmlspecialchars($job['job_title']); ?>
                                                </h3>
                                                <p class="text-sm text-gray-600 mb-2">
                                                    <?php echo htmlspecialchars(substr($job['job_description'] ?? '', 0, 100)) . (strlen($job['job_description'] ?? '') > 100 ? '...' : ''); ?>
                                                </p>
                                                <div class="flex items-center text-xs text-gray-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span><?php echo htmlspecialchars($job['location'] ?? 'Location not specified'); ?></span>
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <a href="edit-post.php?id=<?php echo $job['id']; ?>" 
                                                   class="inline-flex items-center px-3 py-1.5 border border-orange-300 text-xs font-medium rounded-md text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors duration-200">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="glass-effect rounded-2xl border border-white/20 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Recent Activity (This Session)
                </h2>
                
                <?php if (!empty($logs)): ?>
                    <div class="space-y-3">
                        <?php foreach ($logs as $log): ?>
                            <div class="activity-item">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                        <span class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($log['action']); ?>
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                        <?php echo date('M d, Y g:i A', strtotime($log['action_time'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm">No recent activity found for this session.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Session Keepalive Script -->
    <script>
        setInterval(function() {
            fetch('session_refresh.php', { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (!data.success && data.redirect) {
                        window.location.href = data.redirect;
                    }
                })
                .catch(() => {});
        }, 5 * 60 * 1000); // every 5 minutes
    </script>
</body>
</html>
