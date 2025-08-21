<?php
// Header
include 'includes/header.php';
// Database connection
require_once '../admin/config/database.php';

// Use the existing $conn variable from database.php
$pdo = $conn;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, let's check what statuses exist in the job_posts table
try {
    $statusCheck = $pdo->query("SELECT DISTINCT status FROM job_posts LIMIT 10");
    $statuses = $statusCheck->fetchAll(PDO::FETCH_COLUMN);
    error_log("Available job statuses: " . implode(', ', $statuses));
    
    // Use 'Published' status if it exists, otherwise try 'Active' or 'published'
    $statusFilter = 'Active'; // Default
    if (in_array('Published', $statuses)) {
        $statusFilter = 'Published';
    } elseif (in_array('published', $statuses)) {
        $statusFilter = 'published';
    } elseif (in_array('Active', $statuses)) {
        $statusFilter = 'Active';
    }
    
    error_log("Using status filter: " . $statusFilter);
} catch (PDOException $e) {
    error_log("Error checking job statuses: " . $e->getMessage());
    $statusFilter = 'Active'; // Fallback
}

// Fetch job posts with search and filter functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

$query = "SELECT * FROM job_posts WHERE status = ?";
$params = [$statusFilter];

if (!empty($search)) {
    $query .= " AND (job_title LIKE ? OR company_name LIKE ? OR job_description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($category)) {
    $query .= " AND job_category = ?";
    $params[] = $category;
}

if (!empty($location)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($jobs) . " jobs with status: " . $statusFilter);
    foreach ($jobs as $job) {
        error_log("Job: " . $job['job_title'] . " - " . $job['company_name'] . " - Status: " . $job['status']);
    }
} catch (PDOException $e) {
    error_log("Error fetching jobs: " . $e->getMessage());
    $jobs = [];
}

// Fetch unique categories and locations for filters
try {
    $catStmt = $pdo->prepare("SELECT DISTINCT job_category FROM job_posts WHERE status = ? AND job_category IS NOT NULL ORDER BY job_category");
    $catStmt->execute([$statusFilter]);
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

    $locStmt = $pdo->prepare("SELECT DISTINCT location FROM job_posts WHERE status = ? AND location IS NOT NULL ORDER BY location");
    $locStmt->execute([$statusFilter]);
    $locations = $locStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching filters: " . $e->getMessage());
    $categories = [];
    $locations = [];
}

// Get total count for stats
$total_jobs = count($jobs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Board - SLSU-HC Alumni Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#3b82f6'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content lg:ml-72 pt-16 min-h-screen">
        <div class="p-6">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Job Board</h1>
                <p class="text-gray-600">Find your next career opportunity from our curated job listings.</p>
                
                <!-- Debug Information (remove in production) -->
                <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                    <div class="mt-4 p-4 bg-yellow-100 border border-yellow-400 rounded-lg">
                        <h3 class="font-semibold text-yellow-800 mb-2">Debug Information:</h3>
                        <p class="text-sm text-yellow-700 mb-2">Status Filter: <?php echo htmlspecialchars($statusFilter); ?></p>
                        <p class="text-sm text-yellow-700 mb-2">Total Jobs Found: <?php echo count($jobs); ?></p>
                        <p class="text-sm text-yellow-700 mb-2">Categories: <?php echo implode(', ', $categories); ?></p>
                        <p class="text-sm text-yellow-700 mb-2">Locations: <?php echo implode(', ', $locations); ?></p>
                        <?php if (!empty($jobs)): ?>
                            <div class="mt-2">
                                <p class="text-sm font-semibold text-yellow-800">Sample Jobs:</p>
                                <?php foreach (array_slice($jobs, 0, 3) as $job): ?>
                                    <p class="text-xs text-yellow-700">- <?php echo htmlspecialchars($job['job_title']); ?> (<?php echo htmlspecialchars($job['company_name']); ?>) - Status: <?php echo htmlspecialchars($job['status']); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-briefcase text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Available Jobs</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo count($jobs); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-building text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Companies</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo count(array_unique(array_column($jobs, 'company_name'))); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-map-marker-alt text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Locations</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo count(array_unique(array_column($jobs, 'location'))); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                            <i class="fas fa-tag text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Categories</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo count($categories); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow-md mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-search text-blue-500 mr-2"></i>
                        Search & Filter Jobs
                    </h2>
                </div>
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Jobs</label>
                            <input type="text" name="search" placeholder="Job title, company, or keywords..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                            <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <select name="location" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Locations</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc); ?>" 
                                            <?php echo $location === $loc ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($loc); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    </form>
                    
                    <!-- Clear Filters -->
                    <?php if (!empty($search) || !empty($category) || !empty($location)): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="jobs.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i>Clear all filters
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Job Listings -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-briefcase text-green-500 mr-2"></i>
                        Job Opportunities
                        <?php if (!empty($search) || !empty($category) || !empty($location)): ?>
                            <span class="ml-2 text-sm font-normal text-gray-500">
                                (<?php echo count($jobs); ?> results found)
                            </span>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (empty($jobs)): ?>
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-briefcase text-gray-400 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No jobs found</h3>
                            <p class="text-gray-600 mb-4">
                                <?php if (!empty($search) || !empty($category) || !empty($location)): ?>
                                    Try adjusting your search criteria or 
                                    <a href="jobs.php" class="text-blue-600 hover:text-blue-800">clear all filters</a>.
                                <?php else: ?>
                                    No job opportunities available at the moment. Check back later for new positions!
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($jobs as $job): ?>
                                <div class="border border-gray-100 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <!-- Company Info -->
                                            <div class="flex items-center mb-3">
                                                <?php if (!empty($job['company_logo'])): ?>
                                                    <img src="../admin/<?php echo htmlspecialchars($job['company_logo']); ?>" 
                                                         alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                                         class="w-10 h-10 rounded-lg object-cover mr-3">
                                                <?php else: ?>
                                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                        <i class="fas fa-building text-blue-600"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($job['company_name']); ?></h3>
                                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($job['location']); ?></p>
                                                </div>
                                            </div>

                                            <!-- Job Title -->
                                            <h2 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($job['job_title']); ?></h2>

                                            <!-- Job Details -->
                                            <div class="flex flex-wrap gap-4 mb-3">
                                                <div class="flex items-center text-sm text-gray-600">
                                                    <i class="fas fa-tag mr-2 text-blue-500"></i>
                                                    <span><?php echo htmlspecialchars($job['job_category']); ?></span>
                                                </div>
                                                <div class="flex items-center text-sm text-gray-600">
                                                    <i class="fas fa-money-bill-wave mr-2 text-green-500"></i>
                                                    <span>
                                                        <?php 
                                                        if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                                                            echo htmlspecialchars($job['currency'] . ' ' . number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']));
                                                        } elseif (!empty($job['salary_min'])) {
                                                            echo htmlspecialchars($job['currency'] . ' ' . number_format($job['salary_min']) . '+');
                                                        } else {
                                                            echo 'Salary not specified';
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="flex items-center text-sm text-gray-600">
                                                    <i class="fas fa-clock mr-2 text-orange-500"></i>
                                                    <span><?php echo htmlspecialchars($job['job_type']); ?></span>
                                                </div>
                                            </div>

                                            <!-- Job Description Preview -->
                                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                                <?php echo htmlspecialchars(substr($job['job_description'], 0, 200)) . (strlen($job['job_description']) > 200 ? '...' : ''); ?>
                                            </p>

                                            <!-- Posted Date -->
                                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                                <span>Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?></span>
                                                <?php if (!empty($job['deadline'])): ?>
                                                    <span class="text-orange-600">
                                                        <i class="fas fa-calendar-times mr-1"></i>
                                                        Deadline: <?php echo date('M j, Y', strtotime($job['deadline'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex space-x-3">
                                                <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                                    <i class="fas fa-paper-plane mr-2"></i>Apply Now
                                                </button>
                                                <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                                    <i class="fas fa-bookmark"></i>
                                                </button>
                                                <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                                    <i class="fas fa-share"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</body>
</html> 