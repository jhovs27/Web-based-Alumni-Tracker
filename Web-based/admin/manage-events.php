<?php
session_start();
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';

// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Manage Events', 'url' => 'manage-events.php', 'active' => true]
];

// Set current page for sidebar highlighting
$current_page = 'manage-events';

// Handle delete event
if (isset($_POST['delete_event'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM alumni_events WHERE id = ?");
        $stmt->execute([$_POST['event_id']]);
        $_SESSION['success'] = "Event deleted successfully!";
    } catch(PDOException $e) {
        error_log("Database error deleting event: " . $e->getMessage());
        $_SESSION['error'] = "Error deleting event: " . $e->getMessage();
    } catch(Exception $e) {
        error_log("General error deleting event: " . $e->getMessage());
        $_SESSION['error'] = "Error deleting event: " . $e->getMessage();
    }
    
    error_log("Redirecting back to manage-events.php");
    header("Location: manage-events.php");
    exit();
}

// Handle status toggle - MUST be before any output
if (isset($_POST['toggle_status'])) {
    try {
        $stmt = $conn->prepare("UPDATE alumni_events SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['new_status'], $_POST['event_id']]);
        $_SESSION['success'] = "Event status updated successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating event status: " . $e->getMessage();
    }
    header("Location: manage-events.php");
    exit();
}

// Get filter parameters for initial load
$status = isset($_GET['status']) ? $_GET['status'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$visibility = isset($_GET['visibility']) ? $_GET['visibility'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$id_sort = isset($_GET['id_sort']) ? $_GET['id_sort'] : '';

// Pagination settings
$entries_options = [5, 10, 15, 20, 25, 50, 100];
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page_num - 1) * $entries_per_page;

// Build query with filters for initial load
$query = "SELECT * FROM alumni_events WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM alumni_events WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND status = :status";
    $count_query .= " AND status = :status";
    $params[':status'] = $status;
}

if ($type) {
    $query .= " AND event_type = :type";
    $count_query .= " AND event_type = :type";
    $params[':type'] = $type;
}

if ($visibility) {
    $query .= " AND visibility = :visibility";
    $count_query .= " AND visibility = :visibility";
    $params[':visibility'] = $visibility;
}

if ($search) {
    $query .= " AND (event_title LIKE :search OR event_description LIKE :search OR contact_person LIKE :search)";
    $count_query .= " AND (event_title LIKE :search OR event_description LIKE :search OR contact_person LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add sorting
if ($id_sort === 'asc') {
    $query .= " ORDER BY id ASC";
} elseif ($id_sort === 'desc') {
    $query .= " ORDER BY id DESC";
} else {
    $query .= " ORDER BY start_datetime DESC"; // Default sorting
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

// Get success/error messages from session
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<style>
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
</style>

<!-- Add FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<!-- Add FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<!-- Add Tippy.js for tooltips -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<!-- Filter Controls -->
<div id="calendarFilters" class="flex flex-wrap gap-4 mb-4 items-center" style="display:none;">
    <select id="filterType" class="border rounded px-3 py-2 text-sm">
        <option value="">All Types</option>
        <option value="Reunion">Reunion</option>
        <option value="Seminar">Seminar</option>
        <option value="Webinar">Webinar</option>
        <option value="Career Fair">Career Fair</option>
        <option value="Outreach">Outreach</option>
        <option value="Other">Other</option>
    </select>
    <select id="filterStatus" class="border rounded px-3 py-2 text-sm">
        <option value="">All Statuses</option>
        <option value="Active">Active</option>
        <option value="Completed">Completed</option>
        <option value="Published">Published</option>
    </select>
</div>

<!-- Calendar Container (hidden by default) -->
<div id="calendarView" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white bg-opacity-95 backdrop-blur-lg transition-all duration-500 ease-in-out opacity-0 scale-95 pointer-events-none" style="display:none;">
    <div class="w-full max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-4 md:p-8 overflow-x-auto min-h-[600px] flex flex-col relative transition-all duration-500 ease-in-out">
        <button id="viewListBtn" class="absolute top-4 right-4 pc-btn-secondary z-50"><i class="fas fa-list"></i> View List</button>
        <div id="calendarFilters" class="flex flex-wrap gap-4 mb-4 items-center w-full justify-center">
            <select id="filterType" class="border rounded px-3 py-2 text-sm">
                <option value="">All Types</option>
                <option value="Reunion">Reunion</option>
                <option value="Seminar">Seminar</option>
                <option value="Webinar">Webinar</option>
                <option value="Career Fair">Career Fair</option>
                <option value="Outreach">Outreach</option>
                <option value="Other">Other</option>
            </select>
            <select id="filterStatus" class="border rounded px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Completed">Completed</option>
                <option value="Published">Published</option>
            </select>
        </div>
        <div id="fullcalendar" class="w-full"></div>
        <!-- Legend -->
        <div class="flex flex-wrap gap-4 mt-6 text-sm justify-center">
            <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-blue-500 block"></span> Active</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-green-500 block"></span> Completed</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-yellow-400 block"></span> Published</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-pink-500 block"></span> Seminar</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-purple-500 block"></span> Webinar</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-orange-500 block"></span> Outreach</div>
            <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-gray-500 block"></span> Other</div>
        </div>
    </div>
</div>

<!-- Responsive tweaks for filter controls -->
<style>
@media (max-width: 768px) {
  #calendarFilters { flex-direction: column !important; gap: 0.5rem !important; }
  #calendarFilters select { width: 100%; }
}
</style>

<!-- Event Details Modal -->
<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md relative">
        <button id="closeEventModal" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 text-xl">&times;</button>
        <h2 id="modalTitle" class="text-xl font-bold mb-2"></h2>
        <div class="mb-2 text-sm text-gray-500" id="modalTypeStatus"></div>
        <div class="mb-2 text-sm" id="modalTime"></div>
        <div class="mb-4 text-gray-700" id="modalDescription"></div>
        <div class="flex gap-2">
            <a id="modalEditBtn" href="#" class="pc-btn-primary">Edit</a>
            <form id="modalDeleteForm" method="POST" action="" onsubmit="return confirm('Delete this event?');">
                <input type="hidden" name="event_id" id="modalDeleteId" value="">
                <button type="submit" name="delete_event" class="pc-btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-content min-h-screen flex flex-col bg-white pt-16 lg:ml-64 transition-all duration-300">
    <div class="flex-1 p-6">
        <?php include 'includes/breadcrumb.php'; ?>
        
        <div class="max-w-7xl mx-auto">
            <!-- Success Message -->
            <?php if ($success_message): ?>
                <div id="successAlert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between shadow-sm" role="alert">
                    <span class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $success_message; ?>
                    </span>
                    <button onclick="closeAlert('successAlert')" class="text-green-500 hover:text-green-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error_message): ?>
                <div id="errorAlert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between shadow-sm" role="alert">
                    <span class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error_message; ?>
                    </span>
                    <button onclick="closeAlert('errorAlert')" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                    Manage Events
                </h1>
                <div class="flex space-x-2">
                    <button id="toggleCalendarBtn" class="pc-btn-secondary" type="button">
                        <i class="fas fa-calendar"></i> View Calendar
                    </button>
                    <button type="button" onclick="printTable()" class="pc-btn-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <!-- Search Bar and Entries Dropdown -->
                    <div class="flex items-center gap-4 flex-1">
                        <div class="relative flex-1 max-w-md">
                            <input type="text" id="searchInput" placeholder="Search events..." class="search-input w-full" value="<?php echo htmlspecialchars($search); ?>">
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
                            <option value="Draft" <?php echo $status === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="Published" <?php echo $status === 'Published' ? 'selected' : ''; ?>>Published</option>
                            <option value="Cancelled" <?php echo $status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        
                        <!-- Type Filter -->
                        <select id="typeSelect" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm">
                            <option value="">All Types</option>
                            <option value="Reunion" <?php echo $type === 'Reunion' ? 'selected' : ''; ?>>Reunion</option>
                            <option value="Seminar" <?php echo $type === 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
                            <option value="Webinar" <?php echo $type === 'Webinar' ? 'selected' : ''; ?>>Webinar</option>
                            <option value="Career Fair" <?php echo $type === 'Career Fair' ? 'selected' : ''; ?>>Career Fair</option>
                            <option value="Outreach" <?php echo $type === 'Outreach' ? 'selected' : ''; ?>>Outreach</option>
                            <option value="Other" <?php echo $type === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                        
                        <!-- Visibility Filter -->
                        <select id="visibilitySelect" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm">
                            <option value="">All Visibility</option>
                            <option value="Public" <?php echo $visibility === 'Public' ? 'selected' : ''; ?>>Public</option>
                            <option value="Private" <?php echo $visibility === 'Private' ? 'selected' : ''; ?>>Private</option>
                        </select>
                        
                        <!-- Reset Button -->
                        <button type="button" id="resetFilterBtn" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 transition-colors duration-200 text-sm">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Table Section -->
            <div class="table-container">
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Event Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Visibility</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                                <?php if (count($result) > 0): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($row['event_title']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php
                                                $start = new DateTime($row['start_datetime']);
                                                $end = new DateTime($row['end_datetime']);
                                                echo $start->format('M d, Y h:i A') . ' - ' . $end->format('M d, Y h:i A');
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php
                                                    if ($row['physical_address']) {
                                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Onsite</span>';
                                                    } elseif ($row['online_link']) {
                                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Virtual</span>';
                                                    } else {
                                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">TBA</span>';
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['event_type']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    <?php
                                                    switch($row['status']) {
                                                        case 'Published':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'Draft':
                                                            echo 'bg-gray-100 text-gray-800';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        case 'Completed':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                            break;
                                                    }
                                                    ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    <?php echo $row['visibility'] === 'Public' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                    <?php echo htmlspecialchars($row['visibility']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button onclick="viewEvent(<?php echo $row['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-900 hover:bg-blue-50 p-1 rounded transition-colors duration-200" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="edit-event.php?id=<?php echo $row['id']; ?>"
                                                        class="text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 p-1 rounded transition-colors duration-200" title="Edit Event">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="deleteEvent(<?php echo $row['id']; ?>)"
                                                            class="text-red-600 hover:text-red-900 hover:bg-red-50 p-1 rounded transition-colors duration-200" title="Delete Event">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <button onclick="toggleEventStatus(<?php echo $row['id']; ?>, '<?php echo $row['status']; ?>')"
                                                            class="text-green-600 hover:text-green-900 hover:bg-green-50 p-1 rounded transition-colors duration-200" title="Toggle Status">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            <div class="flex flex-col items-center py-8">
                                                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500">No events found</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4">
                <div id="paginationInfo" class="text-sm text-gray-700">
                    Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $entries_per_page, $total_records); ?></span> of <span class="font-medium"><?php echo $total_records; ?></span> results
                </div>
                <div id="paginationControls">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($total_pages > 1): ?>
                            <?php if ($current_page_num > 1): ?>
                                <button onclick="loadPage(<?php echo $current_page_num - 1; ?>)" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                                    <span class="sr-only">Previous</span>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $current_page_num - 2);
                            $end_page = min($total_pages, $current_page_num + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <button onclick="loadPage(<?php echo $i; ?>)" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $current_page_num ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?> transition-colors duration-200">
                                    <?php echo $i; ?>
                                </button>
                            <?php endfor; ?>
                            
                            <?php if ($current_page_num < $total_pages): ?>
                                <button onclick="loadPage(<?php echo $current_page_num + 1; ?>)" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                                    <span class="sr-only">Next</span>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Event Modal -->
<div id="viewEventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden modal-overlay">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Event Details</h3>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be loaded here -->
        </div>
        <div class="modal-footer">
            <button onclick="closeModal()" class="pc-btn-secondary">
                <i class="fas fa-times"></i>
                Close
            </button>
        </div>
    </div>
</div>

<script>
// Global variables
let currentSearch = '<?php echo htmlspecialchars($search); ?>';
let currentStatus = '<?php echo htmlspecialchars($status); ?>';
let currentType = '<?php echo htmlspecialchars($type); ?>';
let currentVisibility = '<?php echo htmlspecialchars($visibility); ?>';
let currentIdSort = '<?php echo htmlspecialchars($id_sort); ?>';
let currentEntries = <?php echo $entries_per_page; ?>;
let currentPage = <?php echo $current_page_num; ?>;
let searchTimeout;

// AJAX function to load table data
function loadTableData(search = currentSearch, status = currentStatus, type = currentType, visibility = currentVisibility, id_sort = currentIdSort, entries = currentEntries, page = currentPage) {
    // Show loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.add('show');
    
    // Create form data
    const formData = new FormData();
    formData.append('search', search);
    formData.append('status', status);
    formData.append('type', type);
    formData.append('visibility', visibility);
    formData.append('id_sort', id_sort);
    formData.append('entries', entries);
    formData.append('page', page);
    
    // Make AJAX request
    fetch('events_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update table body
            document.getElementById('tableBody').innerHTML = data.table_html;
            
            // Update pagination
            document.getElementById('paginationControls').innerHTML = '<nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">' + data.pagination_html + '</nav>';
            document.getElementById('paginationInfo').innerHTML = data.pagination_info;
            
            // Update current values
            currentSearch = search;
            currentStatus = status;
            currentType = type;
            currentVisibility = visibility;
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
        loadTableData(searchValue, currentStatus, currentType, currentVisibility, currentIdSort, currentEntries, 1);
    }, 500); // 500ms delay
});

// Filter change handlers
document.getElementById('statusSelect').addEventListener('change', function() {
    loadTableData(currentSearch, this.value, currentType, currentVisibility, currentIdSort, currentEntries, 1);
});

document.getElementById('typeSelect').addEventListener('change', function() {
    loadTableData(currentSearch, currentStatus, this.value, currentVisibility, currentIdSort, currentEntries, 1);
});

document.getElementById('visibilitySelect').addEventListener('change', function() {
    loadTableData(currentSearch, currentStatus, currentType, this.value, currentIdSort, currentEntries, 1);
});

document.getElementById('idSortSelect').addEventListener('change', function() {
    loadTableData(currentSearch, currentStatus, currentType, currentVisibility, this.value, currentEntries, 1);
});

// Entries per page change
document.getElementById('entriesPerPage').addEventListener('change', function() {
    const entriesValue = parseInt(this.value);
    loadTableData(currentSearch, currentStatus, currentType, currentVisibility, currentIdSort, entriesValue, 1);
});

// Pagination
function loadPage(page) {
    loadTableData(currentSearch, currentStatus, currentType, currentVisibility, currentIdSort, currentEntries, page);
}

// Reset filters
document.getElementById('resetFilterBtn').addEventListener('click', function() {
    // Reset all form elements
    document.getElementById('searchInput').value = '';
    document.getElementById('statusSelect').value = '';
    document.getElementById('typeSelect').value = '';
    document.getElementById('visibilitySelect').value = '';
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

// Close alerts
function closeAlert(alertId) {
    document.getElementById(alertId).style.display = 'none';
}

// View event modal
function viewEvent(eventId) {
    // You can implement AJAX call here to fetch event details
    // For now, we'll show a simple message
    document.getElementById('modalTitle').textContent = 'Event Details';
    document.getElementById('modalBody').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-calendar-alt text-4xl text-blue-500 mb-4"></i>
            <p class="text-gray-600">Event details for ID: ${eventId}</p>
            <p class="text-sm text-gray-500 mt-2">Detailed view functionality can be implemented here.</p>
        </div>
    `;
    document.getElementById('viewEventModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('viewEventModal').classList.add('hidden');
}

// Delete event
function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="delete_event" value="1">
            <input type="hidden" name="event_id" value="${eventId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Toggle event status
function toggleEventStatus(eventId, currentStatus) {
    let newStatus;
    let actionText;
    
    switch(currentStatus) {
        case 'Draft':
            newStatus = 'Published';
            actionText = 'publish';
            break;
        case 'Published':
            newStatus = 'Cancelled';
            actionText = 'cancel';
            break;
        case 'Cancelled':
            newStatus = 'Published';
            actionText = 'publish';
            break;
        case 'Completed':
            newStatus = 'Published';
            actionText = 'reactivate';
            break;
        default:
            newStatus = 'Published';
            actionText = 'publish';
    }
    
    if (confirm(`Are you sure you want to ${actionText} this event?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="toggle_status" value="1">
            <input type="hidden" name="event_id" value="${eventId}">
            <input type="hidden" name="new_status" value="${newStatus}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
document.getElementById('viewEventModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Toggle between table/list and calendar view
const toggleBtn = document.getElementById('toggleCalendarBtn');
const calendarView = document.getElementById('calendarView');
const tableView = document.querySelector('.table-container');
const calendarFilters = document.getElementById('calendarFilters');
const viewListBtn = document.getElementById('viewListBtn');
let calendarInitialized = false;

if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
        calendarView.style.display = '';
        setTimeout(() => {
            calendarView.classList.add('flex');
            calendarView.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
            calendarView.classList.add('opacity-100', 'scale-100');
        }, 10);
        if (tableView) tableView.style.display = 'none';
        toggleBtn.style.display = 'none';
        if (!calendarInitialized) {
            initFullCalendar();
            calendarInitialized = true;
        }
    });
}

if (viewListBtn) {
    viewListBtn.addEventListener('click', function() {
        calendarView.classList.remove('opacity-100', 'scale-100');
        calendarView.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
        setTimeout(() => {
            calendarView.style.display = 'none';
            calendarView.classList.remove('flex');
            if (tableView) tableView.style.display = '';
            toggleBtn.style.display = '';
        }, 500);
    });
}

let calendar;
let currentTypeFilter = '';
let currentStatusFilter = '';

function getEventColor(event) {
    // Priority: status color, then type color
    if (event.status === 'Active') return '#3b82f6'; // blue
    if (event.status === 'Completed') return '#22c55e'; // green
    if (event.status === 'Published') return '#facc15'; // yellow
    if (event.event_type === 'Seminar') return '#ec4899'; // pink
    if (event.event_type === 'Webinar') return '#a21caf'; // purple
    if (event.event_type === 'Outreach') return '#f97316'; // orange
    return '#6b7280'; // gray for other
}

function initFullCalendar() {
    const calendarEl = document.getElementById('fullcalendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        height: 650,
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('fetch_events_api.php')
                .then(res => res.json())
                .then(data => {
                    let events = data;
                    if (currentTypeFilter) {
                        events = events.filter(e => e.event_type === currentTypeFilter);
                    }
                    if (currentStatusFilter) {
                        events = events.filter(e => e.status === currentStatusFilter);
                    }
                    // Add color property
                    events = events.map(e => ({ ...e, backgroundColor: getEventColor(e), borderColor: getEventColor(e) }));
                    successCallback(events);
                })
                .catch(failureCallback);
        },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventDidMount: function(info) {
            // Tooltip
            tippy(info.el, {
                content: `<strong>${info.event.title}</strong><br>${info.event.extendedProps.event_type} | ${info.event.extendedProps.status}<br>${info.event.start.toLocaleString()} - ${info.event.end ? info.event.end.toLocaleString() : ''}`,
                allowHTML: true,
                theme: 'light-border',
                placement: 'top',
            });
            // Highlight today
            if (info.event.startStr === new Date().toISOString().slice(0, 10)) {
                info.el.style.boxShadow = '0 0 0 3px #3b82f6';
            }
        },
        eventClick: function(info) {
            showEventModal(info.event);
        }
    });
    calendar.render();
}

document.getElementById('filterType').addEventListener('change', function() {
    currentTypeFilter = this.value;
    if (calendar) calendar.refetchEvents();
});

document.getElementById('filterStatus').addEventListener('change', function() {
    currentStatusFilter = this.value;
    if (calendar) calendar.refetchEvents();
});

// Modal logic
function showEventModal(event) {
    document.getElementById('modalTitle').textContent = event.title;
    document.getElementById('modalTypeStatus').textContent = `${event.extendedProps.event_type} | ${event.extendedProps.status}`;
    document.getElementById('modalTime').textContent = `${event.start.toLocaleString()} - ${event.end ? event.end.toLocaleString() : ''}`;
    document.getElementById('modalDescription').textContent = event.extendedProps.description || '';
    document.getElementById('modalEditBtn').href = `edit-event.php?id=${event.id}`;
    document.getElementById('modalDeleteId').value = event.id;
    document.getElementById('modalDeleteForm').action = 'manage-events.php';
    document.getElementById('eventModal').classList.remove('hidden');
}

document.getElementById('closeEventModal').onclick = function() {
    document.getElementById('eventModal').classList.add('hidden');
};

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('eventModal').classList.add('hidden');
});

function printTable() {
    // Find the table section
    var tableSection = document.querySelector('.table-container');
    if (!tableSection) return;
    var printWindow = window.open('', '', 'height=700,width=1000');
    printWindow.document.write('<html><head><title>Print Events</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">');
    printWindow.document.write('<style>body{font-family:sans-serif;padding:2rem;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #e5e7eb;padding:8px;}th{background:#f3f4f6;}tr:nth-child(even){background:#f9fafb;}h2{margin-bottom:1rem;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2>Events</h2>');
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
