<?php
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/navbar.php';

// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Manage Surveys', 'url' => 'manage-survey.php', 'active' => true]
];

// manage-survey.php
// Survey management UI and logic for admin panel
session_start();
// require database connection if needed
// require_once 'config/database.php';

// Example: Fetch surveys from DB (replace with real DB logic)
$surveys = [
    [
        'id' => 1,
        'title' => 'Tracer Study 2024',
        'description' => 'A survey to track the career progress of 2024 graduates.',
        'created_at' => '2024-05-01',
        'status' => 'Active',
        'responses' => 120
    ],
    [
        'id' => 2,
        'title' => 'Alumni Feedback',
        'description' => 'Collecting feedback from all alumni.',
        'created_at' => '2024-04-15',
        'status' => 'Inactive',
        'responses' => 45
    ],
    [
        'id' => 3,
        'title' => 'Career Development Needs',
        'description' => 'Help us understand your career development needs.',
        'created_at' => '2024-03-10',
        'status' => 'Expired',
        'responses' => 200
    ],
];
// Handle filter/search (add real logic as needed)
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
// ...
?>
<main class="main-content">
<div class="max-w-6xl mx-auto p-6 mt-10 bg-white rounded-2xl shadow-xl">
    <?php include 'includes/breadcrumb.php'; ?>
    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-blue-700 mb-1 flex items-center gap-2"><i class="fas fa-tasks"></i> Manage Surveys</h1>
            <p class="text-gray-600">View, edit, and manage all alumni surveys.</p>
        </div>
        <form method="GET" class="flex flex-col md:flex-row gap-2 md:items-end">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title or tag" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Expired">Expired</option>
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold ml-2"><i class="fas fa-search"></i> Filter</button>
            <a href="manage-survey.php" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-semibold ml-2"><i class="fas fa-times"></i> Clear</a>
        </form>
    </div>
    <div class="table-container">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-extrabold text-blue-700 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold text-blue-700 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold text-blue-700 uppercase tracking-wider">Created Date</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold text-blue-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold text-blue-700 uppercase tracking-wider">Responses</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold text-blue-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($surveys as $survey): ?>
                <tr class="hover:bg-blue-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($survey['title']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars(substr($survey['description'],0,60)); ?>...</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($survey['created_at']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge status-<?php echo strtolower($survey['status']); ?>"><?php echo $survey['status']; ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-blue-700 font-bold"><?php echo $survey['responses']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap flex gap-1">
                        <button class="action-btn view" title="View Responses"><i class="fas fa-chart-bar"></i></button>
                        <?php if ($survey['status'] === 'Inactive'): ?>
                        <button class="action-btn edit" title="Edit Survey"><i class="fas fa-edit"></i></button>
                        <?php endif; ?>
                        <button class="action-btn toggle" title="Activate/Deactivate"><i class="fas fa-toggle-on"></i></button>
                        <button class="action-btn duplicate" title="Duplicate"><i class="fas fa-copy"></i></button>
                        <button class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($surveys)): ?>
                <tr><td colspan="6" class="text-center text-gray-400 py-8"><i class="fas fa-tasks text-3xl mb-2"></i><br>No surveys found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="flex justify-between items-center mt-8">
        <div>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-file-csv mr-1"></i> Export CSV</button>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold ml-2"><i class="fas fa-file-excel mr-1"></i> Export Excel</button>
        </div>
        <div class="pagination">
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
        </div>
    </div>
</div>
</main>
<script>
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