<?php
session_start();
require_once '../admin/config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';

// Set current page for sidebar highlighting
$current_page = 'manage-surveys';

// Check if user is logged in as program chair
if (!isset($_SESSION['is_chair']) || !$_SESSION['is_chair']) {
    header('Location: ../login.php');
    exit();
}

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'],
    ['label' => 'Manage Surveys', 'url' => 'manage-surveys.php', 'icon' => 'fa-poll'],
    ['label' => 'Edit Survey', 'icon' => 'fa-edit'],
];

// Get survey ID
$survey_id = $_GET['id'] ?? null;
if (!$survey_id) {
    echo '<div class="p-8"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">No survey ID provided.</div></div>';
    include 'includes/footer.php';
    exit;
}

// Fetch school year ranges for Target Alumni dropdown
$school_years = [];
$sql = "SELECT CONCAT(SchoolYear, '-', SchoolYear + 1) AS school_year_range FROM listgradmain GROUP BY SchoolYear ORDER BY SchoolYear DESC";
$stmt = $conn->query($sql);
if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $school_years[] = $row['school_year_range'];
    }
}

// Fetch survey data
$sql = "SELECT * FROM survey WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $survey_id]);
$survey = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$survey) {
    echo '<div class="p-8"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Survey not found.</div></div>';
    include 'includes/footer.php';
    exit;
}

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['survey_title'] ?? '');
    $description = trim($_POST['survey_description'] ?? '');
    $target_group = $_POST['target_group'] ?? '';
    $survey_type = $_POST['survey_type'] ?? '';
    $questions = $_POST['questions'] ?? [];
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $action = $_POST['action'] ?? 'draft';
    $status = ($action === 'publish') ? 'published' : 'draft';
    $created_by = $survey['created_by'];

    $errors = [];
    if (empty($title)) {
        $errors[] = "Survey title is required";
    }
    if (empty($questions)) {
        $errors[] = "At least one question is required";
    }

    if (empty($errors)) {
        $questions_json = json_encode($questions);
        $sql = "UPDATE survey SET title=:title, description=:description, target_alumni=:target_alumni, survey_type=:survey_type, questions=:questions, start_date=:start_date, end_date=:end_date, anonymous=:anonymous, status=:status WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':target_alumni' => $target_group,
            ':survey_type' => $survey_type,
            ':questions' => $questions_json,
            ':start_date' => $start_date ?: null,
            ':end_date' => $end_date ?: null,
            ':anonymous' => $anonymous,
            ':status' => $status,
            ':id' => $survey_id
        ]);

        $success_message = ($status === 'published') ? 'Survey updated and published!' : 'Survey updated and saved as draft!';
        
        // Refresh survey data
        $stmt = $conn->prepare("SELECT * FROM survey WHERE id = :id");
        $stmt->execute([':id' => $survey_id]);
        $survey = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Prepare questions for JS
$questions_json = $survey['questions'] ?? '[]';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Survey - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/navbar.php'; ?>

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
                        
                        <?php if (isset($breadcrumb['url'])): ?>
                            <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>" 
                               class="flex items-center space-x-1 text-blue-600 hover:text-blue-800 transition-colors duration-200">
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

            <div class="max-w-5xl mx-auto">
                <!-- Page Header -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6 mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                                <i class="fas fa-edit text-white text-xl"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                    Edit Survey
                                </h1>
                                <p class="text-gray-600 mt-1">Modify survey details and questions</p>
                            </div>
                        </div>
                        <a href="manage-surveys.php" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Surveys
                        </a>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-red-500"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-red-800 font-semibold">Please fix the following errors:</h3>
                                <ul class="mt-2 text-red-700 list-disc list-inside space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php elseif (!empty($success_message)): ?>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-green-800 font-semibold"><?php echo htmlspecialchars($success_message); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Edit Survey Form -->
                <form method="POST" id="editSurveyForm" class="space-y-8">
                    <!-- Basic Information Section -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-info-circle text-white"></i>
                                </div>
                                <h2 class="text-xl font-semibold text-white">Basic Information</h2>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-heading text-blue-500 mr-2"></i>
                                    Survey Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="survey_title" 
                                       required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white" 
                                       value="<?php echo htmlspecialchars($survey['title']); ?>"
                                       placeholder="Enter survey title">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-align-left text-blue-500 mr-2"></i>
                                    Description
                                </label>
                                <textarea name="survey_description" 
                                          rows="3" 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                                          placeholder="Describe the purpose of this survey"><?php echo htmlspecialchars($survey['description']); ?></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-users text-blue-500 mr-2"></i>
                                        Target Alumni Group
                                    </label>
                                    <select name="target_group" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white">
                                        <option value="" disabled>Select a school year</option>
                                        <?php foreach ($school_years as $sy): ?>
                                            <option value="<?php echo htmlspecialchars($sy); ?>" <?php echo ($survey['target_alumni'] == $sy) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sy); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-tag text-blue-500 mr-2"></i>
                                        Survey Type
                                    </label>
                                    <select name="survey_type" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white">
                                        <option value="Feedback" <?php echo ($survey['survey_type'] == 'Feedback') ? 'selected' : ''; ?>>Feedback Survey</option>
                                        <option value="Tracer Study" <?php echo ($survey['survey_type'] == 'Tracer Study') ? 'selected' : ''; ?>>Tracer Study</option>
                                        <option value="Career Development" <?php echo ($survey['survey_type'] == 'Career Development') ? 'selected' : ''; ?>>Career Development</option>
                                        <option value="Alumni Engagement" <?php echo ($survey['survey_type'] == 'Alumni Engagement') ? 'selected' : ''; ?>>Alumni Engagement</option>
                                        <option value="Open-Ended" <?php echo ($survey['survey_type'] == 'Open-Ended') ? 'selected' : ''; ?>>Open-Ended Questions</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule & Settings Section -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-calendar-alt text-white"></i>
                                </div>
                                <h2 class="text-xl font-semibold text-white">Schedule & Settings</h2>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-play text-green-500 mr-2"></i>
                                        Start Date
                                    </label>
                                    <input type="date" 
                                           name="start_date" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-gray-50 focus:bg-white" 
                                           value="<?php echo htmlspecialchars($survey['start_date']); ?>">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-stop text-red-500 mr-2"></i>
                                        End Date
                                    </label>
                                    <input type="date" 
                                           name="end_date" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-gray-50 focus:bg-white" 
                                           value="<?php echo htmlspecialchars($survey['end_date']); ?>">
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="anonymous" 
                                           id="anonymous" 
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" 
                                           value="1" 
                                           <?php echo ($survey['anonymous']) ? 'checked' : ''; ?>>
                                    <label for="anonymous" class="ml-3 text-sm font-semibold text-gray-700 flex items-center">
                                        <i class="fas fa-user-secret text-purple-500 mr-2"></i>
                                        Anonymous Responses
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-2 ml-7">Enable this to collect responses without identifying the respondent</p>
                            </div>
                        </div>
                    </div>

                    <!-- Questions Section -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-question-circle text-white"></i>
                                </div>
                                <h2 class="text-xl font-semibold text-white">Survey Questions</h2>
                            </div>
                        </div>
                        <div class="p-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-list text-purple-500 mr-2"></i>
                                    Questions (JSON format) <span class="text-red-500">*</span>
                                </label>
                                <textarea name="questions" 
                                          rows="8" 
                                          required 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 bg-gray-50 focus:bg-white font-mono text-sm"
                                          placeholder='[{"text":"What is your current employment status?","type":"multiple_choice","options":["Employed","Unemployed","Self-employed"]}]'><?php echo htmlspecialchars($survey['questions']); ?></textarea>
                                <div class="mt-3 p-4 bg-blue-50 rounded-xl border border-blue-200">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-semibold text-blue-800 mb-1">JSON Format Guide</h4>
                                            <p class="text-xs text-blue-700 mb-2">Enter questions as JSON array. Example format:</p>
                                            <code class="text-xs bg-white px-2 py-1 rounded border text-blue-800 block">
                                                [{"text":"Question text here?","type":"short_text"}]
                                            </code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6">
                        <div class="flex flex-col sm:flex-row justify-end gap-4">
                            <button type="submit" 
                                    name="action" 
                                    value="draft" 
                                    class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                                <i class="fas fa-save mr-2"></i>
                                Save as Draft
                            </button>
                            <button type="submit" 
                                    name="action" 
                                    value="publish" 
                                    class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-globe mr-2"></i>
                                Update & Publish Survey
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
    // Sidebar toggle functionality
    const toggleSidebar = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleSidebar && sidebar && overlay) {
        toggleSidebar.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }

    // Session refresh mechanism
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

    // Close mobile menu when clicking on links
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) { // lg breakpoint
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) { // lg breakpoint
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
        }
    });

    // Form validation
    document.getElementById('editSurveyForm').addEventListener('submit', function(e) {
        const title = document.querySelector('input[name="survey_title"]').value.trim();
        const questions = document.querySelector('textarea[name="questions"]').value.trim();
        
        if (!title) {
            alert('Please enter a survey title.');
            e.preventDefault();
            return;
        }
        
        if (!questions) {
            alert('Please enter at least one question.');
            e.preventDefault();
            return;
        }
        
        // Validate JSON format
        try {
            JSON.parse(questions);
        } catch (error) {
            alert('Please enter valid JSON format for questions.');
            e.preventDefault();
            return;
        }
    });

    // Auto-save functionality (optional)
    let autoSaveTimeout;
    function autoSave() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            const formData = new FormData(document.getElementById('editSurveyForm'));
            formData.append('action', 'draft');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.ok) {
                    // Show subtle save indicator
                    const indicator = document.createElement('div');
                    indicator.className = 'fixed top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-lg text-sm z-50';
                    indicator.textContent = 'Auto-saved';
                    document.body.appendChild(indicator);
                    setTimeout(() => indicator.remove(), 2000);
                }
            }).catch(() => {});
        }, 30000); // Auto-save every 30 seconds
    }

    // Trigger auto-save on input changes
    document.querySelectorAll('input, textarea, select').forEach(element => {
        element.addEventListener('input', autoSave);
    });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
