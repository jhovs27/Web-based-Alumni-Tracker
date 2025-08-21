<?php
$current_page = 'manage-users-chair';
session_start();
require_once 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Set admin session variables if not set (for navbar/sidebar)
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Admin';
}
if (!isset($_SESSION['profile_photo_path'])) {
    $_SESSION['profile_photo_path'] = 'https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff';
}

// Create chair-uploads directory if it doesn't exist
$upload_dir = 'chair-uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_chair':
                createProgramChair($conn, $upload_dir);
                break;
            case 'update_status':
                updateChairStatus($conn);
                break;
            case 'update_chair':
                updateProgramChair($conn, $upload_dir);
                break;
        }
    }
}

// Function to create new Program Chair
function createProgramChair($conn, $upload_dir) {
    try {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $program = $_POST['program'];
        
        // Fetch designation from department
        $designation = '';
        if ($program) {
            $stmt = $conn->prepare("SELECT Designation FROM department WHERE id = ?");
            $stmt->execute([$program]);
            $designation = $stmt->fetchColumn();
        }
        
        // Validation
        if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($program)) {
            $_SESSION['error'] = "All fields are required.";
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid email address.";
            return;
        }
        
        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT id FROM program_chairs WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email or username already exists.";
            return;
        }
        
        // Handle profile picture upload
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowed_types)) {
                $_SESSION['error'] = "Only JPG, PNG, and GIF files are allowed.";
                return;
            }
            
            if ($file['size'] > $max_size) {
                $_SESSION['error'] = "File size must be less than 5MB.";
                return;
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $profile_picture = $filename;
            } else {
                $_SESSION['error'] = "Failed to upload profile picture.";
                return;
            }
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO program_chairs (full_name, email, username, password, program, Designation, profile_picture, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', NOW())");
        $stmt->execute([$full_name, $email, $username, $hashed_password, $program, $designation, $profile_picture]);
        
        $_SESSION['success'] = "Program Chair account created successfully!";
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

// Function to update chair status
function updateChairStatus($conn) {
    try {
        $chair_id = $_POST['chair_id'];
        $new_status = $_POST['status'] === 'Active' ? 'Active' : 'Inactive';
        
        $stmt = $conn->prepare("UPDATE program_chairs SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $chair_id]);
        
        $_SESSION['success'] = "Status updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

// Function to update Program Chair
function updateProgramChair($conn, $upload_dir) {
    try {
        $chair_id = $_POST['chair_id'];
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $program = $_POST['program'];
        
        // Fetch designation from department
        $designation = '';
        if ($program) {
            $stmt = $conn->prepare("SELECT Designation FROM department WHERE id = ?");
            $stmt->execute([$program]);
            $designation = $stmt->fetchColumn();
        }
        
        // Validation
        if (empty($full_name) || empty($email) || empty($username) || empty($program)) {
            $_SESSION['error'] = "All fields are required.";
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid email address.";
            return;
        }
        
        // Check if email or username already exists (excluding current chair)
        $stmt = $conn->prepare("SELECT id FROM program_chairs WHERE (email = ? OR username = ?) AND id != ?");
        $stmt->execute([$email, $username, $chair_id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email or username already exists.";
            return;
        }
        
        // Handle profile picture upload
        $profile_picture = $_POST['current_profile_picture'];
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowed_types)) {
                $_SESSION['error'] = "Only JPG, PNG, and GIF files are allowed.";
                return;
            }
            
            if ($file['size'] > $max_size) {
                $_SESSION['error'] = "File size must be less than 5MB.";
                return;
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Delete old profile picture if exists
                if ($profile_picture && file_exists($upload_dir . $profile_picture)) {
                    unlink($upload_dir . $profile_picture);
                }
                $profile_picture = $filename;
            } else {
                $_SESSION['error'] = "Failed to upload profile picture.";
                return;
            }
        }
        
        // Update password if provided
        $params = [$full_name, $email, $username, $program, $designation, $profile_picture, $chair_id];
        $password_sql = "";
        if (!empty($_POST['password'])) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_sql = ", password = ?";
            array_splice($params, -1, 0, [$hashed_password]);
        }
        
        // Update database
        $stmt = $conn->prepare("UPDATE program_chairs SET full_name = ?, email = ?, username = ?, program = ?, Designation = ?, profile_picture = ?" . $password_sql . " WHERE id = ?");
        $stmt->execute($params);
        
        $_SESSION['success'] = "Program Chair updated successfully!";
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

// Fetch departments for dropdown and display
$departments = [];
try {
    $stmt = $conn->query("SELECT id, DepartmentName, Description, Designation FROM department WHERE Active = 1 ORDER BY DepartmentName ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $departments[] = [
            'id' => $row['id'],
            'label' => $row['DepartmentName'] . ' - ' . $row['Description'],
            'designation' => $row['Designation']
        ];
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error fetching departments: ' . $e->getMessage();
}

// Initial load parameters (for non-AJAX requests)
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$program_filter = isset($_GET['program_filter']) ? $_GET['program_filter'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

// Build query for fetching chairs (initial load)
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if (!empty($program_filter)) {
    $where_conditions[] = "program = ?";
    $params[] = $program_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total records for pagination
try {
    $count_query = "SELECT COUNT(*) as total FROM program_chairs $where_clause";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $entries_per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 1;
}

// Fetch program chairs with pagination (JOIN department for up-to-date info)
try {
    $query = "SELECT pc.*, d.DepartmentName, d.Description, d.Designation
              FROM program_chairs pc
              JOIN department d ON pc.program = d.id
              $where_clause
              ORDER BY pc.created_at DESC
              LIMIT $offset, $entries_per_page";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $chairs = $stmt->fetchAll();
} catch (PDOException $e) {
    $chairs = [];
    $_SESSION['error'] = "Error fetching data: " . $e->getMessage();
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/navbar.php';
include 'includes/breadcrumb.php';

// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Manage Program Chairs', 'url' => 'manage-users-chair.php', 'active' => true]
];
?>

<style>
/* Loading overlay styles */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
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

.table-container {
    position: relative;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .form-grid-mobile {
        grid-template-columns: 1fr !important;
    }
    
    .btn-mobile {
        width: 100%;
        justify-content: center;
    }
    
    .modal-mobile {
        padding: 1rem;
    }
    
    .modal-content-mobile {
        max-width: 95vw;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}

.modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 50;
}

.modal-content {
    background: white;
    border-radius: 0.5rem;
    padding: 2rem;
    width: 100%;
    max-width: 42rem;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}
</style>

<div class="main-content min-h-screen pt-16 lg:ml-64 transition-all duration-300">
    <div class="p-6">
        <!-- Breadcrumb -->
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Manage Users', 'url' => ''],
            ['title' => 'Program Chairs', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
        
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manage Program Chairs</h1>
                <p class="text-gray-600 mt-1">Create and manage Program Chair accounts for Southern Leyte State University - Hinunangan Campus</p>
            </div>
            <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <i class="fas fa-plus"></i>
                Create Account
            </button>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-dismissible bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center gap-2 relative">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="absolute top-2 right-2 text-green-700 hover:text-green-900 text-xl leading-none focus:outline-none close-alert" aria-label="Close">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-dismissible bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center gap-2 relative">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="absolute top-2 right-2 text-red-700 hover:text-red-900 text-xl leading-none focus:outline-none close-alert" aria-label="Close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 form-grid-mobile">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by name, email, or username"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <select id="programFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Programs</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo $program_filter == $dept['id'] ? 'selected' : ''; ?>><?php echo $dept['label']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="flex items-end flex-col sm:flex-row gap-2">
                    <button onclick="clearFilters()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md transition-colors btn-mobile text-center">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Entries Per Page Dropdown -->
        <div class="flex items-center gap-2 mb-4">
            <label for="entriesSelect" class="text-sm text-gray-600">Show</label>
            <select id="entriesSelect" class="border rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="10" <?php if ($entries_per_page == 10) echo 'selected'; ?>>10</option>
                <option value="25" <?php if ($entries_per_page == 25) echo 'selected'; ?>>25</option>
                <option value="50" <?php if ($entries_per_page == 50) echo 'selected'; ?>>50</option>
                <option value="100" <?php if ($entries_per_page == 100) echo 'selected'; ?>>100</option>
            </select>
            <span class="text-sm text-gray-600">entries</span>
        </div>

        <!-- Chairs Table -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden table-container">
            <div class="loading-overlay" id="loadingOverlay" style="display: none;">
                <div class="loading-spinner"></div>
            </div>
            <div class="overflow-x-auto table-responsive">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profile</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="chairsTableBody" class="bg-white divide-y divide-gray-200">
                        <?php if (empty($chairs)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    <i class="fas fa-users text-4xl mb-2 block"></i>
                                    No Program Chairs found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($chairs as $chair): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <?php if ($chair['profile_picture']): ?>
                                                    <img class="h-10 w-10 rounded-full object-cover"
                                                         src="chair-uploads/<?php echo htmlspecialchars($chair['profile_picture']); ?>"
                                                         alt="Profile">
                                                <?php else: ?>
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-600"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($chair['full_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($chair['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($chair['username']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars(($chair['DepartmentName'] ?? '') . ' - ' . ($chair['Description'] ?? '')); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($chair['Designation'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $chair['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ($chair['status'] === 'Active') ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            <button onclick="viewProfile(<?php echo htmlspecialchars(json_encode($chair)); ?>)"
                                                     class="text-blue-600 hover:text-blue-900" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="editChair(<?php echo htmlspecialchars(json_encode($chair)); ?>)"
                                                     class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="toggleStatus(<?php echo $chair['id']; ?>, '<?php echo $chair['status']; ?>')"
                                                     class="<?php echo $chair['status'] === 'Active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'; ?>"
                                                     title="<?php echo $chair['status'] === 'Active' ? 'Set Inactive' : 'Set Active'; ?>">
                                                <i class="fas <?php echo $chair['status'] === 'Active' ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="flex justify-between items-center mt-4">
            <div id="paginationInfo" class="text-sm text-gray-600">
                Showing <?php echo min($total_records, $offset+1); ?> to <?php echo min($offset+$entries_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
            </div>
            <div id="paginationControls" class="flex gap-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="#" onclick="loadPage(<?php echo $i; ?>)" class="px-3 py-1 rounded <?php echo $i == $current_page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Chair Modal -->
<div id="createModal" class="modal modal-mobile">
    <div class="modal-content modal-content-mobile">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Create Program Chair Account</h2>
            <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="create_chair">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 form-grid-mobile">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="full_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                    <input type="text" name="username" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                    <div class="relative">
                        <input type="password" name="password" id="create_chair_password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                        <button type="button" tabindex="-1" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500" onclick="togglePasswordVisibility('create_chair_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters and include uppercase and lowercase letters, a number, and a special character.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program *</label>
                    <select name="program" id="program_select" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           onchange="updateDesignation()">
                        <option value="">Select Program</option>
                        <!-- Options will be populated by JS -->
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                    <input type="text" name="designation" id="designation_input" readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Max size: 5MB. Formats: JPG, PNG, GIF</p>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 flex-col sm:flex-row">
                <button type="button" onclick="closeModal('createModal')"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors btn-mobile">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors btn-mobile">
                    Create Chair
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Profile Modal -->
<div id="viewModal" class="modal modal-mobile">
    <div class="modal-content modal-content-mobile">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Program Chair Profile</h2>
            <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="profileContent" class="space-y-4">
            <!-- Profile content will be loaded here -->
        </div>
        
        <div class="flex justify-end pt-4">
            <button onclick="closeModal('viewModal')"
                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors btn-mobile">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Edit Chair Modal -->
<div id="editModal" class="modal modal-mobile">
    <div class="modal-content modal-content-mobile">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Edit Program Chair</h2>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="update_chair">
            <input type="hidden" name="chair_id" id="edit_chair_id">
            <input type="hidden" name="current_profile_picture" id="edit_current_profile_picture">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 form-grid-mobile">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="full_name" id="edit_full_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" id="edit_email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                    <input type="text" name="username" id="edit_username" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="edit_chair_password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                        <button type="button" tabindex="-1" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500" onclick="togglePasswordVisibility('edit_chair_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password.<br>Password must be at least 8 characters and include uppercase and lowercase letters, a number, and a special character.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program *</label>
                    <select name="program" id="edit_program_select" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           onchange="updateEditDesignation()">
                        <option value="">Select Program</option>
                        <!-- Options will be populated by JS -->
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                    <input type="text" name="designation" id="edit_designation_input" readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Max size: 5MB. Formats: JPG, PNG, GIF</p>
                    <div id="current_profile_display" class="mt-2"></div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 flex-col sm:flex-row">
                <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors btn-mobile">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors btn-mobile">
                    Update Chair
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Status Toggle Form -->
<form id="statusForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="chair_id" id="status_chair_id">
    <input type="hidden" name="status" id="status_new_status">
</form>

<script>
// Global variables
let currentPage = 1;
let currentSearch = '';
let currentProgramFilter = '';
let currentStatusFilter = '';
let currentEntries = 10;
let searchTimeout;

// Initialize AJAX functionality
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners
    setupEventListeners();
    
    // Initialize dropdowns
    initializeDropdowns();
    
    // Auto-dismiss alerts
    setTimeout(function() {
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
            alert.style.display = 'none';
        });
    }, 3000);
});

function setupEventListeners() {
    // Search input with debounce
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = this.value;
                currentPage = 1;
                loadData();
            }, 500);
        });
    }

    // Filter dropdowns
    const programFilter = document.getElementById('programFilter');
    if (programFilter) {
        programFilter.addEventListener('change', function() {
            currentProgramFilter = this.value;
            currentPage = 1;
            loadData();
        });
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            currentStatusFilter = this.value;
            currentPage = 1;
            loadData();
        });
    }

    // Entries per page
    const entriesSelect = document.getElementById('entriesSelect');
    if (entriesSelect) {
        entriesSelect.addEventListener('change', function() {
            currentEntries = parseInt(this.value);
            currentPage = 1;
            loadData();
        });
    }

    // Close alert buttons
    document.querySelectorAll('.close-alert').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const alertBox = this.closest('.alert-dismissible');
            if (alertBox) alertBox.style.display = 'none';
        });
    });
}

function loadData() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    }

    const params = new URLSearchParams({
        search: currentSearch,
        program_filter: currentProgramFilter,
        status_filter: currentStatusFilter,
        entries: currentEntries,
        page: currentPage
    });

    fetch(`chairs_ajax.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update table body
                const tableBody = document.getElementById('chairsTableBody');
                if (tableBody) {
                    tableBody.innerHTML = data.html;
                }

                // Update pagination info
                const paginationInfo = document.getElementById('paginationInfo');
                if (paginationInfo) {
                    paginationInfo.textContent = `Showing ${data.showing_from} to ${data.showing_to} of ${data.total_records} entries`;
                }

                // Update pagination controls
                const paginationControls = document.getElementById('paginationControls');
                if (paginationControls) {
                    paginationControls.innerHTML = data.pagination;
                }
            } else {
                console.error('Error loading data:', data.error);
                showNotification('Error loading data: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showNotification('Network error occurred', 'error');
        })
        .finally(() => {
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        });
}

function loadPage(page) {
    currentPage = page;
    loadData();
}

function clearFilters() {
    currentSearch = '';
    currentProgramFilter = '';
    currentStatusFilter = '';
    currentPage = 1;
    
    // Reset form elements
    document.getElementById('searchInput').value = '';
    document.getElementById('programFilter').value = '';
    document.getElementById('statusFilter').value = '';
    
    loadData();
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-md shadow-lg ${
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' : 
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Modal functions
function openCreateModal() {
    document.getElementById('createModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// View profile function
function viewProfile(chair) {
    const departments = <?php echo json_encode($departments); ?>;
    const content = `
        <div class="text-center mb-6">
            <div class="inline-block">
                ${chair.profile_picture ? 
                    `<img src="chair-uploads/${chair.profile_picture}" alt="Profile" class="h-32 w-32 rounded-full object-cover mx-auto border-4 border-gray-200">` :
                    `<div class="h-32 w-32 rounded-full bg-gray-300 flex items-center justify-center mx-auto border-4 border-gray-200">
                        <i class="fas fa-user text-6xl text-gray-600"></i>
                    </div>`
                }
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mt-4">${chair.full_name}</h3>
            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full ${chair.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                ${chair.status.charAt(0).toUpperCase() + chair.status.slice(1)}
            </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <p class="text-sm text-gray-900">${chair.email}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <p class="text-sm text-gray-900">${chair.username}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                <p class="text-sm text-gray-900">${chair.DepartmentName} - ${chair.Description}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                <p class="text-sm text-gray-900">${chair.Designation}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
                <p class="text-sm text-gray-900">${new Date(chair.created_at).toLocaleDateString()}</p>
            </div>
        </div>
    `;
    
    document.getElementById('profileContent').innerHTML = content;
    document.getElementById('viewModal').style.display = 'block';
}

// Edit chair function
function editChair(chair) {
    document.getElementById('edit_chair_id').value = chair.id;
    document.getElementById('edit_full_name').value = chair.full_name;
    document.getElementById('edit_email').value = chair.email;
    document.getElementById('edit_username').value = chair.username;
    document.getElementById('edit_current_profile_picture').value = chair.profile_picture || '';
    
    // Set program dropdown and designation
    var editSelect = document.getElementById('edit_program_select');
    var designationInput = document.getElementById('edit_designation_input');
    if (editSelect) {
        editSelect.value = chair.program;
        var found = departments.find(function(d) { return d.id == chair.program; });
        designationInput.value = found ? found.designation : '';
    }
    
    // Display current profile picture
    const profileDisplay = document.getElementById('current_profile_display');
    if (chair.profile_picture) {
        profileDisplay.innerHTML = `
            <div class="flex items-center gap-2">
                <img src="chair-uploads/${chair.profile_picture}" alt="Current Profile" class="h-12 w-12 rounded-full object-cover">
                <span class="text-sm text-gray-600">Current profile picture</span>
            </div>
        `;
    } else {
        profileDisplay.innerHTML = '<span class="text-sm text-gray-500">No profile picture</span>';
    }
    
    document.getElementById('editModal').style.display = 'block';
}

// Toggle status function
function toggleStatus(chairId, currentStatus) {
    const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
    document.getElementById('status_chair_id').value = chairId;
    document.getElementById('status_new_status').value = newStatus;
    document.getElementById('statusForm').submit();
}

// Close modals when clicking outside
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        });
    }
});

const departments = <?php echo json_encode($departments); ?>;

function initializeDropdowns() {
    // For Create Modal
    var select = document.getElementById('program_select');
    if (select && select.options.length === 1) {
        departments.forEach(function(dept) {
            var opt = document.createElement('option');
            opt.value = dept.id;
            opt.textContent = dept.label;
            select.appendChild(opt);
        });
    }
    
    // For Edit Modal
    var editSelect = document.getElementById('edit_program_select');
    if (editSelect && editSelect.options.length === 1) {
        departments.forEach(function(dept) {
            var opt = document.createElement('option');
            opt.value = dept.id;
            opt.textContent = dept.label;
            editSelect.appendChild(opt);
        });
    }
}

function updateDesignation() {
    var select = document.getElementById('program_select');
    var designationInput = document.getElementById('designation_input');
    var deptId = select.value;
    var found = departments.find(function(d) { return d.id == deptId; });
    designationInput.value = found ? found.designation : '';
}

function updateEditDesignation() {
    var select = document.getElementById('edit_program_select');
    var designationInput = document.getElementById('edit_designation_input');
    var deptId = select.value;
    var found = departments.find(function(d) { return d.id == deptId; });
    designationInput.value = found ? found.designation : '';
}

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.querySelector('i').classList.remove('fa-eye');
        btn.querySelector('i').classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        btn.querySelector('i').classList.remove('fa-eye-slash');
        btn.querySelector('i').classList.add('fa-eye');
    }
}

// Password validation for Create Program Chair
const createChairForm = document.querySelector('#createModal form');
if (createChairForm) {
    createChairForm.addEventListener('submit', function(e) {
        const password = document.getElementById('create_chair_password').value;
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{8,}$/;
        if (!pattern.test(password)) {
            alert('Password must be at least 8 characters and include uppercase, lowercase, number, and special character.');
            e.preventDefault();
            return false;
        }
    });
}

// Password validation for Edit Program Chair
const editChairForm = document.querySelector('#editModal form');
if (editChairForm) {
    editChairForm.addEventListener('submit', function(e) {
        const password = document.getElementById('edit_chair_password').value;
        if (password) {
            const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{8,}$/;
            if (!pattern.test(password)) {
                alert('Password must be at least 8 characters and include uppercase, lowercase, number, and special character.');
                e.preventDefault();
                return false;
            }
        }
    });
}

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

<?php include 'includes/footer.php'; ?>
