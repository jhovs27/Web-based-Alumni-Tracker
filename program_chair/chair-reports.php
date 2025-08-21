<?php
require_once 'includes/session_config.php';
if (!isChairSessionValid()) {
    header('Location: ../login.php');
    exit();
}

include '../admin/config/database.php';

$chair_program = $_SESSION['chair_program'] ?? '';

// 1. Alumni by Employment Status
$employment_status_data = [];
try {
    $stmt = $conn->prepare("SELECT employment_status, COUNT(*) as count FROM alumni WHERE program = ? GROUP BY employment_status");
    $stmt->execute([$chair_program]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['employment_status'] ?: 'Unknown';
        $employment_status_data[$status] = (int)$row['count'];
    }
} catch (PDOException $e) {}

// 2. Alumni by Graduation Year
$graduates_by_year = [];
try {
    $stmt = $conn->prepare("SELECT year_graduated, COUNT(*) as count FROM alumni WHERE program = ? AND year_graduated IS NOT NULL GROUP BY year_graduated ORDER BY year_graduated ASC");
    $stmt->execute([$chair_program]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $year = $row['year_graduated'] ?: 'Unknown';
        $graduates_by_year[$year] = (int)$row['count'];
    }
} catch (PDOException $e) {}

// 3. Alumni by Program (for this chair, only their program, but keep structure for future multi-program)
$alumni_by_program = [];
try {
    $stmt = $conn->prepare("SELECT d.DepartmentName as program, COUNT(a.id) as count FROM alumni a LEFT JOIN department d ON a.program = d.id WHERE a.program = ? GROUP BY a.program");
    $stmt->execute([$chair_program]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $program = $row['program'] ?: 'Unknown';
        $alumni_by_program[$program] = (int)$row['count'];
    }
} catch (PDOException $e) {}

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Reports', 'icon' => 'fa-chart-bar'],
];

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                        <?php if ($index > 0): ?>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        <?php endif; ?>
                        <span class="flex items-center space-x-1 text-gray-800 font-medium">
                            <i class="fas <?php echo htmlspecialchars($breadcrumb['icon']); ?> text-xs"></i>
                            <span><?php echo htmlspecialchars($breadcrumb['label']); ?></span>
                        </span>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Reports Header -->
            <div class="flex items-center mb-8">
                <div class="p-3 rounded-full bg-gradient-to-br from-violet-500 to-violet-600 text-white shadow-lg mr-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 0 1 4-4h6m-6 4V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17v-2a4 4 0 0 1 4-4h6" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Reports & Analytics</h1>
            </div>

            <!-- Reports Content with Real Data and Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Alumni Distribution by Employment Status (Pie) -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-8 border border-violet-100 flex flex-col items-center justify-center min-h-[320px]">
                    <i class="fas fa-chart-pie text-violet-500 text-4xl mb-4"></i>
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Alumni by Employment Status</h2>
                    <canvas id="employmentStatusChart" width="220" height="180"></canvas>
                </div>
                <!-- Graduate Trends by Year (Bar) -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-8 border border-blue-100 flex flex-col items-center justify-center min-h-[320px]">
                    <i class="fas fa-user-graduate text-blue-500 text-4xl mb-4"></i>
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Graduates Per Year</h2>
                    <canvas id="graduatesByYearChart" width="220" height="180"></canvas>
                </div>
                <!-- Alumni by Program (Doughnut) -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-8 border border-green-100 flex flex-col items-center justify-center min-h-[320px]">
                    <i class="fas fa-building-columns text-green-500 text-4xl mb-4"></i>
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Alumni by Program</h2>
                    <canvas id="alumniByProgramChart" width="220" height="180"></canvas>
                </div>
            </div>
        </div>
    </main>
    <script>
        // Data from PHP
        const employmentStatusData = <?php echo json_encode($employment_status_data); ?>;
        const graduatesByYearData = <?php echo json_encode($graduates_by_year); ?>;
        const alumniByProgramData = <?php echo json_encode($alumni_by_program); ?>;

        // Alumni by Employment Status (Pie)
        const employmentStatusCtx = document.getElementById('employmentStatusChart').getContext('2d');
        new Chart(employmentStatusCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(employmentStatusData),
                datasets: [{
                    data: Object.values(employmentStatusData),
                    backgroundColor: [
                        '#7c3aed', '#6366f1', '#06b6d4', '#22d3ee', '#f59e42', '#f43f5e', '#10b981', '#a3e635', '#facc15', '#64748b'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });

        // Graduates Per Year (Bar)
        const graduatesByYearCtx = document.getElementById('graduatesByYearChart').getContext('2d');
        new Chart(graduatesByYearCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(graduatesByYearData),
                datasets: [{
                    label: 'Graduates',
                    data: Object.values(graduatesByYearData),
                    backgroundColor: '#6366f1',
                    borderRadius: 8,
                }]
            },
            options: {
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#e0e7ef' } }
                }
            }
        });

        // Alumni by Program (Doughnut)
        const alumniByProgramCtx = document.getElementById('alumniByProgramChart').getContext('2d');
        new Chart(alumniByProgramCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(alumniByProgramData),
                datasets: [{
                    data: Object.values(alumniByProgramData),
                    backgroundColor: [
                        '#10b981', '#22d3ee', '#6366f1', '#f59e42', '#f43f5e', '#a3e635', '#facc15', '#64748b', '#7c3aed', '#06b6d4'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>
