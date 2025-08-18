<?php
session_start();
// create-survey.php
// Survey creation logic and UI for admin panel
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/navbar.php';
include 'includes/breadcrumb.php';

// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Manage Surveys', 'url' => 'manage-survey.php', 'active' => false],
    ['title' => 'Create Survey', 'url' => 'create-survey.php', 'active' => true]
];

// require database connection if needed
require_once 'config/database.php';

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
    // Collect and validate form data
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
    $created_by = $_SESSION['user_role'] ?? 'admin'; // Adjust as needed
    
    // Basic validation
    $errors = [];
    if (empty($title)) {
        $errors[] = "Survey title is required";
    }
    if (empty($questions)) {
        $errors[] = "At least one question is required";
    }
    
    if (empty($errors)) {
        // Save to database
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
        // Optionally clear POST data to prevent resubmission
        $_POST = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Survey - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
        :root {
            --primary-color: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
  min-height: 100vh;
            color: var(--text-primary);
        }

        .main-container {
            padding: 2rem;
            margin-left: 0;
            width: 100%;
            min-height: 100vh;
            padding-top: 10rem;
        }

        @media (min-width: 1024px) {
            .main-container {
                margin-left: 16rem;
                width: calc(100% - 16rem);
                padding-top: 8rem;
            }
        }

        .survey-header {
            background: #f8fafc;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .header-title i {
            font-size: 2rem;
            color: var(--primary-color);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-title h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0;
        }

        .header-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .survey-form {
            background: #f8fafc;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
        }

        .form-section {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid var(--light-bg);
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .required {
            color: var(--danger-color);
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-select {
            padding-right: 2.5rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 12px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .checkbox-group:hover {
            border-color: var(--primary-color);
        }

        .checkbox-input {
            width: 1.25rem;
            height: 1.25rem;
            accent-color: var(--primary-color);
        }

        .questions-container {
            background: var(--light-bg);
            border-radius: 16px;
            padding: 1.5rem;
            min-height: 200px;
        }

        .question-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
  position: relative;
}

        .question-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .question-number {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .question-type {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .question-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .required-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .delete-btn {
            background: none;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: var(--danger-color);
            color: white;
        }

        .question-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .question-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .choices-input {
            font-size: 0.9rem;
        }

        .question-help {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-style: italic;
        }

        .add-question-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .add-question-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background: white;
            color: var(--text-primary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .add-question-btn:hover {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .add-question-btn i {
            font-size: 1rem;
        }

        .form-actions {
  position: sticky;
  bottom: 0;
            background: white;
            border-top: 2px solid var(--light-bg);
            padding: 1.5rem 0;
            margin-top: 2rem;
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
            border-radius: 0 0 20px 20px;
        }

        .btn {
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary {
            background: var(--light-bg);
            color: var(--text-secondary);
            border: 2px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .date-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .main-container {
                margin-left: 200px;
                width: calc(100% - 200px);
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            
            .survey-header {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .survey-form {
                padding: 1.5rem;
            }
            
            .header-title h1 {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 1.25rem;
            }
            
            .date-grid {
                grid-template-columns: 1fr;
            }
            
            .add-question-buttons {
                flex-direction: column;
            }
            
            .form-actions {
                flex-direction: column;
                position: relative;
                bottom: auto;
            }
            
            .question-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .question-actions {
                width: 100%;
                justify-content: space-between;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 0.75rem;
            }
            
            .survey-header {
                padding: 1rem;
                border-radius: 12px;
            }
            
            .survey-form {
                padding: 1rem;
                border-radius: 12px;
            }
            
            .header-title h1 {
                font-size: 1.75rem;
            }
            
            .form-input {
                padding: 0.75rem;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .drag-handle {
            cursor: move;
            color: var(--text-secondary);
            margin-right: 0.5rem;
        }

        .question-card.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
}
</style>
</head>
<body>
    <div class="main-container">
        <!-- Breadcrumb -->
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Manage Surveys', 'url' => 'manage-surveys.php'],
            ['title' => 'Create Survey', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
        
        <!-- Header Section -->
        <!-- Removed survey-header with 'Create New Survey' and subtitle -->

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <ul style="margin-left: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Survey Form -->
        <form method="POST" id="surveyForm" class="survey-form">
            <!-- Basic Information Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Basic Information
                </h2>
                
                <div class="form-group">
                    <label class="form-label">
                        Survey Title <span class="required">*</span>
                    </label>
                    <input type="text" name="survey_title" required class="form-input" 
                           placeholder="Enter survey title..." 
                           value="<?php echo htmlspecialchars($_POST['survey_title'] ?? ''); ?>">
        </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="survey_description" class="form-input form-textarea" 
                              placeholder="Provide a brief description of your survey..."><?php echo htmlspecialchars($_POST['survey_description'] ?? ''); ?></textarea>
        </div>

                <div class="form-group">
                    <label class="form-label">Target Alumni Group</label>
                    <select name="target_group" class="form-input form-select">
                        <option value="" disabled selected>Select a school year</option>
                        <?php foreach ($school_years as $sy): ?>
                            <option value="<?php echo htmlspecialchars($sy); ?>" <?php echo (($_POST['target_group'] ?? '') == $sy) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sy); ?></option>
                        <?php endforeach; ?>
            </select>
        </div>

                <div class="form-group">
                    <label class="form-label">Survey Type</label>
                    <select name="survey_type" class="form-input form-select">
                        <option value="Feedback" <?php echo ($_POST['survey_type'] ?? '') === 'Feedback' ? 'selected' : ''; ?>>Feedback Survey</option>
                        <option value="Tracer Study" <?php echo ($_POST['survey_type'] ?? '') === 'Tracer Study' ? 'selected' : ''; ?>>Tracer Study</option>
                        <option value="Career Development" <?php echo ($_POST['survey_type'] ?? '') === 'Career Development' ? 'selected' : ''; ?>>Career Development</option>
                        <option value="Alumni Engagement" <?php echo ($_POST['survey_type'] ?? '') === 'Alumni Engagement' ? 'selected' : ''; ?>>Alumni Engagement</option>
                        <option value="Open-Ended" <?php echo ($_POST['survey_type'] ?? '') === 'Open-Ended' ? 'selected' : ''; ?>>Open-Ended Questions</option>
            </select>
        </div>
            </div>

            <!-- Questions Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-question-circle"></i>
                    Survey Questions
                </h2>
                
                <div class="questions-container" id="questionsContainer">
                    <div class="empty-state" id="emptyState">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No questions added yet. Click the buttons below to add your first question.</p>
                    </div>
                </div>

                <div class="add-question-buttons">
                    <button type="button" onclick="addQuestion('multiple')" class="add-question-btn">
                        <i class="fas fa-list-ul"></i>
                        Multiple Choice
                    </button>
                    <button type="button" onclick="addQuestion('rating')" class="add-question-btn">
                        <i class="fas fa-star"></i>
                        Rating Scale
                    </button>
        </div>
            </div>

            <!-- Settings Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-cog"></i>
                    Survey Settings
                </h2>

                <div class="date-grid">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-input" 
                               value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
            </div>
        </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="anonymous" id="anonymous" class="checkbox-input" 
                           <?php echo isset($_POST['anonymous']) ? 'checked' : ''; ?>>
                    <label for="anonymous" class="form-label" style="margin: 0;">
                        Allow anonymous responses
                    </label>
                </div>
        </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="action" value="draft" class="btn btn-secondary">
                    <i class="fas fa-save"></i>
                    Save as Draft
                </button>
                <button type="submit" name="action" value="publish" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Publish Survey
                </button>
        </div>
    </form>
</div>

<script>
let questionCount = 0;
const questionTypes = {
    multiple: { icon: 'fas fa-list-ul', label: 'Multiple Choice', color: '#3b82f6' },
    rating: { icon: 'fas fa-star', label: 'Rating Scale', color: '#10b981' }
};

function addQuestion(type, data = {}) {
    questionCount++;
    const typeInfo = questionTypes[type];
    const emptyState = document.getElementById('emptyState');
    if (emptyState) emptyState.style.display = 'none';
    let html = `<div class="question-card" data-question-id="${questionCount}">`;
    html += `<div class="question-header">`;
    html += `<div style="display: flex; align-items: center;"><i class="fas fa-grip-vertical drag-handle"></i><span class="question-number">Question ${questionCount}</span></div>`;
    html += `<div class="question-actions"><div class="required-toggle"><input type="checkbox" name="questions[${questionCount}][required]" value="1" id="required_${questionCount}"><label for="required_${questionCount}">Required</label></div><button type="button" onclick="deleteQuestion(this)" class="delete-btn" title="Delete question"><i class="fas fa-trash"></i></button></div>`;
    html += `</div>`;
    html += `<div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;"><i class="${typeInfo.icon}" style="color: ${typeInfo.color};"></i><span class="question-type" style="background: ${typeInfo.color};">${typeInfo.label}</span></div>`;
    html += `<input type="hidden" name="questions[${questionCount}][type]" value="${type}">`;
    html += `<input type="text" name="questions[${questionCount}][text]" placeholder="Enter your question here..." class="question-input" required>`;
    if (type === 'multiple') {
        html += `<div class="multiple-options-list mb-2" id="optionsList_${questionCount}">`;
        let options = (data.choices && Array.isArray(data.choices)) ? data.choices : ["", ""];
        if (data.choices && Array.isArray(data.choices) && data.choices.length > 0) options = data.choices;
        options.forEach((opt, idx) => {
            html += renderOptionField(questionCount, idx, opt);
        });
        html += `</div>`;
        html += `<button type="button" class="add-option-btn btn btn-secondary mb-2" onclick="addOptionField(${questionCount})"><i class='fas fa-plus'></i> Add Option</button>`;
        html += `<div class="question-help">Add or remove options as needed</div>`;
    } else if (type === 'rating') {
        html += `<div class="question-help">Rating scale: 1 (lowest) to 5 (highest)</div>`;
    }
    html += '</div>';
    document.getElementById('questionsContainer').insertAdjacentHTML('beforeend', html);
    // Focus on the new question input
    const newQuestion = document.querySelector(`[data-question-id="${questionCount}"]`);
    const input = newQuestion.querySelector('input[name*="[text]"]');
    input.focus();
}
function renderOptionField(qId, idx, value) {
    return `<div class='flex items-center gap-2 mb-1 option-row group'>
        <span class='cursor-move text-gray-400 hover:text-blue-500 transition' title='Drag to reorder'><i class='fas fa-grip-vertical'></i></span>
        <input type='text' name='questions[${qId}][choices][${idx}]' class='question-input choices-input flex-1 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition px-3 py-2 bg-white shadow-sm' placeholder='Option' value='${value ? value.replace(/'/g, "&#39;") : ''}' required>
        <button type='button' class='delete-option-btn bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-700 rounded-full p-1.5 flex items-center justify-center transition shadow-sm border border-red-100 ml-1' style='width:2rem; height:2rem;' onclick='deleteOptionField(this)' title='Delete option'><i class='fas fa-trash text-xs'></i></button>
    </div>`;
}
function addOptionField(qId) {
    const list = document.getElementById(`optionsList_${qId}`);
    const idx = list.querySelectorAll('.option-row').length;
    list.insertAdjacentHTML('beforeend', renderOptionField(qId, idx, ""));
    makeOptionsSortable(qId);
}
function makeOptionsSortable(qId) {
    const list = document.getElementById(`optionsList_${qId}`);
    if (!list) return;
    if (list._sortable) return; // Prevent double init
    list._sortable = Sortable.create(list, {
        handle: '.fa-grip-vertical',
        animation: 150,
        ghostClass: 'bg-blue-50',
        onEnd: function() {
            // Re-index input names after reorder
            Array.from(list.querySelectorAll('.option-row')).forEach((row, idx) => {
                const input = row.querySelector('input');
                if (input) input.name = `questions[${qId}][choices][${idx}]`;
            });
        }
    });
}
// Call makeOptionsSortable after adding a question
const origAddQuestion = addQuestion;
addQuestion = function(type, data = {}) {
    origAddQuestion(type, data);
    if (type === 'multiple') {
        makeOptionsSortable(questionCount);
    }
};

        function deleteQuestion(button) {
            const questionCard = button.closest('.question-card');
            questionCard.style.animation = 'fadeOut 0.3s ease';
            
            setTimeout(() => {
                questionCard.remove();
                
                // Check if no questions remain
                const questions = document.querySelectorAll('.question-card');
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
            const questions = document.querySelectorAll('.question-card');
            questions.forEach((question, index) => {
                const numberSpan = question.querySelector('.question-number');
                if (numberSpan) {
                    numberSpan.textContent = `Question ${index + 1}`;
                }
            });
        }

        function deleteOptionField(btn) {
            const row = btn.closest('.option-row');
            row.remove();
        }

        // Form validation
        document.getElementById('surveyForm').addEventListener('submit', function(e) {
            const questions = document.querySelectorAll('.question-card');
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
            // Validate multiple choice options
            const multipleQuestions = document.querySelectorAll('.question-card input[type="hidden"][value="multiple"]');
            multipleQuestions.forEach(h => {
                const qCard = h.closest('.question-card');
                const options = qCard.querySelectorAll('.option-row input');
                if (options.length < 2) {
                    e.preventDefault();
                    alert('Each multiple choice question must have at least 2 options.');
                }
                options.forEach(opt => {
                    if (!opt.value.trim()) {
                        e.preventDefault();
                        alert('Multiple choice options cannot be empty.');
                    }
                });
            });
        });

        // Add some CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: scale(1); }
                to { opacity: 0; transform: scale(0.95); }
            }
            
            .question-card {
                animation: slideIn 0.3s ease;
            }
            
            @keyframes slideIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);

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
</html>

<?php include 'includes/footer.php'; ?>
