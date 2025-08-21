<?php
session_start();
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
include 'includes/breadcrumb.php';

// Set current page for sidebar highlighting
$current_page = 'manage-posts';

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$id_sort = isset($_GET['id_sort']) ? $_GET['id_sort'] : '';

// Pagination settings
$entries_options = [5, 10, 15, 20, 25, 50, 100];
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page_num - 1) * $entries_per_page;

// Build query with filters
$query = "SELECT * FROM job_posts WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM job_posts WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND status = :status";
    $count_query .= " AND status = :status";
    $params[':status'] = $status;
}

if ($category) {
    $query .= " AND category = :category";
    $count_query .= " AND category = :category";
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
if ($id_sort === 'asc') {
    $query .= " ORDER BY id ASC";
} elseif ($id_sort === 'desc') {
    $query .= " ORDER BY id DESC";
} else {
    $query .= " ORDER BY posted_date DESC"; // Default sorting
}

// Add pagination
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
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Job Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Styles */
        body {
            font-family: sans-serif;
            background-color: #f3f4f6;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
        }

        /* Search and Filter Styles */
        .search-filter-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .search-container {
            flex: 1;
            margin-right: 20px;
        }

        .search-container input[type="search"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .filter-container {
            display: flex;
            gap: 10px;
        }

        .filter-container select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #f9f9f9;
            font-weight: 600;
        }

        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pagination-buttons {
            display: flex;
            gap: 5px;
        }

        .pagination-buttons button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            cursor: pointer;
        }

        .pagination-buttons button:hover {
            background-color: #f0f0f0;
        }

        .pagination-buttons button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Action Buttons Styles */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .action-buttons button, .action-buttons a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }

        .action-buttons button:hover, .action-buttons a:hover {
            background-color: #f0f0f0;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .modal-header h2 {
            font-size: 20px;
            color: #333;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-footer {
            text-align: right;
        }

        .modal-footer button {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            cursor: pointer;
        }

        .modal-footer button:hover {
            background-color: #f0f0f0;
        }

        .hidden {
            display: none;
        }

        .flex {
            display: flex;
        }

        /* Print Button Styles */
        .print-button {
            margin-bottom: 20px;
            text-align: right;
        }

        .print-button button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .print-button button:hover {
            background-color: #367c39;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-filter-container {
                flex-direction: column;
            }

            .search-container {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .filter-container {
                flex-direction: column;
                gap: 5px;
            }

            .filter-container select {
                width: 100%;
            }

            .pagination {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .pagination-buttons {
                order: -1;
            }
        }

        /* Custom scrollbar for table */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Loading overlay styles */
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

        /* Filter section styling */
        .filter-section {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        .search-input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.875rem;
        }

        /* Button styling */
        .pc-btn-primary {
            background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
        }

        .pc-btn-primary:hover {
            background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .pc-btn-secondary {
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(107, 114, 128, 0.3);
        }

        .pc-btn-secondary:hover {
            background: linear-gradient(135deg, #4B5563 0%, #374151 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
        }

        .pc-btn-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
        }

        .pc-btn-danger:hover {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Modal styling */
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem 1.5rem 0 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 0 1.5rem 1.5rem 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        /* Table container */
        .table-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            position: relative;
        }

        /* Main content spacing for fixed navbar and sidebar */
        .main-content {
            margin-left: 0;
            width: 100%;
            padding-top: 10rem;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        @media (min-width: 1024px) {
            .main-content {
                margin-left: 16rem;
                width: calc(100% - 16rem);
                padding-top: 8rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Main Content -->
<div class="main-content min-h-screen flex flex-col bg-white">
    <div class="flex-1 p-6">
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Manage Posts', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
                
        <div class="max-w-7xl mx-auto">
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

            <div class="table-container">
                <!-- Header -->
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-briefcase text-blue-600"></i>
                        Manage Job Posts
                    </h1>
                    <div class="flex space-x-2">
                        <a href="create-posts.php" class="pc-btn-primary">
                            <i class="fas fa-plus"></i>
                            Create New Post
                        </a>
                        <button type="button" onclick="printTable()" class="pc-btn-secondary">
                            <i class="fas fa-print"></i>
                            Print
                        </button>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="filter-section">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <!-- Search Bar and Entries Dropdown -->
                        <div class="flex items-center gap-4 flex-1">
                            <div class="relative flex-1 max-w-md">
                                <input type="text" id="searchInput" placeholder="Search jobs..." class="search-input w-full" value="<?php echo htmlspecialchars($search); ?>">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                                                        
                            <!-- Show Entries Dropdown -->
                            <div class="flex items-center space-x-2">
                                <label for="entriesPerPage" class="text-sm text-gray-700 whitespace-nowrap">Show</label>
                                <select id="entriesPerPage" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <?php foreach ($entries_options as $opt): ?>
                                        <option value="<?php echo $opt; ?>" <?php if ($entries_per_page == $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="text-sm text-gray-700 whitespace-nowrap">entries</span>
                            </div>
                        </div>
                                                
                        <!-- Filter Dropdowns -->
                        <div class="flex items-center gap-4">
                            <!-- ID Sort -->
                            <select id="idSortSelect" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm">
                                <option value="">Sort by ID</option>
                                <option value="asc" <?php echo $id_sort === 'asc' ? 'selected' : ''; ?>>Lowest to Highest</option>
                                <option value="desc" <?php echo $id_sort === 'desc' ? 'selected' : ''; ?>>Highest to Lowest</option>
                            </select>
                            
                            <!-- Status Filter -->
                            <select id="statusSelect" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm">
                                <option value="">All Status</option>
                                <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                                                        
                            <!-- Category Filter -->
                            <select id="categorySelect" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm">
                                <option value="">All Categories</option>
                                <?php
                                $categories = ['IT', 'Education', 'Healthcare', 'Engineering', 'Business', 'Finance', 'Arts', 'Science', 'Other'];
                                foreach ($categories as $cat):
                                ?>
                                    <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                                                        
                            <!-- Type Filter -->
                            <select id="typeSelect" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm">
                                <option value="">All Types</option>
                                <option value="Full-time" <?php echo $type === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                <option value="Part-time" <?php echo $type === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                <option value="Internship" <?php echo $type === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                                <option value="Freelance" <?php echo $type === 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                                <option value="Remote" <?php echo $type === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                                <option value="On-site" <?php echo $type === 'On-site' ? 'selected' : ''; ?>>On-site</option>
                            </select>

                            <!-- Reset Button -->
                            <button type="button" id="resetFilterBtn" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 transition-colors duration-200 text-sm">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="relative">
                    <!-- Loading overlay -->
                    <div id="loadingOverlay" class="loading-overlay">
                        <div class="loading-spinner"></div>
                    </div>
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <div class="max-h-[600px] overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Job Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Company</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Job Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Posted Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Deadline</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Salary</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                                    <?php if (count($result) > 0): ?>
                                        <?php foreach ($result as $row): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['id']; ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['job_title']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['job_type']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($row['posted_date'])); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($row['deadline'])); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                         <?php echo $row['status'] === 'published' ? 'bg-green-100 text-green-800' :
                                                                 ($row['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                                                 'bg-gray-100 text-gray-800'); ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php
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
                                                    <div class="flex items-center space-x-3">
                                                        <a href="view-post.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-post.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="text-red-600 hover:text-red-900">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No job posts found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                        <div id="paginationInfo" class="flex items-center space-x-4">
                            <span class="text-sm text-gray-700">
                                Showing 
                                <span class="font-medium"><?php echo $offset + 1; ?></span>
                                to 
                                <span class="font-medium"><?php echo min($offset + $entries_per_page, $total_records); ?></span>
                                of 
                                <span class="font-medium"><?php echo $total_records; ?></span>
                                results
                            </span>
                        </div>
                        <div id="paginationControls" class="flex items-center space-x-2">
                            <!-- Previous Button -->
                            <?php if ($current_page_num > 1): ?>
                                <button onclick="loadPage(<?php echo $current_page_num - 1; ?>)" class="px-3 py-1 border rounded-md hover:bg-gray-50 transition-colors duration-200">
                                    Previous
                                </button>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php
                            $start_page = max(1, $current_page_num - 2);
                            $end_page = min($total_pages, $current_page_num + 2);
                            if ($start_page > 1) {
                                echo '<button onclick="loadPage(1)" class="px-3 py-1 border rounded-md hover:bg-gray-50 transition-colors duration-200">1</button>';
                                if ($start_page > 2) {
                                    echo '<span class="px-2">...</span>';
                                }
                            }
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $active = $i === $current_page_num ? 'bg-blue-500 text-white' : 'hover:bg-gray-50';
                                echo "<button onclick=\"loadPage($i)\" class=\"px-3 py-1 border rounded-md $active transition-colors duration-200\">$i</button>";
                            }
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<span class="px-2">...</span>';
                                }
                                echo "<button onclick=\"loadPage($total_pages)\" class=\"px-3 py-1 border rounded-md hover:bg-gray-50 transition-colors duration-200\">$total_pages</button>";
                            }
                            ?>

                            <!-- Next Button -->
                            <?php if ($current_page_num < $total_pages): ?>
                                <button onclick="loadPage(<?php echo $current_page_num + 1; ?>)" class="px-3 py-1 border rounded-md hover:bg-gray-50 transition-colors duration-200">
                                    Next
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modals -->
<div id="deleteModal" class="fixed inset-0 modal-overlay hidden z-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="modal-content max-w-sm mx-auto">
            <div class="modal-header">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                    Confirm Delete
                </h3>
            </div>
            <div class="modal-body">
                <p class="text-gray-600">Are you sure you want to delete this job post? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <div class="flex justify-end space-x-3">
                    <button onclick="closeModal('deleteModal')" class="pc-btn-secondary">Cancel</button>
                    <button onclick="confirmDelete()" class="pc-btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentSearch = '<?php echo htmlspecialchars($search); ?>';
let currentStatus = '<?php echo htmlspecialchars($status); ?>';
let currentCategory = '<?php echo htmlspecialchars($category); ?>';
let currentType = '<?php echo htmlspecialchars($type); ?>';
let currentIdSort = '<?php echo htmlspecialchars($id_sort); ?>';
let currentEntries = <?php echo $entries_per_page; ?>;
let currentPage = <?php echo $current_page_num; ?>;
let searchTimeout;

// AJAX function to load table data
function loadTableData(search = currentSearch, status = currentStatus, category = currentCategory, type = currentType, id_sort = currentIdSort, entries = currentEntries, page = currentPage) {
    // Show loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.add('show');
    
    // Create form data
    const formData = new FormData();
    formData.append('search', search);
    formData.append('status', status);
    formData.append('category', category);
    formData.append('type', type);
    formData.append('id_sort', id_sort);
    formData.append('entries', entries);
    formData.append('page', page);
    
    // Make AJAX request
    fetch('posts_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update table body
            document.getElementById('tableBody').innerHTML = data.table_html;
            
            // Update pagination
            document.getElementById('paginationControls').innerHTML = data.pagination_html;
            document.getElementById('paginationInfo').innerHTML = data.pagination_info;
            
            // Update current values
            currentSearch = search;
            currentStatus = status;
            currentCategory = category;
            currentType = type;
            currentIdSort = id_sort;
            currentEntries = entries;
            currentPage = page;
        } else {
            console.error('Error loading data:', data.error);
            showNotification('Error loading data. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        showNotification('Network error. Please check your connection.', 'error');
    })
    .finally(() => {
        // Hide loading overlay
        const loadingOverlay = document.getElementById('loadingOverlay');
        loadingOverlay.classList.remove('show');
    });
}

// Search functionality with debounce
document.getElementById('searchInput').addEventListener('input', function() {
    const searchValue = this.value;
    
    // Clear previous timeout
    clearTimeout(searchTimeout);
    
    // Set new timeout for debounced search
    searchTimeout = setTimeout(() => {
        loadTableData(searchValue, currentStatus, currentCategory, currentType, currentIdSort, currentEntries, 1);
    }, 500); // 500ms delay
});

// Filter change handlers
document.getElementById('statusSelect').addEventListener('change', function() {
    loadTableData(currentSearch, this.value, currentCategory, currentType, currentIdSort, currentEntries, 1);
});

document.getElementById('categorySelect').addEventListener('change', function() {
    loadTableData(currentSearch, currentStatus, this.value, currentType, currentIdSort, currentEntries, 1);
});

document.getElementById('typeSelect').addEventListener('change', function() {
    loadTableData(currentSearch, currentStatus, currentCategory, this.value, currentIdSort, currentEntries, 1);
});

document.getElementById('idSortSelect').addEventListener('change', function() {
    loadTableData(currentSearch, currentStatus, currentCategory, currentType, this.value, currentEntries, 1);
});

// Entries per page change
document.getElementById('entriesPerPage').addEventListener('change', function() {
    const entriesValue = parseInt(this.value);
    loadTableData(currentSearch, currentStatus, currentCategory, currentType, currentIdSort, entriesValue, 1);
});

// Pagination
function loadPage(page) {
    loadTableData(currentSearch, currentStatus, currentCategory, currentType, currentIdSort, currentEntries, page);
}

// Reset filters
document.getElementById('resetFilterBtn').addEventListener('click', function() {
    // Reset all form elements
    document.getElementById('searchInput').value = '';
    document.getElementById('statusSelect').value = '';
    document.getElementById('categorySelect').value = '';
    document.getElementById('typeSelect').value = '';
    document.getElementById('idSortSelect').value = '';
    document.getElementById('entriesPerPage').value = '10';
    
    // Load data with reset values
    loadTableData('', '', '', '', '', 10, 1);
});

// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'error' ? 'bg-red-100 border-l-4 border-red-500 text-red-700' : 
        type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' :
        'bg-blue-100 border-l-4 border-blue-500 text-blue-700'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <div class="py-1">
                <svg class="h-6 w-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    ${type === 'error' ? 
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' :
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    }
                </svg>
            </div>
            <div>
                <p>${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="ml-auto">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

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
    closeModal('deleteModal');
    if (currentJobId) {
        window.location.href = `job_post_actions.php?delete=${currentJobId}`;
    }
}

function setJobIdAndDelete(id) {
    currentJobId = id;
    showModal('deleteModal');
}

function printTable() {
    // Find the table section
    var tableSection = document.querySelector('.table-container .relative');
    if (!tableSection) return;
    var printWindow = window.open('', '', 'height=700,width=1000');
    printWindow.document.write('<html><head><title>Print Job Posts</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">');
    printWindow.document.write('<style>body{font-family:sans-serif;padding:2rem;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #e5e7eb;padding:8px;}th{background:#f3f4f6;}tr:nth-child(even){background:#f9fafb;}h2{margin-bottom:1rem;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2>Job Posts</h2>');
    printWindow.document.write(tableSection.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() { printWindow.print(); printWindow.close(); }, 500);
}

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
