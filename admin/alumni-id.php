<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Add CSS for proper spacing
?>
<style>
@media (min-width: 1024px) {
    .main-content {
        margin-left: 16rem !important;
        width: calc(100% - 16rem) !important;
        padding-top: 8rem !important;
    }
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
</style>

<?php
// Handle Alumni ID generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_id'], $_POST['student_no'])) {
    $student_no = $_POST['student_no'];
    
    // 1. Get MainID from listgradsub
    $stmt = $conn->prepare("SELECT MainID FROM listgradsub WHERE StudentNo = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$student_no]);
    $sub = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sub) {
        $_SESSION['success'] = "No graduation record found for student $student_no (listgradsub).";
        header('Location: alumni-id.php');
        exit;
    }
    
    $main_id = $sub['MainID'];
    
    // 2. Get DateOfGraduation and Semester from listgradmain
    $stmt = $conn->prepare("SELECT DateOfGraduation, Semester FROM listgradmain WHERE id = ? LIMIT 1");
    $stmt->execute([$main_id]);
    $main = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$main) {
        $_SESSION['success'] = "No graduation main record found for student $student_no (listgradmain).";
        header('Location: alumni-id.php');
        exit;
    }
    
    $date_of_grad = $main['DateOfGraduation'];
    $semester = $main['Semester'];
    $grad_year = $date_of_grad ? date('Y', strtotime($date_of_grad)) : null;
    
    if (!$grad_year) {
        $_SESSION['success'] = "Invalid graduation date for student $student_no.";
        header('Location: alumni-id.php');
        exit;
    }
    
    // 3. Count the number of registrations for the student
    $stmt = $conn->prepare("SELECT COUNT(*) as reg_count FROM registration WHERE StudentNo = ?");
    $stmt->execute([$student_no]);
    $reg_count = $stmt->fetch(PDO::FETCH_ASSOC)['reg_count'] ?? 0;
    
    // 4. Format alumni_id: last 2 digits of year + semester + '-' + zero-padded reg count
    $alumni_id = sprintf('%s%s-%04d', substr($grad_year, -2), $semester, $reg_count);
    
    // 5. Insert into alumni_ids table
    $stmt = $conn->prepare("INSERT INTO alumni_ids (student_no, alumni_id, graduation_year, registration_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$student_no, $alumni_id, $grad_year, $reg_count]);
    
    $_SESSION['success'] = "Alumni ID $alumni_id generated successfully for student $student_no.";
    header('Location: alumni-id.php');
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
include 'includes/breadcrumb.php';

// --- Search, Filter, Pagination Logic ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Build WHERE clause for search
$where = '1=1';
$params = [];
if ($search !== '') {
    $where .= " AND (s.StudentNo LIKE :search OR s.LastName LIKE :search OR s.FirstName LIKE :search OR s.MiddleName LIKE :search OR c.accro LIKE :search OR a.alumni_id LIKE :search)";
    $params[':search'] = "%$search%";
}

// Sorting
$sort_options = [
    'StudentNo ASC' => 'Student No (ASC)',
    'StudentNo DESC' => 'Student No (DESC)',
    'LastName ASC' => 'Name (A-Z)',
    'LastName DESC' => 'Name (Z-A)',
    'accro ASC' => 'Course (A-Z)',
    'accro DESC' => 'Course (Z-A)',
    'alumni_id ASC' => 'Alumni ID (ASC)',
    'alumni_id DESC' => 'Alumni ID (DESC)'
];
$order_by = 's.LastName, s.FirstName';
if ($filter && array_key_exists($filter, $sort_options)) {
    $order_by = $filter;
}

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM students s LEFT JOIN course c ON s.Course = c.id LEFT JOIN alumni_ids a ON a.student_no = s.StudentNo WHERE $where";
$count_stmt = $conn->prepare($count_query);
foreach ($params as $k => $v) {
    $count_stmt->bindValue($k, $v);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$total_pages = ceil($total_records / $entries_per_page);

// Fetch students for current page
$query = "
    SELECT
        s.StudentNo,
        s.LastName,
        s.FirstName,
        s.MiddleName,
        c.accro,
        a.alumni_id,
        -- Generate on the fly if not present
        IFNULL(
            a.alumni_id,
            CONCAT(
                RIGHT(YEAR(lgm.DateOfGraduation), 2),
                lgm.Semester,
                '-',
                LPAD(COALESCE(reg_counts.reg_count, 0), 4, '0')
            )
        ) AS display_alumni_id
    FROM students s
    LEFT JOIN course c ON s.Course = c.id
    LEFT JOIN alumni_ids a ON a.student_no = s.StudentNo
    LEFT JOIN listgradsub lgs ON lgs.StudentNo = s.StudentNo
    LEFT JOIN listgradmain lgm ON lgm.id = lgs.MainID
    LEFT JOIN (
        SELECT StudentNo, COUNT(*) as reg_count
        FROM registration
        GROUP BY StudentNo
    ) reg_counts ON reg_counts.StudentNo = s.StudentNo
    WHERE $where
    ORDER BY $order_by
    LIMIT :offset, :limit";

$stmt = $conn->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content" style="padding-top: 10rem; margin-left: 0; width: 100%; min-height: 100vh;">
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Alumni ID', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
                
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-700 flex items-center gap-3">
                <i class="fas fa-id-card text-blue-500"></i>
                Alumni ID Management
            </h1>
        </div>

        <!-- Success Message -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span class="font-medium"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search, Filter, and Entries Section -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text"
                                id="searchInput"
                                value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search by student no, name, course, alumni ID..."
                                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>
            <!-- Entries Per Page -->
            <div class="flex items-center gap-2">
                <label for="entries" class="text-sm text-gray-600">Show</label>
                <select id="entriesSelect" name="entries"
                        class="border rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="5" <?php echo $entries_per_page == 5 ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?php echo $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
                </select>
                <span class="text-sm text-gray-600">entries</span>
            </div>
            <!-- Filter Dropdown -->
            <div class="relative">
                <button type="button" onclick="toggleFilterDropdown()"
                         class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center">
                    <i class="fas fa-filter mr-2"></i>
                    Filter
                </button>
                <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-1 z-10">
                    <?php foreach ($sort_options as $key => $label): ?>
                        <button onclick="applyFilter('<?php echo $key; ?>')"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?php echo $filter === $key ? 'font-bold bg-blue-50' : ''; ?>">
                            <?php echo $label; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Card for the Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-600">Graduate List</h2>
            </div>
            <div class="relative">
                <!-- Loading overlay -->
                <div id="loadingOverlay" class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">Student No</th>
                                <th class="px-6 py-3 text-center align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                <th class="px-6 py-3 text-center align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3 text-center align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">Alumni ID</th>
                                <th class="px-6 py-3 text-center align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center align-middle text-gray-500">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                    No students found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $stu): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm font-mono text-gray-600"><?php echo htmlspecialchars($stu['StudentNo']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm text-gray-800"><?php echo htmlspecialchars($stu['LastName'] . ', ' . $stu['FirstName'] . ' ' . $stu['MiddleName']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm text-gray-500"><?php echo htmlspecialchars($stu['accro']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm font-mono">
                                        <?php if ($stu['alumni_id']): ?>
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <?php echo htmlspecialchars($stu['alumni_id']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Not Generated
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm font-medium">
                                        <?php if (!$stu['alumni_id']): ?>
                                            <button type="button"
                                                    onclick="openConfirmationModal('<?php echo htmlspecialchars($stu['StudentNo']); ?>', '<?php echo htmlspecialchars($stu['LastName'] . ', ' . $stu['FirstName']); ?>')"
                                                    class="text-indigo-600 hover:text-indigo-900 transition duration-150 ease-in-out font-semibold">
                                                <i class="fas fa-magic mr-1"></i>Generate ID
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 cursor-not-allowed">Generated</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-4">
            <div id="paginationInfo" class="text-sm text-gray-700">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entries_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
            </div>
            <div id="paginationControls" class="flex gap-2">
                <?php if ($total_pages > 1): ?>
                    <!-- First Page -->
                    <?php if ($current_page > 1): ?>
                        <button onclick="loadPage(1)"
                            class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            First
                        </button>
                    <?php endif; ?>
                    <!-- Previous Page -->
                    <?php if ($current_page > 1): ?>
                        <button onclick="loadPage(<?php echo $current_page - 1; ?>)"
                            class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            Previous
                        </button>
                    <?php endif; ?>
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <button onclick="loadPage(<?php echo $i; ?>)"
                            class="px-3 py-1 border rounded-lg <?php echo $i === $current_page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'; ?> transition-colors duration-200">
                            <?php echo $i; ?>
                        </button>
                    <?php endfor; ?>
                    <!-- Next Page -->
                    <?php if ($current_page < $total_pages): ?>
                        <button onclick="loadPage(<?php echo $current_page + 1; ?>)"
                            class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            Next
                        </button>
                    <?php endif; ?>
                    <!-- Last Page -->
                    <?php if ($current_page < $total_pages): ?>
                        <button onclick="loadPage(<?php echo $total_pages; ?>)"
                            class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            Last
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 transition-opacity duration-300">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fas fa-id-card fa-lg text-blue-500"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Generate Alumni ID</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to generate an Alumni ID for <strong id="studentName" class="font-semibold"></strong>?
                </p>
                <p class="text-xs text-gray-400 mt-1">Student No: <span id="studentNumber" class="font-mono"></span></p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="generateIdForm" method="POST" class="inline">
                    <input type="hidden" name="student_no" id="modalStudentNumberInput">
                    <button type="submit" name="generate_id"
                            class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        Proceed
                    </button>
                </form>
                <button id="cancelButton"
                        class="px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-auto ml-2 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-all duration-200 ease-in-out">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentSearch = '';
let currentFilter = '';
let currentEntries = 10;
let currentPage = 1;
let searchTimeout;

// AJAX function to load table data
function loadTableData(search = currentSearch, filter = currentFilter, entries = currentEntries, page = currentPage) {
    // Show loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.add('show');
    
    // Create form data
    const formData = new FormData();
    formData.append('search', search);
    formData.append('filter', filter);
    formData.append('entries', entries);
    formData.append('page', page);
    
    // Make AJAX request
    fetch('alumni_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update table body
            document.getElementById('tableBody').innerHTML = data.table_html;
            
            // Update pagination controls
            const paginationControls = document.getElementById('paginationControls');
            if (paginationControls && data.total_pages > 1) {
                paginationControls.innerHTML = data.pagination_html;
            }
            
            // Update pagination info
            const paginationInfo = document.getElementById('paginationInfo');
            if (paginationInfo) {
                paginationInfo.innerHTML = data.pagination_info;
            }
            
            // Update current values
            currentSearch = search;
            currentFilter = filter;
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
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadTableData(searchValue, currentFilter, currentEntries, 1);
    }, 500);
});

// Entries per page change
document.getElementById('entriesSelect').addEventListener('change', function() {
    const entriesValue = parseInt(this.value);
    loadTableData(currentSearch, currentFilter, entriesValue, 1);
});

// Filter functions
function toggleFilterDropdown() {
    var dropdown = document.getElementById('filterDropdown');
    dropdown.classList.toggle('hidden');
}

function applyFilter(filterValue) {
    loadTableData(currentSearch, filterValue, currentEntries, 1);
    document.getElementById('filterDropdown').classList.add('hidden');
}

// Pagination
function loadPage(page) {
    loadTableData(currentSearch, currentFilter, currentEntries, page);
}

// Notification function
function showNotification(message, type = 'info') {
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
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Hide filter dropdown on click outside
window.addEventListener('click', function(e) {
    var dropdown = document.getElementById('filterDropdown');
    if (dropdown && !dropdown.contains(e.target) && !e.target.closest('button')) {
        dropdown.classList.add('hidden');
    }
});

function openConfirmationModal(studentNo, studentName) {
    // Set student details in the modal
    document.getElementById('studentName').textContent = studentName;
    document.getElementById('studentNumber').textContent = studentNo;
    document.getElementById('modalStudentNumberInput').value = studentNo;
    
    // Show the modal
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('hidden');
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.add('hidden');
}

// Event listener for the cancel button
document.getElementById('cancelButton').addEventListener('click', closeConfirmationModal);

// Close modal if the user clicks outside of it
window.addEventListener('click', function(event) {
    const modal = document.getElementById('confirmationModal');
    if (event.target == modal) {
        closeConfirmationModal();
    }
});

// Session keepalive
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
</body>
<?php include 'includes/footer.php'; ?>
