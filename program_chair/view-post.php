<?php
session_start();
require_once '../admin/config/database.php';

// Set current page for sidebar highlighting
$current_page = 'manage-posts';

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'],
    ['label' => 'Manage Posts', 'url' => 'manage-posts.php', 'icon' => 'fa-newspaper'],
    ['label' => 'View Job Post', 'icon' => 'fa-eye'],
];

// Check if user is logged in as program chair
if (!isset($_SESSION['is_chair']) || !$_SESSION['is_chair']) {
    header('Location: ../login.php');
    exit();
}

// Get job post ID
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$job_id) {
    $_SESSION['error'] = "Invalid job post ID.";
    header('Location: manage-posts.php');
    exit();
}

// Fetch job post data
try {
    $stmt = $conn->prepare("SELECT * FROM job_posts WHERE id = ?");
    $stmt->execute([$job_id]);
    $job_post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job_post) {
        $_SESSION['error'] = "Job post not found.";
        header('Location: manage-posts.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching job post: " . $e->getMessage();
    header('Location: manage-posts.php');
    exit();
}

// Currency symbols
$currencySymbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'JPY' => '¥',
    'AUD' => 'A$',
    'CAD' => 'C$',
    'CHF' => 'Fr',
    'CNY' => '¥',
    'INR' => '₹',
    'PHP' => '₱',
    'SGD' => 'S$',
    'AED' => 'د.إ',
    'BRL' => 'R$',
    'MXN' => 'Mex$',
    'NZD' => 'NZ$',
    'ZAR' => 'R'
];

$currency = isset($job_post['currency']) ? $job_post['currency'] : 'USD';
$symbol = $currencySymbols[$currency] ?? '$';

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Post Details - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pc-btn-primary {
            @apply bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-2.5 rounded-lg font-medium transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 inline-flex items-center gap-2;
        }
        .pc-btn-secondary {
            @apply bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 inline-flex items-center gap-2 border border-gray-300;
        }
        .info-card {
            @apply bg-gradient-to-br from-white to-gray-50 rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300;
        }
        .status-badge {
            @apply px-3 py-1 rounded-full text-sm font-medium inline-flex items-center gap-1;
        }
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
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
            <div class="max-w-6xl mx-auto">
                <!-- Header Section -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-6 mb-8 border border-blue-100">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold gradient-text mb-2">Job Post Details</h1>
                            <p class="text-gray-600">View and manage job posting information</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="manage-posts.php" class="pc-btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Back to List
                            </a>
                            <a href="edit-post.php?id=<?php echo $job_post['id']; ?>" class="pc-btn-primary">
                                <i class="fas fa-edit"></i>
                                Edit Post
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Job Post Content -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="xl:col-span-2 space-y-8">
                        <!-- Job Header Card -->
                        <div class="info-card">
                            <div class="flex flex-col sm:flex-row items-start gap-6">
                                <div class="flex-shrink-0">
                                    <?php if ($job_post['company_logo']): ?>
                                        <img src="../admin/<?php echo htmlspecialchars($job_post['company_logo']); ?>"
                                             alt="<?php echo htmlspecialchars($job_post['company_name']); ?> Logo"
                                             class="w-20 h-20 object-contain rounded-xl border-2 border-gray-200 bg-white p-2">
                                    <?php else: ?>
                                        <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center border-2 border-blue-200">
                                            <i class="fas fa-building text-blue-600 text-2xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <h2 class="text-2xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($job_post['job_title'] ?? 'Untitled Job'); ?></h2>
                                    <p class="text-xl text-blue-600 font-semibold mb-2"><?php echo htmlspecialchars($job_post['company_name'] ?? 'Unknown Company'); ?></p>
                                    
                                    <?php if (isset($job_post['location']) && $job_post['location']): ?>
                                        <p class="text-gray-600 mb-4 flex items-center">
                                            <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>
                                            <?php echo htmlspecialchars($job_post['location']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="flex flex-wrap gap-3">
                                        <?php if (isset($job_post['job_type']) && $job_post['job_type']): ?>
                                            <span class="status-badge bg-blue-100 text-blue-800">
                                                <i class="fas fa-briefcase"></i>
                                                <?php echo htmlspecialchars($job_post['job_type']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($job_post['job_category']) && $job_post['job_category']): ?>
                                            <span class="status-badge bg-green-100 text-green-800">
                                                <i class="fas fa-tag"></i>
                                                <?php echo htmlspecialchars($job_post['job_category']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($job_post['status'])): ?>
                                            <span class="status-badge <?php echo $job_post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <i class="fas fa-circle text-xs"></i>
                                                <?php echo ucfirst($job_post['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Job Description -->
                        <div class="info-card">
                            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-file-alt text-white text-sm"></i>
                                </div>
                                Job Description
                            </h3>
                            <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl p-6 border border-purple-100">
                                <div class="prose max-w-none text-gray-700 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($job_post['job_description'])); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Qualifications -->
                        <div class="info-card">
                            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-graduation-cap text-white text-sm"></i>
                                </div>
                                Qualifications
                            </h3>
                            <div class="bg-gradient-to-br from-indigo-50 to-white rounded-xl p-6 border border-indigo-100">
                                <div class="prose max-w-none text-gray-700 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($job_post['qualifications'])); ?>
                                </div>
                            </div>
                        </div>

                        <!-- How to Apply -->
                        <div class="info-card">
                            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-paper-plane text-white text-sm"></i>
                                </div>
                                How to Apply
                            </h3>
                            <div class="bg-gradient-to-br from-green-50 to-white rounded-xl p-6 border border-green-100">
                                <div class="prose max-w-none text-gray-700 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($job_post['how_to_apply'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Information -->
                    <div class="space-y-6">
                        <!-- Post Information -->
                        <div class="info-card">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-md flex items-center justify-center mr-2">
                                    <i class="fas fa-info text-white text-xs"></i>
                                </div>
                                Post Information
                            </h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600 font-medium">Posted Date</span>
                                    <span class="text-gray-900 font-semibold"><?php echo date('M d, Y', strtotime($job_post['posted_date'])); ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600 font-medium">Job ID</span>
                                    <span class="text-gray-900 font-mono text-sm">#<?php echo str_pad($job_post['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Information -->
                        <div class="info-card">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <div class="w-6 h-6 bg-gradient-to-br from-green-500 to-green-600 rounded-md flex items-center justify-center mr-2">
                                    <i class="fas fa-money-bill-wave text-white text-xs"></i>
                                </div>
                                Salary Information
                            </h3>
                            <div class="bg-gradient-to-br from-green-50 to-white rounded-lg p-4 border border-green-100">
                                <?php if ($job_post['salary_min'] && $job_post['salary_max']): ?>
                                    <p class="text-2xl font-bold text-green-700 mb-1">
                                        <?php echo $symbol . ' ' . number_format($job_post['salary_min']) . ' - ' . $symbol . ' ' . number_format($job_post['salary_max']); ?>
                                    </p>
                                    <p class="text-sm text-green-600"><?php echo $currency; ?> currency</p>
                                <?php else: ?>
                                    <p class="text-gray-500 italic">Salary not specified</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Application Deadline -->
                        <div class="info-card">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <div class="w-6 h-6 bg-gradient-to-br from-red-500 to-red-600 rounded-md flex items-center justify-center mr-2">
                                    <i class="fas fa-calendar-alt text-white text-xs"></i>
                                </div>
                                Application Deadline
                            </h3>
                            <div class="bg-gradient-to-br from-red-50 to-white rounded-lg p-4 border border-red-100">
                                <p class="text-xl font-bold text-red-700 mb-2">
                                    <?php echo date('M d, Y', strtotime($job_post['deadline'])); ?>
                                </p>
                                <?php
                                $deadline = new DateTime($job_post['deadline']);
                                $now = new DateTime();
                                $days_left = $now->diff($deadline)->days;
                                $is_expired = $deadline < $now;
                                ?>
                                <p class="text-sm <?php echo $is_expired ? 'text-red-600' : 'text-gray-600'; ?> flex items-center">
                                    <?php if ($is_expired): ?>
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        Expired <?php echo $days_left; ?> days ago
                                    <?php else: ?>
                                        <i class="fas fa-clock mr-2"></i>
                                        <?php echo $days_left; ?> days remaining
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="info-card">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-md flex items-center justify-center mr-2">
                                    <i class="fas fa-address-book text-white text-xs"></i>
                                </div>
                                Contact Information
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-envelope text-white text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-600">Email</p>
                                        <a href="mailto:<?php echo htmlspecialchars($job_post['contact_email']); ?>" 
                                           class="text-blue-600 hover:text-blue-800 font-medium truncate block">
                                            <?php echo htmlspecialchars($job_post['contact_email']); ?>
                                        </a>
                                    </div>
                                </div>

                                <?php if ($job_post['contact_phone']): ?>
                                    <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg border border-green-100">
                                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-phone text-white text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-600">Phone</p>
                                            <a href="tel:<?php echo htmlspecialchars($job_post['contact_phone']); ?>" 
                                               class="text-green-600 hover:text-green-800 font-medium">
                                                <?php echo htmlspecialchars($job_post['contact_phone']); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($job_post['job_link']): ?>
                                    <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg border border-purple-100">
                                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-external-link-alt text-white text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-600">Application Link</p>
                                            <a href="<?php echo htmlspecialchars($job_post['job_link']); ?>" 
                                               target="_blank" 
                                               class="text-purple-600 hover:text-purple-800 font-medium">
                                                Apply Online
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
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

        // Add smooth scroll behavior for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add loading states to buttons
        document.querySelectorAll('.pc-btn-primary, .pc-btn-secondary').forEach(button => {
            button.addEventListener('click', function() {
                if (this.href && !this.href.includes('#')) {
                    const originalContent = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
                    this.style.pointerEvents = 'none';
                    
                    // Reset after 3 seconds if page doesn't change
                    setTimeout(() => {
                        this.innerHTML = originalContent;
                        this.style.pointerEvents = 'auto';
                    }, 3000);
                }
            });
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
