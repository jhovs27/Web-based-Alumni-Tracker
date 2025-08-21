<?php
// Set page title for header
$page_title = "Dashboard";

// Include header (which handles session and database connection)
include 'includes/header.php';
?>

<?php
// Fetch upcoming events with better formatting
$recent_events = [];
try {
    $stmt = $conn->prepare("
        SELECT id, event_title, event_description, event_type, start_datetime, end_datetime, 
               physical_address, online_link, contact_person, contact_email, poster_image, status
        FROM alumni_events 
        WHERE status = 'Published' AND start_datetime >= NOW()
        ORDER BY start_datetime ASC 
        LIMIT 6
    ");
    $stmt->execute();
    $recent_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the events found
    error_log("Found " . count($recent_events) . " upcoming events");
    foreach ($recent_events as $event) {
        error_log("Event: " . $event['event_title'] . " - " . $event['start_datetime'] . " - Status: " . $event['status']);
    }
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
    // Table might not exist yet, continue with empty array
    $recent_events = [];
}

// Fetch recent job posts with company logos
$recent_jobs = [];
try {
    // First, let's check what statuses exist in the job_posts table
    $statusCheck = $conn->query("SELECT DISTINCT status FROM job_posts LIMIT 10");
    $statuses = $statusCheck->fetchAll(PDO::FETCH_COLUMN);
    error_log("Available job statuses: " . implode(', ', $statuses));
    
    // Use 'Published' status if it exists, otherwise try 'Active'
    $statusFilter = in_array('Published', $statuses) ? 'Published' : 'Active';
    
    $stmt = $conn->prepare("
        SELECT id, job_title, job_description, company_name, company_logo, 
               location, salary_range, job_type, posted_date, application_deadline, status
        FROM job_posts 
        WHERE status = ? AND (application_deadline IS NULL OR application_deadline >= CURDATE())
        ORDER BY posted_date DESC 
        LIMIT 6
    ");
    $stmt->execute([$statusFilter]);
    $recent_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the jobs found
    error_log("Found " . count($recent_jobs) . " job opportunities with status: " . $statusFilter);
    foreach ($recent_jobs as $job) {
        error_log("Job: " . $job['job_title'] . " - " . $job['company_name'] . " - Status: " . $job['status']);
    }
} catch (PDOException $e) {
    error_log("Error fetching jobs: " . $e->getMessage());
    // Table might not exist yet, continue with empty array
    $recent_jobs = [];
}

// Get counts for stats
$total_events = count($recent_events);
$total_jobs = count($recent_jobs);
?>

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content lg:ml-72 pt-16 min-h-screen">
    <div class="p-6">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome back, <?php echo htmlspecialchars($alumni['first_name'] . ' ' . $alumni['last_name']); ?>!</h1>
            <p class="text-gray-600">Here's what's happening in your alumni community today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Upcoming Events</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_events; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-briefcase text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Job Opportunities</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_jobs; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Network Connections</p>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-certificate text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Certificates</p>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Events -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                        Upcoming Events
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (empty($recent_events)): ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar-times text-blue-500 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm">No upcoming events at the moment.</p>
                            <p class="text-gray-400 text-xs mt-1">Check back later for new events!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_events as $event): ?>
                                <div class="border border-gray-100 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                                    <?php echo htmlspecialchars($event['event_type']); ?>
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php echo date('M d, Y g:i A', strtotime($event['start_datetime'])); ?>
                                                </span>
                                            </div>
                                            <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                            <p class="text-sm text-gray-600 mb-2">
                                                <?php echo htmlspecialchars(substr($event['event_description'], 0, 100)) . (strlen($event['event_description']) > 100 ? '...' : ''); ?>
                                            </p>
                                            <div class="flex items-center text-xs text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
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
                                            <a href="events.php?id=<?php echo $event['id']; ?>" 
                                               class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-200">
                                                <i class="fas fa-external-link-alt mr-1"></i>
                                                Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <a href="events.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200">
                            View all events <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Job Posts -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-briefcase text-green-500 mr-2"></i>
                        Latest Job Opportunities
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (empty($recent_jobs)): ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-briefcase text-green-500 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm">No job opportunities available at the moment.</p>
                            <p class="text-gray-400 text-xs mt-1">Check back later for new positions!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_jobs as $job): ?>
                                <div class="border border-gray-100 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <?php if (!empty($job['company_logo'])): ?>
                                                    <img src="../admin/<?php echo htmlspecialchars($job['company_logo']); ?>" 
                                                         alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                                         class="w-6 h-6 rounded mr-2 object-cover">
                                                <?php else: ?>
                                                    <div class="w-6 h-6 bg-green-100 rounded mr-2 flex items-center justify-center">
                                                        <i class="fas fa-building text-green-600 text-xs"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($job['company_name']); ?></span>
                                                <span class="text-xs text-gray-500 ml-2">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php echo date('M d, Y', strtotime($job['posted_date'])); ?>
                                                </span>
                                            </div>
                                            <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                            <div class="flex items-center text-xs text-gray-500 mb-2">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <span><?php echo htmlspecialchars($job['location'] ?? 'Location not specified'); ?></span>
                                                <?php if (!empty($job['job_type'])): ?>
                                                    <span class="mx-2">•</span>
                                                    <span><?php echo htmlspecialchars($job['job_type']); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($job['salary_range'])): ?>
                                                    <span class="mx-2">•</span>
                                                    <span><?php echo htmlspecialchars($job['salary_range']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars(substr($job['job_description'], 0, 100)) . (strlen($job['job_description']) > 100 ? '...' : ''); ?>
                                            </p>
                                        </div>
                                        <div class="ml-4 flex-shrink-0">
                                            <a href="jobs.php?id=<?php echo $job['id']; ?>" 
                                               class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-200">
                                                <i class="fas fa-external-link-alt mr-1"></i>
                                                Apply
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <a href="jobs.php" class="inline-flex items-center text-green-600 hover:text-green-800 text-sm font-medium transition-colors duration-200">
                            View all jobs <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?> 