<?php
session_start();
include 'includes/header.php';
require_once '../admin/config/database.php';

// Check session validity
if (!isset($_SESSION['chair_name'])) {
    header('Location: ../login.php');
    exit();
}

$chair_name = $_SESSION['chair_name'] ?? 'Program Chair';
$profile_photo = $_SESSION['profile_photo_path'] ?? '';
if (!empty($profile_photo) && strpos($profile_photo, 'ui-avatars.com') === false) {
    $profile_photo_url = '../admin/chair-uploads/' . htmlspecialchars($profile_photo);
} else {
    $profile_photo_url = 'https://ui-avatars.com/api/?name=' . urlencode($chair_name) . '&background=0D8ABC&color=fff';
}

// Handle search and filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
$where = [];
$params = [];

if ($search) {
    $where[] = "(title LIKE :search OR target_alumni LIKE :search OR survey_type LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($filter && in_array($filter, ['published', 'draft'])) {
    $where[] = "status = :filter";
    $params[':filter'] = $filter;
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Fetch survey stats
$total = $published = $draft = 0;
$stats_sql = "SELECT status, COUNT(*) as count FROM survey GROUP BY status";
$stats_stmt = $conn->query($stats_sql);
if ($stats_stmt) {
    while ($row = $stats_stmt->fetch(PDO::FETCH_ASSOC)) {
        $total += $row['count'];
        if ($row['status'] === 'published') $published = $row['count'];
        if ($row['status'] === 'draft') $draft = $row['count'];
    }
}

// Pagination logic
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count total filtered surveys
$count_sql = "SELECT COUNT(*) FROM survey $where_sql";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_surveys = $count_stmt->fetchColumn();
$total_pages = ceil($total_surveys / $per_page);

// Fetch paginated surveys
$survey_sql = "SELECT * FROM survey $where_sql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$survey_stmt = $conn->prepare($survey_sql);
foreach ($params as $k => $v) { 
    $survey_stmt->bindValue($k, $v); 
}
$survey_stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$survey_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$survey_stmt->execute();
$surveys = $survey_stmt->fetchAll(PDO::FETCH_ASSOC);

function render_survey_table($surveys) {
    ob_start();
    ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Alumni</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($surveys)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500">No surveys found.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($surveys as $survey): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200" data-survey='<?php echo htmlspecialchars(json_encode($survey), ENT_QUOTES, "UTF-8"); ?>'>
                            <td class="px-6 py-4">
                                <div class="max-w-xs">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($survey['title']); ?></div>
                                    <?php if ($survey['description']): ?>
                                        <div class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars(substr($survey['description'], 0, 60)) . '...'; ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($survey['target_alumni']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($survey['survey_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status = strtolower($survey['status']);
                                $badgeClass = '';
                                $text = ucfirst($status);
                                if ($status === 'published') {
                                    $badgeClass = 'bg-green-100 text-green-800';
                                } elseif ($status === 'draft') {
                                    $badgeClass = 'bg-yellow-100 text-yellow-800';
                                } else {
                                    $badgeClass = 'bg-gray-100 text-gray-800';
                                }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $badgeClass; ?>">
                                    <?php echo $text; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($survey['created_by']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($survey['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors duration-200 preview-btn" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="edit-survey.php?id=<?php echo $survey['id']; ?>" class="text-yellow-600 hover:text-yellow-900 p-2 rounded-lg hover:bg-yellow-50 transition-colors duration-200" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200 delete-btn" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// AJAX: Only return the table container if XHR
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    ?>
    <div id="survey-table-container">
        <?php echo render_survey_table($surveys); ?>
        <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-center space-x-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="#" onclick="changePage(<?php echo $i; ?>); return false;" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
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
    <title>Manage Surveys - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
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
            <!-- Breadcrumb Navigation (always render) -->
            <div class="mb-6">
                <nav class="flex items-center space-x-2 text-sm text-gray-600 bg-white/80 backdrop-blur-sm rounded-xl px-4 py-3 shadow-sm border border-blue-100">
                    <span class="flex items-center space-x-1 text-blue-600 font-medium">
                        <i class="fas fa-home text-xs"></i>
                        <a href="index.php" class="hover:underline">Home</a>
                    </span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="flex items-center space-x-1 text-gray-800 font-medium">
                        <i class="fas fa-poll text-xs"></i>
                        <span>Manage Surveys</span>
                    </span>
                </nav>
            </div>
            <!-- Controls Section (AJAX search/filter) -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 mb-8">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                        <div class="relative flex-1 max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="searchbar" class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                   placeholder="Search surveys..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="flex-1 max-w-xs">
                            <label for="filterDropdown" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="filterDropdown" class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" >
                                <option value="" <?php if ($filter == '') echo 'selected'; ?>>All</option>
                                <option value="published" <?php if ($filter == 'published') echo 'selected'; ?>>Published</option>
                                <option value="draft" <?php if ($filter == 'draft') echo 'selected'; ?>>Draft</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table Container -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100">
                <div id="survey-table-container">
                    <?php echo render_survey_table($surveys); ?>
                </div>
                <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-center space-x-2">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="#" onclick="changePage(<?php echo $i; ?>); return false;" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                <h3 class="text-lg font-semibold text-gray-900">Survey Preview</h3>
                <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modalBody" class="p-6"></div>
        </div>
    </div>

    <script>
        // AJAX Search and Filter
        const searchbar = document.getElementById('searchbar');
        const filterDropdown = document.getElementById('filterDropdown');
        let timeout = null;
        let currentFilter = '<?php echo htmlspecialchars($filter); ?>';
        let currentPage = <?php echo (int)$page; ?>;

        function reloadTable(page = 1, filter = currentFilter) {
            const search = encodeURIComponent(searchbar.value);
            const params = [];
            if (search) params.push('search=' + search);
            if (filter) params.push('filter=' + encodeURIComponent(filter));
            if (page > 1) params.push('page=' + page);
            const url = window.location.pathname + (params.length ? '?' + params.join('&') : '');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    // Only replace the table container, not the whole main content
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.getElementById('survey-table-container');
                    if (newTable) {
                        document.getElementById('survey-table-container').innerHTML = newTable.innerHTML;
                    } else {
                        // fallback: if response is just the table container
                        document.getElementById('survey-table-container').innerHTML = html;
                    }
                    reapplyListeners();
                });
            currentPage = page;
            currentFilter = filter;
        }

        searchbar.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => reloadTable(1, filterDropdown.value), 400);
        });

        filterDropdown.addEventListener('change', function() {
            reloadTable(1, filterDropdown.value);
        });

        function changePage(page) {
            reloadTable(page, filterDropdown.value);
        }

        function reapplyListeners() {
            // Reapply pagination and preview listeners if needed
            const pageLinks = document.querySelectorAll('#survey-table-container a[onclick^="changePage"]');
            pageLinks.forEach(link => {
                link.onclick = function(e) { e.preventDefault(); changePage(parseInt(this.textContent)); };
            });
            // Add preview modal logic if needed
        }

        // Initial listeners
        reapplyListeners();
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>