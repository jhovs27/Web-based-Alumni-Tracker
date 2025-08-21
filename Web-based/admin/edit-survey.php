<?php
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/navbar.php';
require_once 'config/database.php';

// Get survey ID
$survey_id = $_GET['id'] ?? null;
if (!$survey_id) {
    echo '<div class="main-container"><div class="alert alert-error">No survey ID provided.</div></div>';
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
    echo '<div class="main-container"><div class="alert alert-error">Survey not found.</div></div>';
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
    <title>Edit Survey - SLSU Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .main-container {
            padding: 2rem;
            margin-left: 0;
            width: 100%;
            min-height: 100vh;
            padding-top: 5rem;
        }

        @media (min-width: 1024px) {
            .main-container {
                margin-left: 16rem;
                width: calc(100% - 16rem);
            }
        }

        .survey-form {
            background: white;
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .required {
            color: #ef4444;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .add-question-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            background: white;
            color: #374151;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .add-question-btn:hover {
            border-color: #3b82f6;
            background: #3b82f6;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .form-actions {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem 0;
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            border-radius: 0 0 1.5rem 1.5rem;
        }

        .btn {
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
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
            background: #f8fafc;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .question-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 2px solid #e0f2fe;
            transition: all 0.3s ease;
            position: relative;
        }

        .question-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .question-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .question-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #3b82f6;
            color: white;
        }

        .multiple-options-list {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem;
            background: #f8fafc;
            margin-bottom: 0.5rem;
        }

        .option-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .question-input {
            flex: 1;
            padding: 0.625rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .question-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .delete-option-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            transition: all 0.2s ease;
        }

        .delete-option-btn:hover {
            background: #fecaca;
            transform: translateY(-1px);
        }

        .question-help {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
                margin-left: 0;
                width: 100%;
            }
            
            .survey-form {
                padding: 1.5rem;
            }
            
            .form-section {
                padding-bottom: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
                position: relative;
                bottom: auto;
            }

            .add-question-btn {
                font-size: 0.75rem;
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        Edit Survey
                    </h1>
                    <p class="text-gray-600 mt-1">Modify survey details and questions</p>
                </div>
                <a href="manage-surveys.php" class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Surveys
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <ul class="ml-4">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Survey Form -->
        <div class="survey-form">
            <form method="POST" id="surveyForm">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Basic Information
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="form-label">Survey Title <span class="required">*</span></label>
                            <input type="text" name="survey_title" required class="form-input" value="<?php echo htmlspecialchars($_POST['survey_title'] ?? $survey['title']); ?>" placeholder="Enter a descriptive title for your survey">
                        </div>

                        <div>
                            <label class="form-label">Survey Description</label>
                            <textarea name="survey_description" rows="3" class="form-input form-textarea" placeholder="Provide a brief description of the survey purpose and objectives"><?php echo htmlspecialchars($_POST['survey_description'] ?? $survey['description']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Target & Type -->
                <div class="form-section">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Target Audience & Type
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Target Alumni Group</label>
                            <select name="target_group" class="form-input">
                                <option value="" disabled>Select a school year</option>
                                <?php foreach ($school_years as $sy): ?>
                                    <option value="<?php echo htmlspecialchars($sy); ?>" <?php echo ((($_POST['target_group'] ?? $survey['target_alumni']) == $sy) ? 'selected' : ''); ?>><?php echo htmlspecialchars($sy); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Survey Type</label>
                            <select name="survey_type" class="form-input">
                                <option value="Feedback" <?php echo (($_POST['survey_type'] ?? $survey['survey_type']) === 'Feedback' ? 'selected' : ''); ?>>Feedback Survey</option>
                                <option value="Tracer Study" <?php echo (($_POST['survey_type'] ?? $survey['survey_type']) === 'Tracer Study' ? 'selected' : ''); ?>>Tracer Study</option>
                                <option value="Career Development" <?php echo (($_POST['survey_type'] ?? $survey['survey_type']) === 'Career Development' ? 'selected' : ''); ?>>Career Development</option>
                                <option value="Alumni Engagement" <?php echo (($_POST['survey_type'] ?? $survey['survey_type']) === 'Alumni Engagement' ? 'selected' : ''); ?>>Alumni Engagement</option>
                                <option value="Open-Ended" <?php echo (($_POST['survey_type'] ?? $survey['survey_type']) === 'Open-Ended' ? 'selected' : ''); ?>>Open-Ended Questions</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Questions Section -->
                <div class="form-section">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Survey Questions
                    </h3>
                    
                    <div id="questionsContainer" class="mb-6"></div>
                    
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Add Question Types:</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
                            <button type="button" onclick="addQuestion('multiple')" class="add-question-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                Multiple Choice
                            </button>
                            <button type="button" onclick="addQuestion('rating')" class="add-question-btn">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Rating Scale
                            </button>
                            <button type="button" onclick="addQuestion('short')" class="add-question-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Short Answer
                            </button>
                            <button type="button" onclick="addQuestion('paragraph')" class="add-question-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                Paragraph
                            </button>
                            <button type="button" onclick="addQuestion('yesno')" class="add-question-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Yes/No
                            </button>
                            <button type="button" onclick="addQuestion('email')" class="add-question-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Email
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Schedule & Settings -->
                <div class="form-section">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Schedule & Settings
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-input" value="<?php echo htmlspecialchars($_POST['start_date'] ?? $survey['start_date']); ?>">
                        </div>
                        <div>
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-input" value="<?php echo htmlspecialchars($_POST['end_date'] ?? $survey['end_date']); ?>">
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 p-4 bg-blue-50 rounded-lg">
                        <input type="checkbox" name="anonymous" id="anonymous" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" <?php echo ((isset($_POST['anonymous']) ? $_POST['anonymous'] : $survey['anonymous']) ? 'checked' : ''); ?>>
                        <label for="anonymous" class="text-sm font-medium text-gray-700">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Allow anonymous responses
                            </div>
                            <span class="text-xs text-gray-500 ml-6">Respondents can submit without providing personal information</span>
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" name="action" value="draft" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Save as Draft
                    </button>
                    <button type="submit" name="action" value="publish" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Publish Survey
                    </button>
                </div>
            </form>
        </div>
    </div>

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

        function addQuestion(type, data = {}) {
            questionCount++;
            const typeInfo = questionTypes[type];
            let html = `<div class='question-card' data-question-id='${questionCount}'>`;
            html += `<div class='flex justify-between items-start mb-4'>`;
            html += `<div class='flex items-center gap-3'><span class='question-type-badge'>Q${questionCount}</span><span class='text-sm font-medium text-blue-700'>${typeInfo.label}</span></div>`;
            html += `<div class='question-actions'><label class='flex items-center gap-2 text-sm text-gray-600'><input type='checkbox' name='questions[${questionCount}][required]' value='1' class='rounded' ${(data.required ? 'checked' : '')}><span>Required</span></label><button type='button' onclick='this.closest(".question-card").remove()' class='ml-3 text-red-500 hover:text-red-700 p-1 rounded-lg hover:bg-red-50 transition-colors duration-200'><svg class='w-4 h-4' fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>`;
            html += `</div>`;
            html += `<input type='hidden' name='questions[${questionCount}][type]' value='${type}'>`;
            html += `<input type='text' name='questions[${questionCount}][text]' placeholder='Enter your question...' class='w-full px-4 py-3 border-2 border-gray-200 rounded-lg mb-3 focus:border-blue-500 focus:outline-none transition-colors duration-200' value='${data.text || ''}'>`;
            if (type === 'multiple') {
                html += `<div class='multiple-options-list mb-2' id='optionsList_${questionCount}'>`;
                let options = (data.choices && Array.isArray(data.choices)) ? data.choices : ["", ""];
                if (data.choices && Array.isArray(data.choices) && data.choices.length > 0) options = data.choices;
                options.forEach((opt, idx) => {
                    html += renderOptionField(questionCount, idx, opt);
                });
                html += `</div>`;
                html += `<button type='button' class='add-option-btn btn btn-secondary mb-2' onclick='addOptionField(${questionCount})'><i class='fas fa-plus'></i> Add Option</button>`;
                html += `<div class='question-help'>Add or remove options as needed</div>`;
            } else if (type === 'rating') {
                html += `<div class='mb-3 p-3 bg-gray-50 rounded-lg'><span class='text-sm text-gray-600'>Rating scale: 1 (lowest) to 5 (highest)</span></div>`;
            } else if (type === 'yesno') {
                html += `<div class='mb-3 p-3 bg-gray-50 rounded-lg'><span class='text-sm text-gray-600'>Simple Yes/No question</span></div>`;
            }
            html += `</div>`;
            document.getElementById('questionsContainer').insertAdjacentHTML('beforeend', html);
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

        function deleteOptionField(btn) {
            const row = btn.closest('.option-row');
            row.remove();
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

        // Load existing questions
        window.onload = function() {
            let questions = <?php echo $questions_json ? $questions_json : '[]'; ?>;
            if (typeof questions === 'string') {
                try { 
                    questions = JSON.parse(questions); 
                } catch(e) { 
                    questions = []; 
                }
            }
            
            questions.forEach(q => {
                addQuestion(q.type, q);
            });
        };

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

        document.getElementById('surveyForm').addEventListener('submit', function(e) {
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
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
