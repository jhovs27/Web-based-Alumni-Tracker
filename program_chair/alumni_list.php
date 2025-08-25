<?php
session_start();
require_once '../admin/config/database.php';
include 'includes/header.php';

// Get program chair's department/program from session or DB
$chair_program = $_SESSION['chair_program'] ?? null;
if (!$chair_program && isset($_SESSION['chair_username'])) {
    // Fallback: fetch from DB if not in session
    $stmt = $conn->prepare("SELECT program FROM program_chairs WHERE username = ? LIMIT 1");
    $stmt->execute([$_SESSION['chair_username']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $chair_program = $row['program'] ?? null;
}

// Fetch the matching course id(s) for this program string
$course_ids = [];
if ($chair_program) {
    $stmt = $conn->prepare("SELECT id FROM course WHERE CONCAT(course_title, ' (', accro, ')') = ?");
    $stmt->execute([$chair_program]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $course_ids[] = $row['id'];
    }
}

// Pagination settings for initial load
$records_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search and filter for initial load
$search = isset($_GET['search']) ? $_GET['search'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';

// Base query (no program/course restriction)
$query = "SELECT s.StudentNo, s.LastName, s.FirstName, s.MiddleName, s.Sex, s.ContactNo, m.SchoolYear, m.Semester,
    a.alumni_id              
    FROM students s               
    LEFT JOIN listgradsub l ON s.StudentNo = l.StudentNo              
    LEFT JOIN listgradmain m ON l.MainID = m.id
    LEFT JOIN alumni_ids a ON a.student_no = s.StudentNo              
    WHERE 1=1";

$params = [];

// Add search condition
if (!empty($search)) {
    $query .= " AND (s.StudentNo LIKE :search
                OR s.LastName LIKE :search
                OR s.FirstName LIKE :search
                OR s.MiddleName LIKE :search
                OR s.ContactNo LIKE :search
                OR m.SchoolYear LIKE :search
                OR m.Semester LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add course filter
if (!empty($course_filter)) {
    $query .= " AND c.id = :course";
    $params[':course'] = $course_filter;
}

// Add filtering by school year to the PHP query
$school_year_filter = isset($_GET['school_year']) ? $_GET['school_year'] : '';
if (!empty($school_year_filter)) {
    $query .= " AND m.SchoolYear = :school_year";
    $params[':school_year'] = $school_year_filter;
}

// Get total records for pagination
$total_query = "SELECT COUNT(DISTINCT s.StudentNo) as total FROM students s
                LEFT JOIN course c ON s.course = c.id
                LEFT JOIN listgradsub l ON s.StudentNo = l.StudentNo
                LEFT JOIN listgradmain m ON l.MainID = m.id
                WHERE 1=1";

if (!empty($search)) {
    $total_query .= " AND (s.StudentNo LIKE :search
                     OR s.LastName LIKE :search
                     OR s.FirstName LIKE :search
                    OR s.MiddleName LIKE :search
                    OR s.ContactNo LIKE :search
                    OR m.SchoolYear LIKE :search
                    OR m.Semester LIKE :search)";
}

if (!empty($course_filter)) {
    $total_query .= " AND c.id = :course";
}

if (!empty($school_year_filter)) {
    $total_query .= " AND m.SchoolYear = :school_year";
}

// Get course list for filter
try {
    $courses_query = "SELECT id, accro FROM course ORDER BY accro";
    $courses_stmt = $conn->query($courses_query);
    $courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error getting courses: " . $e->getMessage());
}

try {
    $stmt = $conn->prepare($total_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total_records / $records_per_page);
} catch (PDOException $e) {
    die("Error getting total records: " . $e->getMessage());
}

// Add pagination to main query
$query .= " ORDER BY s.LastName ASC LIMIT :offset, :limit";

// Execute main query
try {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error executing main query: " . $e->getMessage());
}

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'],
    ['label' => 'Alumni List', 'icon' => 'fa-users'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni List - SLSU-HC Chair Panel</title>
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
                    <?php foreach (isset($breadcrumbs) ? $breadcrumbs : [] as $index => $breadcrumb): ?>
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

            <!-- Header Section with Print Button -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Alumni List
                        </h1>
                        <p class="mt-2 text-gray-600">View and manage alumni records for your program</p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex justify-end w-full sm:w-auto space-x-2">
                        <button onclick="exportToExcel()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export
                        </button>
                        <button onclick="printTable()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2V7a2 2 0 012-2h16a2 2 0 012 2v7a2 2 0 01-2 2h-2m-6 0v2m0 0h6m-6 0H6"></path>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                            <!-- Search Input -->
                            <div class="lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Alumni</label>
                                <div class="relative">
                                    <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>"
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                           placeholder="Search by Student No, Name, Contact, or School Year...">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- School Year Filter -->
                            <div>
                                <label for="school_year" class="block text-sm font-medium text-gray-700 mb-2">School Year</label>
                                <select id="school_year" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">All Years</option>
                                    <?php
                                    $school_years = [];
                                    $stmt_sy = $conn->query("SELECT DISTINCT SchoolYear FROM listgradmain ORDER BY SchoolYear DESC");
                                    while ($row_sy = $stmt_sy->fetch(PDO::FETCH_ASSOC)) {
                                        $sy = (int)$row_sy['SchoolYear'];
                                        $selected = (isset($_GET['school_year']) && $_GET['school_year'] == $sy) ? 'selected' : '';
                                        echo "<option value=\"$sy\" $selected>" . htmlspecialchars($sy . ' - ' . ($sy + 1)) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Show Entries Dropdown -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Show Entries</label>
                                <select id="entriesPerPage" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <?php $entries_options = [5, 10, 15, 20, 25, 50, 100];
                                    foreach ($entries_options as $opt): ?>
                                        <option value="<?php echo $opt; ?>" <?php if ($records_per_page == $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="relative">
                <!-- Loading overlay -->
                <div id="loadingOverlay" class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>

                <!-- Table Container for AJAX -->
                <div id="alumni-table-container">
                    <!-- Results Summary -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> alumni records
                        </p>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50/80">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Alumni ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Full Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sex</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact No</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">School Year</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Semester</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (count($result) > 0): ?>
                                        <?php foreach ($result as $row): ?>
                                        <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php
                                                if (!empty($row['alumni_id'])) {
                                                    echo htmlspecialchars($row['alumni_id']);
                                                } else {
                                                    echo '<span class="text-yellow-600">Not Generated</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php
                                                $fullName = array_filter([
                                                    $row['LastName'],
                                                    $row['FirstName'],
                                                    $row['MiddleName']
                                                ]);
                                                echo htmlspecialchars(implode(', ', $fullName));
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $row['Sex'] === 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'; ?>">
                                                    <?php echo htmlspecialchars($row['Sex']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['ContactNo']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php
                                                if (isset($row['SchoolYear'])) {
                                                    $sy = (int)$row['SchoolYear'];
                                                    echo htmlspecialchars($sy . ' - ' . ($sy + 1));
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php
                                                if (isset($row['Semester'])) {
                                                    if ($row['Semester'] == 1) echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">1st Semester</span>';
                                                    elseif ($row['Semester'] == 2) echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">2nd Semester</span>';
                                                    else echo htmlspecialchars($row['Semester']);
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-2">
                                                    <button class="text-blue-600 hover:text-blue-900 p-1 rounded-lg hover:bg-blue-50 transition-colors duration-200" title="View Details">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </button>
                                                    <button class="text-yellow-600 hover:text-yellow-900 p-1 rounded-lg hover:bg-yellow-50 transition-colors duration-200" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                    </svg>
                                                    <p class="text-lg font-medium text-gray-900 mb-1">No alumni records found</p>
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
                                    <?php if ($page > 1): ?>
                                        <button onclick="loadPage(<?php echo $page - 1; ?>)"
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            Previous
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($page < $total_pages): ?>
                                        <button onclick="loadPage(<?php echo $page + 1; ?>)"
                                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            Next
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $records_per_page, $total_records); ?></span> of <span class="font-medium"><?php echo $total_records; ?></span> results
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <?php if ($page > 1): ?>
                                                <button onclick="loadPage(<?php echo $page - 1; ?>)"
                                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                    <span class="sr-only">Previous</span>
                                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                            <?php
                                            $start_page = max(1, $page - 2);
                                            $end_page = min($total_pages, $page + 2);
                                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <button onclick="loadPage(<?php echo $i; ?>)"
                                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $i === $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                                                    <?php echo $i; ?>
                                                </button>
                                            <?php endfor; ?>
                                            <?php if ($page < $total_pages): ?>
                                                <button onclick="loadPage(<?php echo $page + 1; ?>)"
                                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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
            </div>
        </div>
    </main>

    <script>
    // Global variables
    let currentSearch = '<?php echo htmlspecialchars($search); ?>';
    let currentSchoolYear = '<?php echo htmlspecialchars($school_year_filter); ?>';
    let currentEntries = <?php echo $records_per_page; ?>;
    let currentPage = <?php echo $page; ?>;
    let searchTimeout;

    // AJAX function to load table data
    function loadTableData(search = currentSearch, school_year = currentSchoolYear, entries = currentEntries, page = currentPage) {
        console.log('loadTableData called with:', { search, school_year, entries, page });

        // Show loading overlay
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('show');
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('search', search);
        formData.append('school_year', school_year);
        formData.append('entries', entries);
        formData.append('page', page);
        
        console.log('Sending AJAX request to chair_alumni_ajax.php');

        // Make AJAX request
        fetch('chair_alumni_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                // Update entire table container
                const tableContainer = document.getElementById('alumni-table-container');
                if (tableContainer) {
                    tableContainer.innerHTML = data.html;
                    console.log('Table updated successfully');
                } else {
                    console.error('Table container not found');
                }
                
                // Update current values
                currentSearch = search;
                currentSchoolYear = school_year;
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
            if (loadingOverlay) {
                loadingOverlay.classList.remove('show');
            }
        });
    }

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

    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Initializing event listeners');

        // Search functionality with debounce
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            console.log('Search input found, adding event listener');
            searchInput.addEventListener('input', function() {
                const searchValue = this.value;
                console.log('Search input changed:', searchValue);

                // Clear previous timeout
                clearTimeout(searchTimeout);

                // Set new timeout for debounced search
                searchTimeout = setTimeout(() => {
                    console.log('Executing search with value:', searchValue);
                    loadTableData(searchValue, currentSchoolYear, currentEntries, 1);
                }, 500); // 500ms delay
            });
        } else {
            console.error('Search input not found');
        }

        // Filter change handlers - automatic trigger
        const schoolYearSelect = document.getElementById('school_year');
        if (schoolYearSelect) {
            console.log('School year select found, adding event listener');
            schoolYearSelect.addEventListener('change', function() {
                console.log('School year changed:', this.value);
                loadTableData(currentSearch, this.value, currentEntries, 1);
            });
        } else {
            console.error('School year select not found');
        }

        // Entries per page change - automatic trigger
        const entriesSelect = document.getElementById('entriesPerPage');
        if (entriesSelect) {
            console.log('Entries select found, adding event listener');
            entriesSelect.addEventListener('change', function() {
                const entriesValue = parseInt(this.value);
                console.log('Entries per page changed:', entriesValue);
                loadTableData(currentSearch, currentSchoolYear, entriesValue, 1);
            });
        } else {
            console.error('Entries select not found');
        }
    });

    // Pagination function
    function loadPage(page) {
        console.log('loadPage called with page:', page);
        loadTableData(currentSearch, currentSchoolYear, currentEntries, page);
    }

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

    // Export function
    function exportToExcel() {
        // Get current filter values
        const searchValue = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
        const schoolYearValue = document.getElementById('school_year') ? document.getElementById('school_year').value : '';
        const entriesValue = document.getElementById('entriesPerPage') ? document.getElementById('entriesPerPage').value : '10';
        
        // Build query string for export
        const params = new URLSearchParams();
        if (searchValue) params.append('search', searchValue);
        if (schoolYearValue) params.append('school_year', schoolYearValue);
        if (entriesValue) params.append('entries', entriesValue);
        params.append('export', 'excel');
        
        // For debugging
        console.log('Export URL:', 'export_alumni_list.php?' + params.toString());
        
        // Redirect to export endpoint
        window.location.href = 'export_alumni_list.php?' + params.toString();
    }
    
    // Print function
    function printTable() {
        var tableContainer = document.getElementById('alumni-table-container');
        if (!tableContainer) return;
        var printWindow = window.open('', '', 'height=700,width=1000');
        printWindow.document.write('<html><head><title>Print Alumni List</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h2 class="text-2xl font-bold mb-4">Alumni List</h2>');
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
