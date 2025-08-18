<?php
session_start();
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
include 'config/database.php';

// Set current page for sidebar highlighting
$current_page = 'manage-posts';

// Get job post ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch job post details using PDO prepared statement
$stmt = $conn->prepare("SELECT * FROM job_posts WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$job_post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job_post) {
    $_SESSION['error'] = "Job post not found!";
    header("Location: manage-posts.php");
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
?>

<!-- Main Content -->
<div class="main-content min-h-screen flex flex-col bg-white pt-8">
    <div class="flex-1 p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between" role="alert">
                    <span class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $_SESSION['success']; ?>
                    </span>
                    <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between" role="alert">
                    <span class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $_SESSION['error']; ?>
                    </span>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-800">Job Post Details</h1>
                    <div class="flex space-x-3">
                        <button onclick="showModal('archiveModal')" class="btn btn-warning">
                            <i class="fas fa-archive mr-2"></i>
                            Archive
                        </button>
                        <button onclick="showModal('deleteModal')" class="btn btn-danger">
                            <i class="fas fa-trash mr-2"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Job Post Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Job Header -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <?php if (isset($job_post['company_logo']) && $job_post['company_logo']): ?>
                                <img src="<?php echo htmlspecialchars($job_post['company_logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($job_post['company_name'] ?? 'Company'); ?> Logo" 
                                     class="w-16 h-16 object-contain rounded-lg border border-gray-200">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-building text-gray-400 text-xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($job_post['job_title'] ?? 'Untitled Job'); ?></h2>
                                <p class="text-lg text-gray-600 mb-1"><?php echo htmlspecialchars($job_post['company_name'] ?? 'Unknown Company'); ?></p>
                                <?php if (isset($job_post['location']) && $job_post['location']): ?>
                                    <p class="text-gray-500 mb-2">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?php echo htmlspecialchars($job_post['location']); ?>
                                    </p>
                                <?php endif; ?>
                                <div class="flex items-center space-x-4 text-sm">
                                    <?php if (isset($job_post['job_type']) && $job_post['job_type']): ?>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full">
                                            <i class="fas fa-briefcase mr-1"></i>
                                            <?php echo htmlspecialchars($job_post['job_type']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($job_post['category']) && $job_post['category']): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full">
                                            <i class="fas fa-tag mr-1"></i>
                                            <?php echo htmlspecialchars($job_post['category']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($job_post['status'])): ?>
                                        <span class="px-3 py-1 <?php echo $job_post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> rounded-full">
                                            <i class="fas fa-circle mr-1"></i>
                                            <?php echo ucfirst($job_post['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Posted on</p>
                            <p class="font-semibold text-gray-900"><?php echo date('M d, Y', strtotime($job_post['posted_date'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Job Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Salary Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>
                            Salary Information
                        </h3>
                        <?php if (isset($job_post['salary_min']) && isset($job_post['salary_max']) && $job_post['salary_min'] && $job_post['salary_max']): ?>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php echo $symbol . ' ' . number_format($job_post['salary_min']) . ' - ' . $symbol . ' ' . number_format($job_post['salary_max']); ?>
                            </p>
                            <p class="text-sm text-gray-500"><?php echo $currency; ?> currency</p>
                        <?php else: ?>
                            <p class="text-gray-500">Salary not specified</p>
                        <?php endif; ?>
                    </div>

                    <!-- Application Deadline -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-red-600"></i>
                            Application Deadline
                        </h3>
                        <p class="text-lg font-semibold text-gray-900">
                            <?php echo date('M d, Y', strtotime($job_post['deadline'])); ?>
                        </p>
                        <?php
                        $deadline = new DateTime($job_post['deadline']);
                        $now = new DateTime();
                        $days_left = $now->diff($deadline)->days;
                        $is_expired = $deadline < $now;
                        ?>
                        <p class="text-sm <?php echo $is_expired ? 'text-red-600' : 'text-gray-500'; ?>">
                            <?php if ($is_expired): ?>
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Expired <?php echo $days_left; ?> days ago
                            <?php else: ?>
                                <i class="fas fa-clock mr-1"></i>
                                <?php echo $days_left; ?> days remaining
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-address-book mr-2 text-blue-600"></i>
                        Contact Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-semibold text-gray-900">
                                <a href="mailto:<?php echo htmlspecialchars($job_post['contact_email']); ?>" class="text-blue-600 hover:text-blue-800">
                                    <?php echo htmlspecialchars($job_post['contact_email']); ?>
                                </a>
                            </p>
                        </div>
                        <?php if (isset($job_post['contact_phone']) && $job_post['contact_phone']): ?>
                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <p class="font-semibold text-gray-900">
                                    <a href="tel:<?php echo htmlspecialchars($job_post['contact_phone']); ?>" class="text-blue-600 hover:text-blue-800">
                                        <?php echo htmlspecialchars($job_post['contact_phone']); ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($job_post['job_link']) && $job_post['job_link']): ?>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Application Link</p>
                            <a href="<?php echo htmlspecialchars($job_post['job_link']); ?>" 
                               target="_blank" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                Apply Online
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Job Description -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-purple-600"></i>
                        Job Description
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($job_post['job_description'] ?? '')); ?>
                        </div>
                    </div>
                </div>

                <!-- Qualifications -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-graduation-cap mr-2 text-indigo-600"></i>
                        Qualifications
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($job_post['qualifications'] ?? '')); ?>
                        </div>
                    </div>
                </div>

                <!-- How to Apply -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-paper-plane mr-2 text-green-600"></i>
                        How to Apply
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($job_post['how_to_apply'] ?? '')); ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="manage-posts.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to List
                    </a>
                    <a href="edit-post.php?id=<?php echo $job_post['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Post
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modals -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
        <h3 class="text-lg font-semibold mb-4">Confirm Delete</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this job post? This action cannot be undone.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="hideModal('deleteModal')" class="btn btn-secondary">Cancel</button>
            <a href="job_post_actions.php?delete=<?php echo $job_post['id']; ?>" class="btn btn-danger">Delete</a>
        </div>
    </div>
</div>

<div id="archiveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
        <h3 class="text-lg font-semibold mb-4">Confirm Archive</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to archive this job post?</p>
        <div class="flex justify-end space-x-3">
            <button onclick="hideModal('archiveModal')" class="btn btn-secondary">Cancel</button>
            <a href="job_post_actions.php?archive=<?php echo $job_post['id']; ?>" class="btn btn-warning">Archive</a>
        </div>
    </div>
</div>

<script>
function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.getElementById(modalId).classList.add('flex');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.getElementById(modalId).classList.remove('flex');
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('fixed')) {
        event.target.classList.add('hidden');
        event.target.classList.remove('flex');
    }
});

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

<?php include 'includes/footer.php'; ?>
</body>
</html> 