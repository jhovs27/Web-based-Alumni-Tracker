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

// Fetch school year ranges for Target Alumni dropdown
$school_years = [];
$sql = "SELECT CONCAT(SchoolYear, '-', SchoolYear + 1) AS school_year_range FROM listgradmain GROUP BY SchoolYear ORDER BY SchoolYear DESC";
$stmt = $conn->query($sql);
if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $school_years[] = $row['school_year_range'];
    }
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
    $created_by = $_SESSION['chair_name'] ?? 'chair';
    
    $errors = [];
    if (empty($title)) $errors[] = "Survey title is required";
    if (empty($questions)) $errors[] = "At least one question is required";
    
    if (empty($errors)) {
        $questions_json = json_encode($questions);
        $sql = "INSERT INTO survey (title, description, target_alumni, survey_type, questions, start_date, end_date, anonymous, status, created_by) VALUES (:title, :description, :target_alumni, :survey_type, :questions, :start_date, :end_date, :anonymous, :status, :created_by)";
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
            ':created_by' => $created_by
        ]);
        
        $success_message = ($status === 'published') ? 'Survey published successfully!' : 'Survey saved as draft!';
        $_POST = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Survey - SLSU-HC Chair Panel</title>
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
            <!-- Breadcrumb Navigation -->
            <div class="mb-6">
                <nav class="flex items-center space-x-2 text-sm text-gray-600 bg-white/80 backdrop-blur-sm rounded-xl px-4 py-3 shadow-sm border border-blue-100">
                    <?php foreach (
                        isset($breadcrumbs) ? $breadcrumbs : [] as $index => $breadcrumb): ?>
                        <?php if ($index > 0): ?>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        <?php endif; ?>
                        <?php if (isset($breadcrumb['url'])): ?>
                            <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>" class="flex items-center space-x-1 text-blue-600 hover:text-blue-800 transition-colors duration-200">
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
            <!-- Header Section -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-6 border border-blue-100 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl text-white shadow-lg">
                            <i class="fas fa-plus-circle text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Create Survey</h1>
                            <p class="text-gray-600 mt-1">Design and publish surveys to gather valuable feedback from alumni</p>
                        </div>
                    </div>
                    <a href="manage-surveys.php" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Surveys
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-green-800 font-medium"><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-red-800 font-medium">Please fix the following errors:</h3>
                            <ul class="mt-2 text-red-700 list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Survey Form -->
            <form method="POST" id="surveyForm" class="space-y-8">
                <!-- Basic Information Section -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                            Basic Information
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Survey Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="survey_title" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                   placeholder="Enter survey title..." 
                                   value="<?php echo htmlspecialchars($_POST['survey_title'] ?? ''); ?>">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                            <textarea name="survey_description" rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                      placeholder="Provide a brief description of your survey..."><?php echo htmlspecialchars($_POST['survey_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Target Alumni Group</label>
                                <select name="target_group" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="" disabled selected>Select a school year</option>
                                    <?php foreach ($school_years as $sy): ?>
                                        <option value="<?php echo htmlspecialchars($sy); ?>" 
                                                <?php echo (($_POST['target_group'] ?? '') == $sy) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sy); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Survey Type</label>
                                <select name="survey_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="Feedback" <?php echo ($_POST['survey_type'] ?? '') === 'Feedback' ? 'selected' : ''; ?>>Feedback Survey</option>
                                    <option value="Tracer Study" <?php echo ($_POST['survey_type'] ?? '') === 'Tracer Study' ? 'selected' : ''; ?>>Tracer Study</option>
                                    <option value="Career Development" <?php echo ($_POST['survey_type'] ?? '') === 'Career Development' ? 'selected' : ''; ?>>Career Development</option>
                                    <option value="Alumni Engagement" <?php echo ($_POST['survey_type'] ?? '') === 'Alumni Engagement' ? 'selected' : ''; ?>>Alumni Engagement</option>
                                    <option value="Open-Ended" <?php echo ($_POST['survey_type'] ?? '') === 'Open-Ended' ? 'selected' : ''; ?>>Open-Ended Questions</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Survey Questions Section -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-question-circle text-blue-500 mr-3"></i>
                            Survey Questions
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-50 rounded-xl p-6 min-h-[200px]" id="questionsContainer">
                            <div class="text-center py-12" id="emptyState">
                                <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500">No questions added yet. Click the buttons below to add your first question.</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mt-6">
                            <button type="button" onclick="addQuestion('multiple')" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 group">
                                <i class="fas fa-list-ul text-2xl text-blue-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Multiple Choice</span>
                            </button>
                            <button type="button" onclick="addQuestion('rating')" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-all duration-200 group">
                                <i class="fas fa-star text-2xl text-green-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-green-700">Rating Scale</span>
                            </button>
                            <button type="button" onclick="addQuestion('short')" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-all duration-200 group">
                                <i class="fas fa-pen text-2xl text-yellow-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-700">Short Answer</span>
                            </button>
                            <button type="button" onclick="addQuestion('paragraph')" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all duration-200 group">
                                <i class="fas fa-align-left text-2xl text-purple-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700">Paragraph</span>
                            </button>
                            <button type="button" onclick="addQuestion('yesno')" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-pink-500 hover:bg-pink-50 transition-all duration-200 group">
                                <i class="fas fa-toggle-on text-2xl text-pink-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-pink-700">Yes/No</span>
                            </button>
                            <button type="button" onclick="addQuestion('email')" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-cyan-500 hover:bg-cyan-50 transition-all duration-200 group">
                                <i class="fas fa-envelope text-2xl text-cyan-500 mb-2 group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-cyan-700">Email</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Survey Settings Section -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-cog text-blue-500 mr-3"></i>
                            Survey Settings
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Start Date</label>
                                <input type="date" name="start_date" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                       value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">End Date</label>
                                <input type="date" name="end_date" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" 
                                       value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="flex items-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <input type="checkbox" name="anonymous" id="anonymous" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                   <?php echo isset($_POST['anonymous']) ? 'checked' : ''; ?>>
                            <label for="anonymous" class="ml-3 text-sm font-medium text-gray-700">Allow anonymous responses</label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-4 justify-end">
                    <button type="submit" name="action" value="draft" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Save as Draft
                    </button>
                    <button type="submit" name="action" value="publish" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 focus:ring-2 focus:ring-blue-500 transition-all duration-200 shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Publish Survey
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        let questionCount = 0;

        const questionTypes = {
            multiple: { icon: 'fas fa-list-ul', label: 'Multiple Choice', color: '#3b82f6' },
            rating: { icon: 'fas fa-star', label: 'Rating Scale', color: '#10b981' },
            short: { icon: 'fas fa-pen', label: 'Short Answer', color: '#f59e0b' },
            paragraph: { icon: 'fas fa-align-left', label: 'Paragraph', color: '#8b5cf6' },
            yesno: { icon: 'fas fa-toggle-on', label: 'Yes/No', color: '#ec4899' },
            email: { icon: 'fas fa-envelope', label: 'Email', color: '#06b6d4' }
        };

        function addQuestion(type) {
            questionCount++;
            const typeInfo = questionTypes[type];
            const emptyState = document.getElementById('emptyState');
            if (emptyState) { 
                emptyState.style.display = 'none'; 
            }

            let html = `
                <div class="bg-white rounded-xl p-6 mb-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200" data-question-id="${questionCount}">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center space-x-3">
                            <span class="text-lg font-bold text-blue-600">Question ${questionCount}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white" style="background-color: ${typeInfo.color};">
                                <i class="${typeInfo.icon} mr-1"></i>
                                ${typeInfo.label}
                            </span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="questions[${questionCount}][required]" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-600">Required</span>
                            </label>
                            <button type="button" onclick="deleteQuestion(this)" class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200" title="Delete question">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="questions[${questionCount}][type]" value="${type}">
                    <input type="text" name="questions[${questionCount}][text]" placeholder="Enter your question here..." 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 mb-3" required>
            `;

            if (type === 'multiple') {
                html += `
                    <input type="text" name="questions[${questionCount}][choices]" placeholder="Option 1, Option 2, Option 3, Option 4" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                    <p class="text-sm text-gray-500 mt-2">Separate options with commas</p>
                `;
            } else if (type === 'rating') {
                html += `<p class="text-sm text-gray-500">Rating scale: 1 (lowest) to 5 (highest)</p>`;
            } else if (type === 'yesno') {
                html += `<p class="text-sm text-gray-500">Simple Yes/No question</p>`;
            } else if (type === 'email') {
                html += `<p class="text-sm text-gray-500">Collects email addresses</p>`;
            }

            html += '</div>';

            document.getElementById('questionsContainer').insertAdjacentHTML('beforeend', html);

            // Focus on the new question input
            const newQuestion = document.querySelector(`[data-question-id="${questionCount}"]`);
            const input = newQuestion.querySelector('input[name*="[text]"]');
            input.focus();
        }

        function deleteQuestion(button) {
            const questionCard = button.closest('[data-question-id]');
            questionCard.style.animation = 'fadeOut 0.3s ease';
            
            setTimeout(() => {
                questionCard.remove();
                
                // Check if no questions remain
                const questions = document.querySelectorAll('[data-question-id]');
                if (questions.length === 0) {
                    const emptyState = document.getElementById('emptyState');
                    if (emptyState) { 
                        emptyState.style.display = 'block'; 
                    }
                }
                
                // Renumber questions
                renumberQuestions();
            }, 300);
        }

        function renumberQuestions() {
            const questions = document.querySelectorAll('[data-question-id]');
            questions.forEach((question, index) => {
                const numberSpan = question.querySelector('.text-lg.font-bold');
                if (numberSpan) { 
                    numberSpan.textContent = `Question ${index + 1}`; 
                }
            });
        }

        document.getElementById('surveyForm').addEventListener('submit', function(e) {
            const questions = document.querySelectorAll('[data-question-id]');
            if (questions.length === 0) {
                e.preventDefault();
                alert('Please add at least one question to your survey.');
                return;
            }

            const title = document.querySelector('input[name="survey_title"]').value.trim();
            if (!title) {
                e.preventDefault();
                alert('Please enter a survey title.');
                return;
            }
        });

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

        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: scale(1); }
                to { opacity: 0; transform: scale(0.95); }
            }
        `;
        document.head.appendChild(style);
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
