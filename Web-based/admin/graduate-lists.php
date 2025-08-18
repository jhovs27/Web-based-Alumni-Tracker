<?php
session_start();
require_once 'config/database.php';

// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Graduates List', 'url' => 'graduate-lists.php', 'active' => true]
];

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';

// Base query
$query = "SELECT s.StudentNo, s.LastName, s.FirstName, s.MiddleName, s.Sex, c.accro as Course, s.ContactNo, m.SchoolYear, m.Semester
          FROM students s 
          LEFT JOIN course c ON s.course = c.id 
          LEFT JOIN listgradsub l ON s.StudentNo = l.StudentNo 
          LEFT JOIN listgradmain m ON l.MainID = m.id 
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

// Get course list for filter
try {
    $courses_query = "SELECT id, accro FROM course ORDER BY accro";
    $courses_stmt = $conn->query($courses_query);
    $courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error getting courses: " . $e->getMessage());
}

// Execute main query
try {
    $stmt = $conn->prepare($query);
    // Bind parameters
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graduates List - Alumni System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
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
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100 min-h-screen">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/breadcrumb.php'; ?>
        
    <!-- Main Content -->
    <div class="main-content min-h-screen pt-16 lg:ml-64 transition-all duration-300">
        <div class="p-6">
            <!-- Breadcrumb -->
            <?php
            $breadcrumbs = [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Graduate Lists', 'url' => '']
            ];
            renderBreadcrumb($breadcrumbs);
            ?>
                        
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Graduates List</h2>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>"
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Search by Student No, Name, Contact, or School Year...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="w-48">
                        <select id="courseSelect" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo $course_filter == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['accro']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label for="entriesSelect" class="text-sm text-gray-700 whitespace-nowrap">Show</label>
                        <select id="entriesSelect" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-700 whitespace-nowrap">entries</span>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="relative">
                    <!-- Loading overlay -->
                    <div id="loadingOverlay" class="loading-overlay">
                        <div class="loading-spinner"></div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sex</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School Year</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                                <?php if (count($result) > 0): ?>
                                    <?php foreach ($result as $row): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['StudentNo']); ?></td>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Sex']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Course']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['ContactNo']); ?></td>
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
                                                if ($row['Semester'] == 1) echo 'FIRST SEMESTER';
                                                elseif ($row['Semester'] == 2) echo 'SECOND SEMESTER';
                                                else echo htmlspecialchars($row['Semester']);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <i class="fas fa-search text-4xl mb-3"></i>
                                                <p class="text-lg font-medium">No records found</p>
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
            <?php if ($total_pages > 1): ?>
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <div class="flex justify-between items-center">
                    <div id="paginationInfo" class="text-sm text-gray-700">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
                    </div>
                    <div id="paginationControls" class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <button onclick="loadPage(<?php echo $page - 1; ?>)" class="px-3 py-1 bg-white border rounded-md hover:bg-gray-50 transition-colors duration-200">
                                Previous
                            </button>
                        <?php endif; ?>
                                                
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <button onclick="loadPage(<?php echo $i; ?>)" class="px-3 py-1 <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'; ?> border rounded-md transition-colors duration-200">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>
                                                
                        <?php if ($page < $total_pages): ?>
                            <button onclick="loadPage(<?php echo $page + 1; ?>)" class="px-3 py-1 bg-white border rounded-md hover:bg-gray-50 transition-colors duration-200">
                                Next
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-96 transform transition-all duration-200">
            <div class="text-center mb-4">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
                <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
            </div>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete the record for student <span id="studentName" class="font-medium"></span>?
                This action cannot be undone.
            </p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()"
                         class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    Cancel
                </button>
                <button onclick="deleteStudent()"
                         class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Global variables
        let currentSearch = '';
        let currentCourse = '';
        let currentEntries = 10;
        let currentPage = 1;
        let searchTimeout;

        // AJAX function to load table data
        function loadTableData(search = currentSearch, course = currentCourse, entries = currentEntries, page = currentPage) {
            // Show loading overlay
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.add('show');
            
            // Create form data
            const formData = new FormData();
            formData.append('search', search);
            formData.append('course', course);
            formData.append('entries', entries);
            formData.append('page', page);
            
            // Make AJAX request
            fetch('graduates_ajax.php', {
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
                    currentCourse = course;
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
                loadTableData(searchValue, currentCourse, currentEntries, 1);
            }, 500);
        });

        // Course filter change
        document.getElementById('courseSelect').addEventListener('change', function() {
            loadTableData(currentSearch, this.value, currentEntries, 1);
        });

        // Entries per page change
        document.getElementById('entriesSelect').addEventListener('change', function() {
            const entriesValue = parseInt(this.value);
            loadTableData(currentSearch, currentCourse, entriesValue, 1);
        });

        // Pagination
        function loadPage(page) {
            loadTableData(currentSearch, currentCourse, currentEntries, page);
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

        function viewDetails(studentNo) {
            console.log('View details for:', studentNo);
        }

        function editGraduate(studentNo) {
            console.log('Edit graduate:', studentNo);
        }

        let studentToDelete = null;

        function confirmDelete(studentNo, studentName) {
            studentToDelete = studentNo;
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
            studentToDelete = null;
        }

        function deleteStudent() {
            if (studentToDelete) {
                console.log('Deleting student:', studentToDelete);
                closeDeleteModal();
            }
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
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
        }, 5 * 60 * 1000);
    </script>
    <footer class="bg-gray-800 text-white p-4 text-center">
        <p>&copy; <?php echo date('Y'); ?> Alumni System. All rights reserved.</p>
    </footer>
</body>
</html>
