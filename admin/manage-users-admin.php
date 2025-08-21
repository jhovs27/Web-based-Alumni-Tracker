<?php
require_once 'config/database.php';

// Handle admin soft delete (set status to Inactive)
if (isset($_GET['delete_admin'])) {
    $id = intval($_GET['delete_admin']);
    $success_message = '';
    $error_message = '';
    try {
        // Prevent deleting self (optional, for safety)
        if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $id) {
            $error_message = 'You cannot delete your own account.';
        } else {
            $stmt = $conn->prepare("UPDATE admins SET status = 'Inactive' WHERE id = ?");
            $stmt->execute([$id]);
            $success_message = 'Admin user set to Inactive.';
        }
    } catch (Exception $e) {
        $error_message = 'Error setting admin user to Inactive.';
    }
    // Redirect to same page without delete_admin param, but with message
    $params = $_GET;
    unset($params['delete_admin']);
    if ($success_message) $params['success_message'] = urlencode($success_message);
    if ($error_message) $params['error_message'] = urlencode($error_message);
    $redirect = basename(__FILE__) . '?' . http_build_query($params);
    header("Location: $redirect");
    exit;
}

// Handle admin restore (set status to Active)
if (isset($_GET['restore_admin'])) {
    $id = intval($_GET['restore_admin']);
    $success_message = '';
    $error_message = '';
    try {
        $stmt = $conn->prepare("UPDATE admins SET status = 'Active' WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = 'Admin user restored to Active.';
    } catch (Exception $e) {
        $error_message = 'Error restoring admin user.';
    }
    $params = $_GET;
    unset($params['restore_admin']);
    if ($success_message) $params['success_message'] = urlencode($success_message);
    if ($error_message) $params['error_message'] = urlencode($error_message);
    $redirect = basename(__FILE__) . '?' . http_build_query($params);
    header("Location: $redirect");
    exit;
}

// Handle Add Admin (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $success_message = '';
    $error_message = '';

    if ($name === '' || $email === '' || $password === '') {
        $error_message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email address.';
    } else {
        // Check for unique email
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error_message = 'Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $conn->prepare("INSERT INTO admins (name, email, password, status, created_at) VALUES (?, ?, ?, 'Active', NOW())");
                $stmt->execute([$name, $email, $hashed_password]);
                $success_message = 'Admin user added successfully.';
            } catch (Exception $e) {
                $error_message = 'Error adding admin user.';
            }
        }
    }
    // Redirect to same page with message
    $params = $_GET;
    if ($success_message) $params['success_message'] = urlencode($success_message);
    if ($error_message) $params['error_message'] = urlencode($error_message);
    $redirect = basename(__FILE__) . '?' . http_build_query($params);
    header("Location: $redirect");
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
include 'includes/breadcrumb.php';

// --- Handle GET params for search, filter, pagination ---
$search = trim($_GET['search'] ?? '');
$per_page = intval($_GET['per_page'] ?? 10);
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Count total for pagination
$count_sql = "SELECT COUNT(*) FROM admins $where_sql";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));

// Fetch paginated admins (all statuses)
$sql = "SELECT id, name, email, status, created_at FROM admins $where_sql ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle toggle active/inactive
if (isset($_GET['toggle_active'])) {
    $id = intval($_GET['toggle_active']);
    $success_message = '';
    $error_message = '';
    try {
        // Prevent toggling self (optional)
        if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $id) {
            $error_message = 'You cannot change your own status.';
        } else {
            $stmt = $conn->prepare("SELECT status FROM admins WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetchColumn();
            if ($current === 'Active') {
                $stmt = $conn->prepare("UPDATE admins SET status = 'Inactive' WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = 'Admin user set to Inactive.';
            } elseif ($current === 'Inactive') {
                $stmt = $conn->prepare("UPDATE admins SET status = 'Active' WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = 'Admin user set to Active.';
            }
        }
    } catch (Exception $e) {
        $error_message = 'Error updating admin user status.';
    }
    $params = $_GET;
    unset($params['toggle_active']);
    if ($success_message) $params['success_message'] = urlencode($success_message);
    if ($error_message) $params['error_message'] = urlencode($error_message);
    $redirect = basename(__FILE__) . '?' . http_build_query($params);
    header("Location: $redirect");
    exit;
}

// At the top of the file, add logic to display success or error messages from GET params
if (isset($_GET['success_message'])) $success_message = urldecode($_GET['success_message']);
if (isset($_GET['error_message'])) $error_message = urldecode($_GET['error_message']);
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

@media (max-width: 900px) {
    .main-content .p-8 { padding: 1.5rem !important; }
}
@media (max-width: 640px) {
    .main-content .p-8 { padding: 0.5rem !important; }
    .main-content h1 { font-size: 1.3rem !important; }
    .main-content .bg-white { padding: 0.5rem !important; }
    .main-content table th, .main-content table td { padding: 0.5rem !important; font-size: 0.85rem !important; }
    .main-content .flex { flex-direction: column !important; gap: 0.5rem !important; }
    .main-content .justify-between { flex-direction: column !important; align-items: flex-start !important; }
    .main-content .max-w-5xl { max-width: 100vw !important; }
    .main-content .rounded-xl { border-radius: 0.7rem !important; }
    .main-content .shadow-lg, .main-content .shadow-xl { box-shadow: 0 2px 8px rgba(59,130,246,0.08) !important; }
    .main-content .px-5, .main-content .px-6 { padding-left: 0.7rem !important; padding-right: 0.7rem !important; }
    .main-content .py-2, .main-content .py-3, .main-content .py-4 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
    .main-content .rounded-lg { border-radius: 0.5rem !important; }
    .main-content .w-full { width: 100% !important; }
    .main-content .max-w-xs { max-width: 100% !important; }
    .main-content .searchbar-input { font-size: 0.95rem !important; }
}
</style>

<div class="main-content min-h-screen flex flex-col bg-gradient-to-br from-blue-50 via-white to-indigo-50 pt-12">
    <div class="p-8 max-w-5xl mx-auto w-full">
        <!-- Breadcrumb -->
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Manage Users', 'url' => ''],
            ['title' => 'Admin Users', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
        
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Manage Admin Users</h1>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold shadow transition" onclick="openAdminModal()">
                <i class="fas fa-user-plus mr-2"></i> Add Admin
            </button>
        </div>

        <!-- Controls -->
        <div class="flex flex-wrap gap-4 items-center mb-6">
            <div>
                <label class="text-sm font-semibold text-gray-600 mr-2">Show</label>
                <select id="perPageSelect" class="border rounded px-2 py-1">
                    <?php foreach ([5,10,25,50] as $n): ?>
                        <option value="<?php echo $n; ?>" <?php if ($per_page == $n) echo 'selected'; ?>><?php echo $n; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="text-sm text-gray-600 ml-1">entries</span>
            </div>
            <!-- Sort by ID Dropdown -->
            <div>
                <label class="text-sm font-semibold text-gray-600 mr-2">Sort by ID</label>
                <select id="sortIdSelect" class="border rounded px-2 py-1">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>
            <!-- Status Filter Dropdown -->
            <div>
                <label class="text-sm font-semibold text-gray-600 mr-2">Status</label>
                <select id="statusFilterSelect" class="border rounded px-2 py-1">
                    <option value="">All</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="flex-1"></div>
            <div class="relative w-full max-w-xs">
                <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name or email..." class="searchbar-input block w-full pl-10 pr-4 py-2 rounded-full border border-gray-300 shadow focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition text-gray-700 bg-white" style="min-width:200px;">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"><i class="fas fa-search"></i></span>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 overflow-x-auto table-container">
            <div class="loading-overlay" id="loadingOverlay" style="display: none;">
                <div class="loading-spinner"></div>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminsTableBody" class="bg-white divide-y divide-gray-200">
                    <?php if (empty($admins)): ?>
                        <tr><td colspan="6" class="px-6 py-4 text-center text-gray-400">No admin users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($admin['id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($admin['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($admin['status'] === 'Inactive'): ?>
                                    <span class="inline-block px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold uppercase">Inactive</span>
                                <?php else: ?>
                                    <span class="inline-block px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold uppercase">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($admin['created_at']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center flex gap-2 justify-center items-center">
                                <button class="text-blue-600 hover:text-blue-900 font-semibold mr-1 p-2 rounded-full bg-blue-50 hover:bg-blue-100 transition" title="Edit" onclick="openAdminModal(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars(addslashes($admin['name'])); ?>', '<?php echo htmlspecialchars(addslashes($admin['email'])); ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleAdminStatus(<?php echo $admin['id']; ?>)"
                                       class="font-semibold p-2 rounded-full transition <?php echo $admin['status']==='Active' ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 'bg-green-50 text-green-700 hover:bg-green-100'; ?>"
                                       title="<?php echo $admin['status']==='Active' ? 'Set Inactive' : 'Set Active'; ?>">
                                    <?php if ($admin['status']==='Active'): ?>
                                        <i class="fas fa-user-slash"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user-check"></i>
                                    <?php endif; ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-2">
                <div id="paginationInfo" class="text-sm text-gray-600 mb-2 sm:mb-0">
                    Showing <?php echo min($total, $offset+1); ?> to <?php echo min($offset+$per_page, $total); ?> of <?php echo $total; ?> entries
                </div>
                <div id="paginationControls" class="flex gap-1 flex-wrap">
                    <?php for ($i=1; $i<=$total_pages; $i++): ?>
                        <a href="#" onclick="loadPage(<?php echo $i; ?>)" class="px-3 py-1 rounded <?php echo $i==$page ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-blue-100'; ?> transition text-sm">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Admin -->
    <div id="adminModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl" onclick="closeAdminModal()"><i class="fas fa-times"></i></button>
            <h2 class="text-2xl font-bold mb-4" id="adminModalTitle">Add Admin</h2>
            <form id="adminForm" method="post" autocomplete="off">
                <input type="hidden" name="add_admin" value="1">
                <input type="hidden" id="adminId" name="admin_id" value="">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-1" for="adminName">Name</label>
                    <input type="text" id="adminName" name="name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-1" for="adminEmail">Email</label>
                    <input type="email" id="adminEmail" name="email" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                </div>
                <div class="mb-6" id="adminPasswordField">
                    <label class="block text-gray-700 font-semibold mb-1" for="adminPassword">Password</label>
                    <input type="password" id="adminPassword" name="password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold w-full transition">Save</button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation -->
    <div id="deleteAdminModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-sm relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl" onclick="closeDeleteAdminModal()"><i class="fas fa-times"></i></button>
            <h2 class="text-xl font-bold mb-4 text-red-600">Delete Admin</h2>
            <p class="mb-6 text-gray-700">Are you sure you want to delete this admin user?</p>
            <div class="flex justify-end gap-3">
                <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold" onclick="closeDeleteAdminModal()">Cancel</button>
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold" id="deleteAdminButton">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentPage = 1;
let currentSearch = '';
let currentPerPage = 10;
let searchTimeout;

// Initialize AJAX functionality
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
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
    // Per page dropdown
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            currentPerPage = parseInt(this.value);
            currentPage = 1;
            loadData();
        });
    }
    // Sort by ID dropdown
    const sortIdSelect = document.getElementById('sortIdSelect');
    if (sortIdSelect) {
        sortIdSelect.addEventListener('change', function() {
            currentPage = 1;
            loadData();
        });
    }
    // Status filter dropdown
    const statusFilterSelect = document.getElementById('statusFilterSelect');
    if (statusFilterSelect) {
        statusFilterSelect.addEventListener('change', function() {
            currentPage = 1;
            loadData();
        });
    }
}

function loadData() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    }
    const sortId = document.getElementById('sortIdSelect') ? document.getElementById('sortIdSelect').value : 'desc';
    const status = document.getElementById('statusFilterSelect') ? document.getElementById('statusFilterSelect').value : '';
    const params = new URLSearchParams({
        search: currentSearch,
        per_page: currentPerPage,
        page: currentPage,
        sort_id: sortId,
        status: status
    });
    fetch(`admins_ajax.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update table body
                const tableBody = document.getElementById('adminsTableBody');
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

function openAdminModal(id = '', name = '', email = '') {
    document.getElementById('adminModal').classList.remove('hidden');
    document.getElementById('adminId').value = id;
    document.getElementById('adminName').value = name;
    document.getElementById('adminEmail').value = email;
    document.getElementById('adminPassword').value = '';
    document.getElementById('adminModalTitle').textContent = id ? 'Edit Admin' : 'Add Admin';
    document.getElementById('adminPasswordField').style.display = id ? 'none' : 'block';
}

function closeAdminModal() {
    document.getElementById('adminModal').classList.add('hidden');
}

function toggleAdminStatus(id) {
    if (confirm('Are you sure you want to change this admin\'s status?')) {
        window.location = '?toggle_active=' + id + '&' + new URLSearchParams(window.location.search).toString().replace(/(&)?toggle_active=\d+/, '');
    }
}

function confirmDeleteAdmin(id) {
    document.getElementById('deleteAdminModal').classList.remove('hidden');
    document.getElementById('deleteAdminButton').onclick = function() {
        window.location = '?delete_admin=' + id + '&' + new URLSearchParams(window.location.search).toString().replace(/(&)?delete_admin=\d+/, '');
    };
}

function closeDeleteAdminModal() {
    document.getElementById('deleteAdminModal').classList.add('hidden');
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
