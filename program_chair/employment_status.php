<?php
include 'includes/header.php';

// Pagination settings
$entries_options = [5, 10, 15, 20, 25, 50, 100];
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page_num - 1) * $entries_per_page;

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Sample data (in real implementation, this would come from database)
$alumniData = [
    ['id' => 1, 'last' => 'ABING', 'first' => 'JHOANNA', 'mi' => 'M', 'sex' => 'F', 'status' => 'Employed', 'company' => 'LGU - Hinunangan - Admin Aide I', 'doc' => 'Company ID'],
    ['id' => 2, 'last' => 'BAGOOD', 'first' => 'QUENNIE JANE', 'mi' => 'M', 'sex' => 'F', 'status' => 'Employed', 'company' => "Excellent People's Multi Purpose Cooperative", 'doc' => 'Company ID'],
    ['id' => 3, 'last' => 'BARRAMEDA', 'first' => 'MHAILANE', 'mi' => 'H', 'sex' => 'F', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 4, 'last' => 'BASANEZ', 'first' => 'JOHN CARLOS', 'mi' => 'N', 'sex' => 'M', 'status' => 'Employed', 'company' => 'Alorica Cebu', 'doc' => 'Company ID'],
    ['id' => 5, 'last' => 'BAYANO', 'first' => 'AYLWIN', 'mi' => 'M', 'sex' => 'M', 'status' => 'Employed', 'company' => 'Self-employed - Businessman', 'doc' => ''],
    ['id' => 6, 'last' => 'BONADOR', 'first' => 'MIRABELLE', 'mi' => 'P', 'sex' => 'F', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 7, 'last' => 'CAMANTIGUE', 'first' => 'JULIUS', 'mi' => 'P', 'sex' => 'M', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 8, 'last' => 'CATABAS', 'first' => 'JUSTINE NYLE', 'mi' => 'O', 'sex' => 'F', 'status' => 'Employed', 'company' => 'Catabas Printing Services', 'doc' => 'No ID Issued'],
    ['id' => 9, 'last' => 'COQUILLA', 'first' => 'HANNAH', 'mi' => 'R', 'sex' => 'F', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 10, 'last' => 'DAVID', 'first' => 'ZYRA', 'mi' => 'E', 'sex' => 'F', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 11, 'last' => 'DELA CRUZ', 'first' => 'MARIA', 'mi' => 'A', 'sex' => 'F', 'status' => 'Employed', 'company' => 'Tech Solutions Inc.', 'doc' => 'Company ID'],
    ['id' => 12, 'last' => 'ESTRADA', 'first' => 'JUAN', 'mi' => 'B', 'sex' => 'M', 'status' => 'Unemployed', 'company' => '', 'doc' => ''],
    ['id' => 13, 'last' => 'FLORES', 'first' => 'ANA', 'mi' => 'C', 'sex' => 'F', 'status' => 'Employed', 'company' => 'Global Services', 'doc' => 'Employment Certificate'],
    ['id' => 14, 'last' => 'GARCIA', 'first' => 'PEDRO', 'mi' => 'D', 'sex' => 'M', 'status' => 'Not Tracked', 'company' => '', 'doc' => ''],
    ['id' => 15, 'last' => 'HERNANDEZ', 'first' => 'LUCIA', 'mi' => 'E', 'sex' => 'F', 'status' => 'Employed', 'company' => 'Local Business', 'doc' => 'Business Permit']
];

// Filter data based on search and status
$filteredData = array_filter($alumniData, function($row) use ($search, $status) {
    $matchesSearch = empty($search) || 
        stripos($row['last'], $search) !== false ||
        stripos($row['first'], $search) !== false ||
        stripos($row['mi'], $search) !== false ||
        stripos($row['sex'], $search) !== false ||
        stripos($row['status'], $search) !== false ||
        stripos($row['company'], $search) !== false ||
        stripos($row['doc'], $search) !== false;
    
    $matchesStatus = empty($status) || $row['status'] === $status;
    
    return $matchesSearch && $matchesStatus;
});

$total_records = count($filteredData);
$total_pages = ceil($total_records / $entries_per_page);

// Paginate the data
$paginatedData = array_slice($filteredData, $offset, $entries_per_page);

// Calculate statistics
$employed_count = count(array_filter($alumniData, function($row) { return $row['status'] === 'Employed'; }));
$unemployed_count = count(array_filter($alumniData, function($row) { return $row['status'] === 'Unemployed'; }));
$not_tracked_count = count(array_filter($alumniData, function($row) { return $row['status'] === 'Not Tracked'; }));

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'],
    ['label' => 'Employment Status', 'icon' => 'fa-briefcase'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment Status - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/breadcrumb.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-72 pt-16 min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
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

            <!-- Header Section with Print Button -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                            </svg>
                            Employment Status
                        </h1>
                        <p class="mt-2 text-gray-600">Track and manage alumni employment information</p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex justify-end w-full sm:w-auto">
                        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2V7a2 2 0 012-2h16a2 2 0 012 2v9a2 2 0 01-2 2h-2m-6 0v2m0 0h6m-6 0H6"></path>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                            <!-- Search Input -->
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Alumni</label>
                                <div class="relative">
                                    <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>"
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                           placeholder="Search by name, company, or document...">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <!-- Status Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employment Status</label>
                                <select id="statusFilter" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">All Status</option>
                                    <option value="Employed" <?php echo $status === 'Employed' ? 'selected' : ''; ?>>Employed</option>
                                    <option value="Unemployed" <?php echo $status === 'Unemployed' ? 'selected' : ''; ?>>Unemployed</option>
                                    <option value="Not Tracked" <?php echo $status === 'Not Tracked' ? 'selected' : ''; ?>>Not Tracked</option>
                                </select>
                            </div>
                            <!-- Entries Per Page and School Year Filter -->
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Show Entries</label>
                                    <select id="entriesPerPage" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                        <?php foreach ($entries_options as $opt): ?>
                                            <option value="<?php echo $opt; ?>" <?php if ($entries_per_page == $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">School Year</label>
                                    <select id="schoolYearFilter" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                        <option value="">All Years</option>
                                        <?php
                                        $school_years = array_unique(array_column($alumniData, 'school_year'));
                                        rsort($school_years);
                                        foreach ($school_years as $sy) {
                                            if (!$sy) continue;
                                            $selected = (isset($_GET['school_year']) && $_GET['school_year'] == $sy) ? 'selected' : '';
                                            echo "<option value=\"$sy\" $selected>" . htmlspecialchars($sy) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Results Summary + Table Container for AJAX -->
            <div id="employment-table-container">
            <!-- Results Summary -->
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entries_per_page, $total_records); ?> of <?php echo $total_records; ?> employment records
                </p>
            </div>
            <!-- Table Card + Grand Total + Pagination (existing code) -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">First Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">M.I.</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sex</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Company/Business</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Documents</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($paginatedData) > 0): ?>
                                <?php foreach ($paginatedData as $index => $row): ?>
                                    <tr class="hover:bg-blue-50/50 transition-colors duration-150 <?php echo $row['status'] === 'Not Tracked' ? 'bg-yellow-50/30' : ''; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $offset + $index + 1; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($row['last']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['first']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['mi']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $row['sex'] === 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'; ?>">
                                                <?php echo htmlspecialchars($row['sex']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                 <?php echo $row['status'] === 'Employed' ? 'bg-green-100 text-green-800' :
                                                         ($row['status'] === 'Unemployed' ? 'bg-red-100 text-red-800' :
                                                         'bg-yellow-100 text-yellow-800'); ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                            <?php echo htmlspecialchars($row['company']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php if (!empty($row['doc'])): ?>
                                                <span class="text-blue-600 hover:text-blue-900 cursor-pointer underline">
                                                    <?php echo htmlspecialchars($row['doc']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400">No document</span>
                                            <?php endif; ?>
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
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-900 mb-1">No employment records found</p>
                                            <p class="text-sm text-gray-500">Try adjusting your search or filter criteria</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Grand Total Table: Employed, Unemployed, Not Tracked, and Employed Percentage -->
                <div class="mt-8">
                    <table class="min-w-max w-full table-auto border border-gray-200 rounded-xl overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Grand Total</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Employed</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Unemployed</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Not Tracked</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Filter for school year if set
                            $filteredForSummary = $alumniData;
                            if (!empty($_GET['school_year'])) {
                                $filteredForSummary = array_filter($alumniData, function($row) {
                                    return isset($row['school_year']) && $row['school_year'] == $_GET['school_year'];
                                });
                            }
                            $total = count($filteredForSummary);
                            $employed = count(array_filter($filteredForSummary, function($row) { return $row['status'] === 'Employed'; }));
                            $unemployed = count(array_filter($filteredForSummary, function($row) { return $row['status'] === 'Unemployed'; }));
                            $not_tracked = count(array_filter($filteredForSummary, function($row) { return $row['status'] === 'Not Tracked'; }));
                            $percentage = $total > 0 ? round(($employed / $total) * 100, 2) . '%' : '0%';
                            ?>
                            <tr class="bg-white">
                                <td class="px-6 py-4 font-bold text-gray-900">Grand Total (<?php echo $total; ?>)</td>
                                <td class="px-6 py-4 text-center text-green-700 font-semibold"><?php echo $employed; ?></td>
                                <td class="px-6 py-4 text-center text-red-700 font-semibold"><?php echo $unemployed; ?></td>
                                <td class="px-6 py-4 text-center text-yellow-700 font-semibold"><?php echo $not_tracked; ?></td>
                                <td class="px-6 py-4 text-center text-blue-700 font-semibold"><?php echo $percentage; ?></td>
                            </tr>
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
                                        <button onclick="changePage(<?php echo $current_page_num - 1; ?>)"
                                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Previous</span>
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start_page = max(1, $current_page_num - 2);
                                    $end_page = min($total_pages, $current_page_num + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <button onclick="changePage(<?php echo $i; ?>)"
                                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $i === $current_page_num ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                                            <?php echo $i; ?>
                                        </button>
                                    <?php endfor; ?>
                                    
                                    <?php if ($current_page_num < $total_pages): ?>
                                        <button onclick="changePage(<?php echo $current_page_num + 1; ?>)"
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
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const entriesPerPage = document.getElementById('entriesPerPage');
        const schoolYearFilter = document.getElementById('schoolYearFilter');
        const tableContainer = document.getElementById('employment-table-container');

        function getParams(page = null) {
            const params = new URLSearchParams();
            if (searchInput) params.append('search', searchInput.value);
            if (statusFilter) params.append('status', statusFilter.value);
            if (entriesPerPage) params.append('entries', entriesPerPage.value);
            if (schoolYearFilter) params.append('school_year', schoolYearFilter.value);
            if (page) params.append('page', page);
            return params;
        }

        function fetchTable(params) {
            fetch('employment_status.php?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    tableContainer.innerHTML = ''; // Clear first to avoid duplication
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.getElementById('employment-table-container');
                    if (newTable) {
                        tableContainer.innerHTML = newTable.innerHTML;
                    } else {
                        // fallback: if response is just the table container
                        tableContainer.innerHTML = html;
                    }
                    attachAjaxPagination();
                })
                .catch(() => {
                    tableContainer.innerHTML = '<div class="p-4 text-red-600">Failed to load data. Please try again.';
                });
        }

        function attachAjaxPagination() {
            document.querySelectorAll('#employment-table-container button[onclick^="changePage"]').forEach(btn => {
                btn.onclick = function(e) {
        e.preventDefault();
                    const match = this.getAttribute('onclick').match(/\d+/);
                    if (match) fetchTable(getParams(parseInt(match[0])));
                };
            });
        }

        // Auto-search (no debounce)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
                fetchTable(getParams());
            });
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                fetchTable(getParams());
            });
        }
        if (entriesPerPage) {
            entriesPerPage.addEventListener('change', function() {
                fetchTable(getParams());
            });
        }
        if (schoolYearFilter) {
            schoolYearFilter.addEventListener('change', function() {
                fetchTable(getParams());
            });
        }

        // Initial pagination setup
        attachAjaxPagination();
    });
    </script>

    <?php
    // PHP: If AJAX, only render the contents of #employment-table-container (not the container div itself)
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        ob_clean();
        ?>
            <!-- Results Summary -->
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entries_per_page, $total_records); ?> of <?php echo $total_records; ?> employment records
                </p>
            </div>
            <!-- Table Card + Grand Total + Pagination (existing code) -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">First Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">M.I.</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sex</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Company/Business</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Documents</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($paginatedData) > 0): ?>
                                <?php foreach ($paginatedData as $index => $row): ?>
                                    <tr class="hover:bg-blue-50/50 transition-colors duration-150 <?php echo $row['status'] === 'Not Tracked' ? 'bg-yellow-50/30' : ''; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $offset + $index + 1; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($row['last']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['first']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['mi']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $row['sex'] === 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'; ?>">
                                                <?php echo htmlspecialchars($row['sex']); ?>
                                            </span>
                                        </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                            <?php echo htmlspecialchars($row['company']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php if (!empty($row['doc'])): ?>
                                                <span class="text-blue-600 hover:text-blue-900 cursor-pointer underline">
                                                    <?php echo htmlspecialchars($row['doc']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400">No document</span>
                                            <?php endif; ?>
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
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-900 mb-1">No employment records found</p>
                                            <p class="text-sm text-gray-500">Try adjusting your search or filter criteria</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <!-- Grand Total and Pagination (existing code) -->
                <!-- Grand Total Table: Employed, Unemployed, Not Tracked, and Employed Percentage -->
                <div class="mt-8">
                    <table class="min-w-max w-full table-auto border border-gray-200 rounded-xl overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Grand Total</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Employed</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Unemployed</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Not Tracked</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Filter for school year if set
                            $filteredForSummary = $alumniData;
                            if (!empty($_GET['school_year'])) {
                                $filteredForSummary = array_filter($alumniData, function($row) {
                                    return isset($row['school_year']) && $row['school_year'] == $_GET['school_year'];
                                });
                            }
                            $total = count($filteredForSummary);
                            $employed = count(array_filter($filteredForSummary, function($row) { return $row['status'] === 'Employed'; }));
                            $unemployed = count(array_filter($filteredForSummary, function($row) { return $row['status'] === 'Unemployed'; }));
                            $not_tracked = count(array_filter($filteredForSummary, function($row) { return $row['status'] === 'Not Tracked'; }));
                            $percentage = $total > 0 ? round(($employed / $total) * 100, 2) . '%' : '0%';
                            ?>
                            <tr class="bg-white">
                                <td class="px-6 py-4 font-bold text-gray-900">Grand Total (<?php echo $total; ?>)</td>
                                <td class="px-6 py-4 text-center text-green-700 font-semibold"><?php echo $employed; ?></td>
                                <td class="px-6 py-4 text-center text-red-700 font-semibold"><?php echo $unemployed; ?></td>
                                <td class="px-6 py-4 text-center text-yellow-700 font-semibold"><?php echo $not_tracked; ?></td>
                                <td class="px-6 py-4 text-center text-blue-700 font-semibold"><?php echo $percentage; ?></td>
                            </tr>
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
                                        <button onclick="changePage(<?php echo $current_page_num - 1; ?>)"
                                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Previous</span>
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start_page = max(1, $current_page_num - 2);
                                    $end_page = min($total_pages, $current_page_num + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <button onclick="changePage(<?php echo $i; ?>)"
                                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $i === $current_page_num ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                                            <?php echo $i; ?>
                                        </button>
                                    <?php endfor; ?>
                                    
                                    <?php if ($current_page_num < $total_pages): ?>
                                        <button onclick="changePage(<?php echo $current_page_num + 1; ?>)"
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
            <?php
            exit;
        }
        ?>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
