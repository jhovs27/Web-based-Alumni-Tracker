<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
include 'includes/breadcrumb.php';
include 'config/database.php';

// Add CSS for proper spacing and enhanced design
?>
<style>
@media (min-width: 1024px) {
    .main-content {
        margin-left: 16rem !important;
        width: calc(100% - 16rem) !important;
        padding-top: 8rem !important;
    }
}

/* Loading overlay styles - FIXED */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: none; /* Changed from flex to none by default */
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.loading-overlay.show {
    display: flex; /* Only show when .show class is added */
}

.table-container {
    position: relative;
    transition: opacity 0.3s ease;
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Enhanced button hover effects */
.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}
</style>

<?php
// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Departments', 'url' => 'departments.php', 'active' => true]
];

// Initial load parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Get initial data for page load
$count_query = "SELECT COUNT(*) as total FROM department WHERE 1=1";
$params = [];
if (!empty($search)) {
    $count_query .= " AND (DepartmentName LIKE :search OR Description LIKE :search OR DepartmentHead LIKE :search)";
    $params[':search'] = "%$search%";
}
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $entries_per_page);

// Fetch all program chairs and map by department id
$chair_map = [];
$chair_stmt = $conn->query("SELECT full_name, program FROM program_chairs");
while ($row = $chair_stmt->fetch(PDO::FETCH_ASSOC)) {
    $chair_map[$row['program']] = $row['full_name'];
}

// Build the query with search, filter, and pagination
$query = "SELECT id, DepartmentName, Description, DepartmentHead, Designation, Active FROM department WHERE 1=1";
if (!empty($search)) {
    $query .= " AND (DepartmentName LIKE :search OR Description LIKE :search OR DepartmentHead LIKE :search)";
}
if (!empty($filter)) {
    $query .= " ORDER BY " . $filter;
} else {
    $query .= " ORDER BY DepartmentName ASC";
}
$query .= " LIMIT :offset, :limit";

// Execute query with error handling
try {
    $stmt = $conn->prepare($query);
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
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

<div class="main-content" style="padding-top: 10rem; margin-left: 0; width: 100%; min-height: 100vh;">
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Departments', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
        
        <!-- Alert Messages -->
        <?php if ($success_message): ?>
        <div id="successAlert" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
            <div class="flex items-center">
                <div class="py-1">
                    <svg class="h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold">Success!</p>
                    <p><?php echo $success_message; ?></p>
                </div>
                <button onclick="closeAlert('successAlert')" class="ml-auto">
                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div id="errorAlert" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
            <div class="flex items-center">
                <div class="py-1">
                    <svg class="h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold">Error!</p>
                    <p><?php echo $error_message; ?></p>
                </div>
                <button onclick="closeAlert('errorAlert')" class="ml-auto">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fas fa-building text-blue-500"></i>
                Departments
            </h1>
            <button onclick="openAddDepartmentModal()"
                    class="btn-primary text-white px-6 py-3 rounded-lg transition-all duration-200 flex items-center shadow-lg">
                <i class="fas fa-plus mr-2"></i>
                Add Department
            </button>
        </div>

        <!-- Search, Filter, and Entries Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Search Bar -->
                <div class="flex-1">
                    <div class="relative">
                        <input type="text"
                               id="searchInput"
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search departments..."
                               class="search-input w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Entries Per Page -->
                <div class="flex items-center gap-2">
                    <label for="entriesSelect" class="text-sm text-gray-600 whitespace-nowrap">Show</label>
                    <select id="entriesSelect" 
                            class="border border-gray-300 rounded-lg px-3 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="5" <?php echo $entries_per_page == 5 ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                    <span class="text-sm text-gray-600 whitespace-nowrap">entries</span>
                </div>
                
                <!-- Filter Button -->
                <div class="relative">
                    <button onclick="toggleFilterDropdown()"
                            class="bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    <!-- Filter Dropdown -->
                    <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-1 z-20 border">
                        <button onclick="applyFilter('id ASC')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            ID (Low to High)
                        </button>
                        <button onclick="applyFilter('id DESC')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            ID (High to Low)
                        </button>
                        <button onclick="applyFilter('DepartmentName ASC')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            Name (A-Z)
                        </button>
                        <button onclick="applyFilter('DepartmentName DESC')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            Name (Z-A)
                        </button>
                        <button onclick="applyFilter('DepartmentHead ASC')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            Department Head (A-Z)
                        </button>
                        <button onclick="applyFilter('DepartmentHead DESC')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            Department Head (Z-A)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section - FIXED -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="table-container">
                <!-- Loading overlay - FIXED -->
                <div id="loadingOverlay" class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department Head</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                            <?php if (count($result) > 0): ?>
                                <?php foreach ($result as $row): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($row['DepartmentName']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Description']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php
                                            $dept_id = $row['id'];
                                            if (!empty($chair_map[$dept_id])) {
                                                echo htmlspecialchars($chair_map[$dept_id]);
                                            } else {
                                                echo '<span class="text-yellow-600">Not assigned</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Designation']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['Active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo $row['Active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                            <button onclick="editDepartment(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['DepartmentName']); ?>', '<?php echo htmlspecialchars($row['Description']); ?>', '<?php echo htmlspecialchars($row['DepartmentHead']); ?>', '<?php echo htmlspecialchars($row['Designation']); ?>', <?php echo $row['Active']; ?>)"
                                                    class="text-blue-600 hover:text-blue-900 transition-colors duration-200 mr-3 hover:bg-blue-50 p-1 rounded">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="confirmDeleteDepartment('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['DepartmentName']); ?>')"
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-200 hover:bg-red-50 p-1 rounded">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fas fa-search text-4xl mb-3 text-gray-300"></i>
                                            <p class="text-lg font-medium">No departments found</p>
                                            <p class="text-sm mt-1">Try adjusting your search or filter to find what you're looking for.</p>
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
        <div class="flex items-center justify-between mt-6">
            <div id="paginationInfo" class="text-sm text-gray-700">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entries_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
            </div>
            <div id="paginationControls" class="flex gap-2">
                <?php if ($total_pages > 1): ?>
                    <!-- First Page -->
                    <?php if ($current_page > 1): ?>
                        <button onclick="loadPage(1)" class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">First</button>
                    <?php endif; ?>
                    
                    <!-- Previous Page -->
                    <?php if ($current_page > 1): ?>
                        <button onclick="loadPage(<?php echo $current_page - 1; ?>)" class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">Previous</button>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <button onclick="loadPage(<?php echo $i; ?>)" class="px-3 py-1 border rounded-lg <?php echo $i === $current_page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'; ?> transition-colors duration-200">
                            <?php echo $i; ?>
                        </button>
                    <?php endfor; ?>
                    
                    <!-- Next Page -->
                    <?php if ($current_page < $total_pages): ?>
                        <button onclick="loadPage(<?php echo $current_page + 1; ?>)" class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">Next</button>
                    <?php endif; ?>
                    
                    <!-- Last Page -->
                    <?php if ($current_page < $total_pages): ?>
                        <button onclick="loadPage(<?php echo $total_pages; ?>)" class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">Last</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div id="addDepartmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 transform transition-all duration-200 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Add New Department</h3>
            <button onclick="closeAddDepartmentModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addDepartmentForm" action="department_actions.php" method="POST">
            <div class="mb-4">
                <label for="departmentName" class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                <input type="text" id="departmentName" name="departmentName" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" id="description" name="description" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="departmentHead" class="block text-sm font-medium text-gray-700 mb-1">Department Head</label>
                <input type="text" id="departmentHead" name="departmentHead" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="designation" class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                <input type="text" id="designation" name="designation" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="active" name="active" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAddDepartmentModal()"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit" name="add_department"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    Add Department
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Department Modal -->
<div id="editDepartmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 transform transition-all duration-200 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Edit Department</h3>
            <button onclick="closeEditDepartmentModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editDepartmentForm" action="department_actions.php" method="POST">
            <input type="hidden" id="edit_id" name="edit_id">
            <div class="mb-4">
                <label for="edit_departmentName" class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                <input type="text" id="edit_departmentName" name="departmentName" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" id="edit_description" name="description" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="edit_departmentHead" class="block text-sm font-medium text-gray-700 mb-1">Department Head</label>
                <input type="text" id="edit_departmentHead" name="departmentHead" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="edit_designation" class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                <input type="text" id="edit_designation" name="designation" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="edit_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="edit_active" name="active" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEditDepartmentModal()"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit" name="edit_department"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    Update Department
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteDepartmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 transform transition-all duration-200 shadow-xl">
        <div class="text-center mb-4">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
            <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
        </div>
        <p class="text-gray-600 text-center mb-6">
            Are you sure you want to delete the department <span id="departmentName" class="font-medium"></span>?
            This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteDepartmentModal()"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition-colors duration-200">
                Cancel
            </button>
            <button onclick="deleteDepartment()"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
// Global variables
let currentSearch = '<?php echo htmlspecialchars($search); ?>';
let currentFilter = '<?php echo htmlspecialchars($filter); ?>';
let currentEntries = <?php echo $entries_per_page; ?>;
let currentPage = <?php echo $current_page; ?>;
let searchTimeout;

// AJAX function to load table data - FIXED
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
    fetch('departments_ajax.php', {
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
    
    // Clear previous timeout
    clearTimeout(searchTimeout);
    
    // Set new timeout for debounced search
    searchTimeout = setTimeout(() => {
        loadTableData(searchValue, currentFilter, currentEntries, 1);
    }, 500); // 500ms delay
});

// Entries per page change
document.getElementById('entriesSelect').addEventListener('change', function() {
    const entriesValue = parseInt(this.value);
    loadTableData(currentSearch, currentFilter, entriesValue, 1);
});

// Filter functionality
function applyFilter(filterValue) {
    document.getElementById('filterDropdown').classList.add('hidden');
    loadTableData(currentSearch, filterValue, currentEntries, 1);
}

// Pagination
function loadPage(page) {
    loadTableData(currentSearch, currentFilter, currentEntries, page);
}

// Filter Dropdown
function toggleFilterDropdown() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown.classList.toggle('hidden');
}

// Close filter dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('filterDropdown');
    const filterButton = e.target.closest('button');
    if (!filterButton || !filterButton.onclick || filterButton.onclick.toString().indexOf('toggleFilterDropdown') === -1) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    }
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

// Add Department Modal
function openAddDepartmentModal() {
    document.getElementById('addDepartmentModal').classList.remove('hidden');
    document.getElementById('addDepartmentModal').classList.add('flex');
}

function closeAddDepartmentModal() {
    document.getElementById('addDepartmentModal').classList.add('hidden');
    document.getElementById('addDepartmentModal').classList.remove('flex');
}

// Edit Department Modal
function editDepartment(id, name, description, head, designation, active) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_departmentName').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_departmentHead').value = head;
    document.getElementById('edit_designation').value = designation;
    document.getElementById('edit_active').value = active;
    
    document.getElementById('editDepartmentModal').classList.remove('hidden');
    document.getElementById('editDepartmentModal').classList.add('flex');
}

function closeEditDepartmentModal() {
    document.getElementById('editDepartmentModal').classList.add('hidden');
    document.getElementById('editDepartmentModal').classList.remove('flex');
}

// Delete Department Modal
let departmentToDelete = null;

function confirmDeleteDepartment(id, name) {
    departmentToDelete = id;
    document.getElementById('departmentName').textContent = name;
    document.getElementById('deleteDepartmentModal').classList.remove('hidden');
    document.getElementById('deleteDepartmentModal').classList.add('flex');
}

function closeDeleteDepartmentModal() {
    document.getElementById('deleteDepartmentModal').classList.add('hidden');
    document.getElementById('deleteDepartmentModal').classList.remove('flex');
    departmentToDelete = null;
}

function deleteDepartment() {
    if (departmentToDelete) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'department_actions.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_department';
        input.value = departmentToDelete;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Close alerts
function closeAlert(alertId) {
    document.getElementById(alertId).style.display = 'none';
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    const addModal = document.getElementById('addDepartmentModal');
    const editModal = document.getElementById('editDepartmentModal');
    const deleteModal = document.getElementById('deleteDepartmentModal');
    
    if (e.target === addModal) {
        closeAddDepartmentModal();
    }
    if (e.target === editModal) {
        closeEditDepartmentModal();
    }
    if (e.target === deleteModal) {
        closeDeleteDepartmentModal();
    }
});

// Close modals when pressing Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddDepartmentModal();
        closeEditDepartmentModal();
        closeDeleteDepartmentModal();
    }
});

// Session refresh (keep existing functionality)
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
