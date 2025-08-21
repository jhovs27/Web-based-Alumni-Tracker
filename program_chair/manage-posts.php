<?php
session_start();
require_once '../admin/config/database.php';
include 'includes/header.php';

// Set current page for sidebar highlighting
$current_page = 'manage-posts';

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'],
    ['label' => 'Manage Posts', 'icon' => 'fa-newspaper'],
];

// Check if user is logged in as program chair
if (!isset($_SESSION['is_chair']) || !$_SESSION['is_chair']) {
    header('Location: ../login.php');
    exit();
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$id_sort = isset($_GET['id_sort']) ? $_GET['id_sort'] : '';

// For now, let's show all job posts since program chairs might not have created_by field
// We'll filter by program chair's department later if needed
$query = "SELECT * FROM job_posts WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM job_posts WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND status = :status";
    $count_query .= " AND status = :status";
    $params[':status'] = $status;
}

if ($category) {
    $query .= " AND job_category = :category";
    $count_query .= " AND job_category = :category";
    $params[':category'] = $category;
}

if ($type) {
    $query .= " AND job_type = :type";
    $count_query .= " AND job_type = :type";
    $params[':type'] = $type;
}

if ($search) {
    $query .= " AND (job_title LIKE :search OR company_name LIKE :search OR location LIKE :search)";
    $count_query .= " AND (job_title LIKE :search OR company_name LIKE :search OR location LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add sorting
if ($id_sort === 'asc' || $id_sort === '') {
    $query .= " ORDER BY id ASC";
} elseif ($id_sort === 'desc') {
    $query .= " ORDER BY id DESC";
} else {
    $query .= " ORDER BY posted_date DESC"; // fallback
}

// Add pagination
$entries_options = [5, 10, 15, 20, 25, 50, 100];
$entries_per_page = isset($_GET['entries']) && in_array((int)$_GET['entries'], $entries_options) ? (int)$_GET['entries'] : 10;
$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page_num - 1) * $entries_per_page;

$query .= " LIMIT :offset, :limit";

try {
    // Get total records for pagination
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total_records / $entries_per_page);

    // Execute main query
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Show error for debugging
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
    echo 'Database Error: ' . $e->getMessage();
    echo '</div>';
    $result = [];
    $total_records = 0;
    $total_pages = 0;
}

// Calculate statistics
$published_count = 0;
$draft_count = 0;
$total_count = count($result);

try {
    $stmt = $conn->query("SELECT status, COUNT(*) as count FROM job_posts GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['status'] === 'published') {
            $published_count = $row['count'];
        } elseif ($row['status'] === 'draft') {
            $draft_count = $row['count'];
        }
    }
} catch (PDOException $e) {
    // Handle error silently
}

// Get job categories from database or define statically
$categories = [
    'IT', 'Education', 'Healthcare', 'Engineering', 'Business', 'Finance', 'Arts', 'Science', 'Other'
];

// Get job types
$job_types = [
    'Full-time', 'Part-time', 'Internship', 'Freelance', 'Remote', 'On-site'
];

// Handle AJAX request for partial table rendering
if ((isset($_GET['ajax']) && $_GET['ajax'] == '1') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    ob_clean();
    ?>
    <div id="posts-table-container">
        <!-- Results Summary -->
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entries_per_page, $total_records); ?> of <?php echo $total_records; ?> job posts
            </p>
        </div>
        <!-- Table Card -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Job Title</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Company</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Posted Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Deadline</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Salary</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($result) > 0): ?>
                            <?php foreach ($result as $row): ?>
                                <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo $row['id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($row['job_title']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($row['company_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars($row['job_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M d, Y', strtotime($row['posted_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M d, Y', strtotime($row['deadline'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             <?php echo $row['status'] === 'published' ? 'bg-green-100 text-green-800' :
                                                     ($row['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                                     'bg-gray-100 text-gray-800'); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php
                                        $currencySymbols = [
                                            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
                                            'AUD' => 'A$', 'CAD' => 'C$', 'CHF' => 'Fr', 'CNY' => '¥',
                                            'INR' => '₹', 'PHP' => '₱', 'SGD' => 'S$', 'AED' => 'د.إ',
                                            'BRL' => 'R$', 'MXN' => 'Mex$', 'NZD' => 'NZ$', 'ZAR' => 'R'
                                        ];
                                        $currency = isset($row['currency']) ? $row['currency'] : 'USD';
                                        $symbol = $currencySymbols[$currency] ?? '$';
                                        if ($row['salary_min'] && $row['salary_max']) {
                                            echo $symbol . ' ' . number_format($row['salary_min']) . ' - ' . $symbol . ' ' . number_format($row['salary_max']);
                                        } else {
                                            echo 'Not specified';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="view-post.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900 p-1 rounded-lg hover:bg-blue-50 transition-colors duration-200" title="View Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="edit-post.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900 p-1 rounded-lg hover:bg-yellow-50 transition-colors duration-200" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <button onclick="setCurrentJobId(<?php echo $row['id']; ?>); showModal('deleteModal')" class="text-red-600 hover:text-red-900 p-1 rounded-lg hover:bg-red-50 transition-colors duration-200" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-900 mb-1">No job posts found</p>
                                        <p class="text-sm text-gray-500">Try adjusting your search or filter criteria</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($current_page_num > 1): ?>
                            <button onclick="changePage(<?php echo $current_page_num - 1; ?>)"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </button>
                        <?php endif; ?>
                        <?php if ($current_page_num < $total_pages): ?>
                            <button onclick="changePage(<?php echo $current_page_num + 1; ?>)"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $entries_per_page, $total_records); ?></span> of <span class="font-medium"><?php echo $total_records; ?></span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php if ($current_page_num > 1): ?>
                                    <button class="ajax-page-link relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" data-page="<?php echo $current_page_num - 1; ?>">
                                        <span class="sr-only">Previous</span>
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                <?php
                                $start_page = max(1, $current_page_num - 2);
                                $end_page = min($total_pages, $current_page_num + 2);
                                for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <button class="ajax-page-link relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $i === $current_page_num ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>" data-page="<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </button>
                                <?php endfor; ?>
                                <?php if ($current_page_num < $total_pages): ?>
                                    <button class="ajax-page-link relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" data-page="<?php echo $current_page_num + 1; ?>">
                                        <span class="sr-only">Next</span>
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Job Posts - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .loading-overlay.show {
            display: flex;
        }
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between" role="alert">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php echo $_SESSION['success']; ?>
                    </span>
                    <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between" role="alert">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php echo $_SESSION['error']; ?>
                    </span>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                            </svg>
                            Manage Job Posts
                        </h1>
                        <p class="mt-2 text-gray-600">View, edit, and manage all job postings</p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex space-x-2">
                        <a href="create-posts.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create New Post
                        </a>
                        <button type="button" onclick="printTable()" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V4a2 2 0 012-2h8a2 2 0 012 2v5"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2V9a2 2 0 012-2h16a2 2 0 012 2v7a2 2 0 01-2 2h-2"></path>
                                <rect width="8" height="4" x="8" y="14" rx="1"/>
                            </svg>
                            Print
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Card -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 mb-6">
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- Search Input -->
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Posts</label>
                                <div class="relative">
                                    <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>"
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                           placeholder="Search by title, company, or location...">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <!-- Status Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="statusDropdown" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">All Status</option>
                                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <!-- Category Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="categoryDropdown" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php if ($category == $cat) echo 'selected'; ?>><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Job Type Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Job Type</label>
                                <select id="typeDropdown" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">All Types</option>
                                    <?php foreach ($job_types as $job_type): ?>
                                        <option value="<?php echo $job_type; ?>" <?php if ($type == $job_type) echo 'selected'; ?>><?php echo $job_type; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Show Entries Dropdown -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Show Entries</label>
                                <select id="entriesPerPage" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <?php foreach ($entries_options as $opt): ?>
                                        <option value="<?php echo $opt; ?>" <?php if ($entries_per_page == $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Container for AJAX -->
            <div class="relative">
                <!-- Loading overlay -->
                <div id="loadingOverlay" class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
                
                <div id="posts-table-container">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600" id="paginationInfo">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entries_per_page, $total_records); ?> of <?php echo $total_records; ?> job posts
                        </p>
                    </div>
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50/80">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Job Title</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Company</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Posted Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Deadline</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Salary</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                                <?php if (count($result) > 0): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $row['id']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($row['job_title']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['company_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <?php echo htmlspecialchars($row['job_type']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo date('M d, Y', strtotime($row['posted_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo date('M d, Y', strtotime($row['deadline'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                     <?php echo $row['status'] === 'published' ? 'bg-green-100 text-green-800' :
                                                             ($row['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                                             'bg-gray-100 text-gray-800'); ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php
                                                $currencySymbols = [
                                                    'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
                                                    'AUD' => 'A$', 'CAD' => 'C$', 'CHF' => 'Fr', 'CNY' => '¥',
                                                    'INR' => '₹', 'PHP' => '₱', 'SGD' => 'S$', 'AED' => 'د.إ',
                                                    'BRL' => 'R$', 'MXN' => 'Mex$', 'NZD' => 'NZ$', 'ZAR' => 'R'
                                                ];
                                                $currency = isset($row['currency']) ? $row['currency'] : 'USD';
                                                $symbol = $currencySymbols[$currency] ?? '$';
                                                if ($row['salary_min'] && $row['salary_max']) {
                                                    echo $symbol . ' ' . number_format($row['salary_min']) . ' - ' . $symbol . ' ' . number_format($row['salary_max']);
                                                } else {
                                                    echo 'Not specified';
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-2">
                                                    <a href="view-post.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900 p-1 rounded-lg hover:bg-blue-50 transition-colors duration-200" title="View Details">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="edit-post.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900 p-1 rounded-lg hover:bg-yellow-50 transition-colors duration-200" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>
                                                    <button onclick="setCurrentJobId(<?php echo $row['id']; ?>); showModal('deleteModal')" class="text-red-600 hover:text-red-900 p-1 rounded-lg hover:bg-red-50 transition-colors duration-200" title="Delete">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                                </svg>
                                                <p class="text-lg font-medium text-gray-900 mb-1">No job posts found</p>
                                                <p class="text-sm text-gray-500">Try adjusting your search or filter criteria</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div id="paginationControls" class="flex items-center justify-between"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Confirm Delete</h3>
                        <p class="text-sm text-gray-600">Are you sure you want to delete this job post? This action cannot be undone.</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeModal('deleteModal')" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                        Cancel
                    </button>
                    <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    // Global variables for AJAX
    let searchTimeout = null;
    let currentSearch = '<?php echo htmlspecialchars($search); ?>';
    let currentStatus = '<?php echo htmlspecialchars($status); ?>';
    let currentCategory = '<?php echo htmlspecialchars($category); ?>';
    let currentType = '<?php echo htmlspecialchars($type); ?>';
    let currentEntries = <?php echo $entries_per_page; ?>;
    let currentPage = <?php echo $current_page_num; ?>;

    // AJAX function to load table data
    function loadTableData(search = currentSearch, status = currentStatus, category = currentCategory, type = currentType, entries = currentEntries, page = currentPage) {
        // Show loading overlay
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('show');
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('search', search);
        formData.append('status', status);
        formData.append('category', category);
        formData.append('type', type);
        formData.append('entries', entries);
        formData.append('page', page);
        
        // Make AJAX request
        fetch('chair_posts_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update table body
                const tableBody = document.getElementById('tableBody');
                if (tableBody) {
                    tableBody.innerHTML = data.table_html;
                }
                
                // Update pagination controls
                const paginationControls = document.getElementById('paginationControls');
                if (paginationControls) {
                    paginationControls.innerHTML = data.pagination_html;
                }
                
                // Update pagination info
                const paginationInfo = document.getElementById('paginationInfo');
                if (paginationInfo) {
                    paginationInfo.innerHTML = data.pagination_info;
                }
                
                // Update current values
                currentSearch = search;
                currentStatus = status;
                currentCategory = category;
                currentType = type;
                currentEntries = entries;
                currentPage = page;
            } else {
                console.error('Error loading data:', data.error);
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
        })
        .finally(() => {
            // Hide loading overlay
            if (loadingOverlay) {
                loadingOverlay.classList.remove('show');
            }
        });
    }

    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality with debounce
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchValue = this.value;
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Set new timeout for debounced search
                searchTimeout = setTimeout(() => {
                    loadTableData(searchValue, currentStatus, currentCategory, currentType, currentEntries, 1);
                }, 500); // 500ms delay
            });
        }

        // Filter change handlers - automatic trigger
        const statusDropdown = document.getElementById('statusDropdown');
        if (statusDropdown) {
            statusDropdown.addEventListener('change', function() {
                loadTableData(currentSearch, this.value, currentCategory, currentType, currentEntries, 1);
            });
        }

        const categoryDropdown = document.getElementById('categoryDropdown');
        if (categoryDropdown) {
            categoryDropdown.addEventListener('change', function() {
                loadTableData(currentSearch, currentStatus, this.value, currentType, currentEntries, 1);
            });
        }

        const typeDropdown = document.getElementById('typeDropdown');
        if (typeDropdown) {
            typeDropdown.addEventListener('change', function() {
                loadTableData(currentSearch, currentStatus, currentCategory, this.value, currentEntries, 1);
            });
        }

        // Entries per page change - automatic trigger
        const entriesSelect = document.getElementById('entriesPerPage');
        if (entriesSelect) {
            entriesSelect.addEventListener('change', function() {
                const entriesValue = parseInt(this.value);
                loadTableData(currentSearch, currentStatus, currentCategory, currentType, entriesValue, 1);
            });
        }
    });

    // Pagination function
    function loadPage(page) {
        loadTableData(currentSearch, currentStatus, currentCategory, currentType, currentEntries, page);
    }

    // Modal functionality
    let currentJobId = null;

    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.getElementById(modalId).classList.add('flex');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.getElementById(modalId).classList.remove('flex');
    }

    function setCurrentJobId(id) {
        currentJobId = id;
    }

    function confirmDelete() {
        if (currentJobId) {
            window.location.href = `job_post_actions.php?delete=${currentJobId}`;
        }
    }

    // Close mobile menu when clicking on links
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    });

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

    function printTable() {
        var tableContainer = document.querySelector('.overflow-x-auto');
        if (!tableContainer) return;
        var printWindow = window.open('', '', 'height=700,width=1000');
        printWindow.document.write('<html><head><title>Print Job Posts</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h2 class="text-2xl font-bold mb-4">Job Posts</h2>');
        printWindow.document.write(tableContainer.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() { printWindow.print(); printWindow.close(); }, 500);
    }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
 