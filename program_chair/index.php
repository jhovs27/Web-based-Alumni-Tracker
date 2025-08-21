<?php
require_once 'includes/session_config.php';
if (!isChairSessionValid()) {
    header('Location: ../login.php');
    exit();
}

require_once '../admin/config/database.php';
$chair_name = $_SESSION['chair_name'] ?? 'Program Chair';
$chair_designation = $_SESSION['chair_designation'] ?? '';
$profile_photo = $_SESSION['profile_photo_path'] ?? 'https://ui-avatars.com/api/?name=Chair&background=0D8ABC&color=fff';
$chair_program = $_SESSION['chair_program'] ?? '';

// Fetch total graduates in department (alumni with year_graduated not null)
$total_graduates = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM alumni WHERE program = ? AND year_graduated IS NOT NULL");
    $stmt->execute([$chair_program]);
    $total_graduates = $stmt->fetchColumn();
} catch (PDOException $e) { $total_graduates = 0; }

// Fetch total registered alumni in department (all alumni with program = chair_program)
$total_alumni = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM alumni WHERE program = ?");
    $stmt->execute([$chair_program]);
    $total_alumni = $stmt->fetchColumn();
} catch (PDOException $e) { $total_alumni = 0; }

// Fetch total employed alumni in department
$total_employed = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM alumni WHERE program = ? AND (employment_status = 'employed' OR employment_status = 'self-employed')");
    $stmt->execute([$chair_program]);
    $total_employed = $stmt->fetchColumn();
} catch (PDOException $e) { $total_employed = 0; }

// Fetch 2 latest upcoming alumni events
$upcoming_events = [];
try {
    $stmt = $conn->prepare("SELECT id, event_title, event_description, event_type, start_datetime, end_datetime, physical_address, online_link, poster_image, status FROM alumni_events WHERE (status = 'Published' OR status = 'Active') AND start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 2");
    $stmt->execute();
    $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $upcoming_events = []; }

// Fetch latest published job posts (limit 2) for display in Job Opportunities section
$latest_jobs = [];
try {
    $stmt = $conn->query("SELECT id, job_title, company_name, job_type, posted_date, job_description, location FROM job_posts WHERE status = 'published' ORDER BY posted_date DESC LIMIT 2");
    $latest_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Fetch recent activity logs for the chair
$chair_id = $_SESSION['chair_id'] ?? 0;
$logs = [];
if ($chair_id) {
    try {
        $log_stmt = $conn->prepare("SELECT action, action_time FROM chair_access_logs WHERE chair_id = ? ORDER BY action_time DESC LIMIT 10");
        $log_stmt->execute([$chair_id]);
        $logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $logs = []; }
}

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'icon' => 'fa-home'],
];

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/breadcrumb.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 pt-16 min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Breadcrumb Navigation -->
            <div class="mb-6">
                <nav class="flex items-center space-x-2 text-sm text-gray-600 bg-white/80 backdrop-blur-sm rounded-xl px-4 py-3 shadow-sm border border-blue-100">
                    <?php foreach (
                        isset($breadcrumbs) ? $breadcrumbs : [] as $index => $breadcrumb): ?>
                        <?php if ($index > 0): ?>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        <?php endif; ?>
                        <?php if (isset($breadcrumb['url'])): ?>
                            <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>" class="flex items-center space-x-1 text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                <i class="fas <?php echo htmlspecialchars($breadcrumb['icon']); ?> text-xs"></i>
                                <span><?php echo htmlspecialchars($breadcrumb['label']); ?></span>
                            </a>
                        <?php else: ?>
                            <span class="flex items-center space-x-1 text-gray-800 font-medium">
                                <i class="fas <?php echo htmlspecialchars($breadcrumb['icon']); ?> text-xs"></i>
                                <span><?php echo htmlspecialchars($breadcrumb['label']); ?></span>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </div>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
                <!-- Total Graduates Card -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-6 border border-blue-100 hover:shadow-xl transition-all duration-300 hover:scale-105">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Graduates</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo number_format($total_graduates); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Registered Alumni Card -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-6 border border-green-100 hover:shadow-xl transition-all duration-300 hover:scale-105">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Registered Alumni</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo number_format($total_alumni); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Employed Alumni Card -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-6 border border-purple-100 hover:shadow-xl transition-all duration-300 hover:scale-105">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Employed Alumni</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo number_format($total_employed); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 mb-8">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Quick Actions
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="alumni_list.php" class="quick-action-card group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Manage Alumni List</div>
                                    <div class="text-sm text-gray-500">View and manage alumni records</div>
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
                        <a href="create-survey.php" class="quick-action-card group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Create Survey</div>
                                    <div class="text-sm text-gray-500">Gather alumni feedback</div>
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

            <!-- Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 lg:gap-8">
                <!-- Upcoming Events -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Upcoming Alumni Events
                            </h2>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-semibold transition-colors duration-200 flex items-center group">
                                View all 
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if (empty($upcoming_events)): ?>
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-sm">No upcoming events at the moment.</p>
                                <p class="text-gray-400 text-xs mt-1">Check back later for new events!</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($upcoming_events as $event): ?>
                                    <div class="border border-gray-100 rounded-xl p-4 hover:shadow-md transition-all duration-200 bg-white/50">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                                        <?php echo htmlspecialchars($event['event_type']); ?>
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <?php echo date('M d, Y g:i A', strtotime($event['start_datetime'])); ?>
                                                    </span>
                                                </div>
                                                <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                                <p class="text-sm text-gray-600 mb-2">
                                                    <?php echo htmlspecialchars(substr($event['event_description'], 0, 100)) . (strlen($event['event_description']) > 100 ? '...' : ''); ?>
                                                </p>
                                                <div class="flex items-center text-xs text-gray-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
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
                                                <a href="#" class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                    Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Latest Job Posts (imitating admin panel) -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-orange-100">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                <svg class="w-6 h-6 text-orange-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2m8 0H8m8 0v2a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2V8m8 0V6a2 2 0 0 0-2-2H10a2 2 0 0 0-2 2v2"/>
                                </svg>
                                Latest Job Posts
                            </h2>
                            <a href="manage-posts.php" class="text-orange-600 hover:text-orange-800 text-sm font-semibold transition-colors duration-200 flex items-center group">
                                View all 
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
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
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6 mt-8">
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
    </main>

    <script>
    // Sidebar toggle functionality
    const toggleSidebar = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleSidebar && sidebar && overlay) {
        toggleSidebar.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }

    // Session refresh mechanism
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

    // Close mobile menu when clicking on links
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) { // lg breakpoint
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) { // lg breakpoint
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
        }
    });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
