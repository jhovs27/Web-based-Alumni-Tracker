<?php
session_start();
require_once '../admin/config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';

// Set current page for sidebar highlighting
$current_page = 'manage-posts';

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'],
    ['label' => 'Manage Posts', 'url' => 'manage-posts.php', 'icon' => 'fa-newspaper'],
    ['label' => 'Edit Job Post', 'icon' => 'fa-edit'],
];

// Check if user is logged in as program chair
if (!isset($_SESSION['is_chair']) || !$_SESSION['is_chair']) {
    header('Location: ../login.php');
    exit();
}

// Get job post ID
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$job_id) {
    $_SESSION['error'] = "Invalid job post ID.";
    header('Location: manage-posts.php');
    exit();
}

// Fetch job post data
try {
    $stmt = $conn->prepare("SELECT * FROM job_posts WHERE id = ?");
    $stmt->execute([$job_id]);
    $job_post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job_post) {
        $_SESSION['error'] = "Job post not found.";
        header('Location: manage-posts.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching job post: " . $e->getMessage();
    header('Location: manage-posts.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Post - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        
        .form-input {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        }
        
        .form-input:focus {
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .icon-wrapper {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            transition: all 0.3s ease;
        }
        
        .icon-wrapper:hover {
            background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
            transform: scale(1.1);
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
            height: 4px;
            border-radius: 2px;
            transition: width 0.3s ease;
        }
        
        .floating-label {
            transition: all 0.3s ease;
        }
        
        .form-input:focus + .floating-label,
        .form-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #3b82f6;
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
            <!-- Page Header -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6 mb-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="icon-wrapper w-12 h-12 rounded-xl flex items-center justify-center">
                            <i class="fas fa-edit text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold section-header">Edit Job Post</h1>
                            <p class="text-gray-600 mt-1">Update job posting information and requirements</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="view-post.php?id=<?php echo $job_post['id']; ?>" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
                            <i class="fas fa-eye mr-2"></i>
                            Preview
                        </a>
                        <a href="manage-posts.php" 
                           class="btn-secondary inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Posts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="max-w-5xl mx-auto">
                <form action="job_post_actions.php" method="POST" enctype="multipart/form-data" id="editJobForm">
                    <input type="hidden" name="job_id" value="<?php echo $job_post['id']; ?>">
                    
                    <!-- Basic Information Section -->
                    <div class="form-section rounded-2xl shadow-lg p-6 mb-8">
                        <div class="flex items-center mb-6">
                            <div class="icon-wrapper w-10 h-10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <h2 class="text-xl font-semibold section-header">Basic Information</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Job Title -->
                            <div class="lg:col-span-2">
                                <label for="jobTitle" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-briefcase mr-2 text-blue-500"></i>Job Title *
                                </label>
                                <input type="text" id="jobTitle" name="jobTitle" 
                                       value="<?php echo htmlspecialchars($job_post['job_title']); ?>" 
                                       required
                                       class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Company Name -->
                            <div>
                                <label for="companyName" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-building mr-2 text-green-500"></i>Company Name *
                                </label>
                                <input type="text" id="companyName" name="companyName" 
                                       value="<?php echo htmlspecialchars($job_post['company_name']); ?>" 
                                       required
                                       class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Location -->
                            <div>
                                <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>Location *
                                </label>
                                <input type="text" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($job_post['location']); ?>" 
                                       required
                                       class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <!-- Company Logo -->
                        <div class="mt-6">
                            <label for="companyLogo" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-image mr-2 text-purple-500"></i>Company Logo
                            </label>
                            <?php if ($job_post['company_logo']): ?>
                                <div class="mb-4 p-4 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                                    <div class="flex items-center space-x-4">
                                        <img src="../admin/<?php echo htmlspecialchars($job_post['company_logo']); ?>" 
                                             alt="Current Logo" 
                                             class="h-16 w-16 object-contain rounded-lg border border-gray-200">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Current Logo</p>
                                            <p class="text-xs text-gray-500">Upload a new image to replace</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="companyLogo" name="companyLogo" accept="image/*"
                                   class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Recommended: PNG or JPG, max 2MB. Leave empty to keep current logo.
                            </p>
                        </div>
                    </div>

                    <!-- Job Details Section -->
                    <div class="form-section rounded-2xl shadow-lg p-6 mb-8">
                        <div class="flex items-center mb-6">
                            <div class="icon-wrapper w-10 h-10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-cogs text-blue-600"></i>
                            </div>
                            <h2 class="text-xl font-semibold section-header">Job Details</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Job Type -->
                            <div>
                                <label for="jobType" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-clock mr-2 text-blue-500"></i>Job Type *
                                </label>
                                <select id="jobType" name="jobType" required
                                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Job Type</option>
                                    <option value="Full-time" <?php echo $job_post['job_type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                    <option value="Part-time" <?php echo $job_post['job_type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                    <option value="Internship" <?php echo $job_post['job_type'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                                    <option value="Freelance" <?php echo $job_post['job_type'] === 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                                    <option value="Remote" <?php echo $job_post['job_type'] === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                                    <option value="On-site" <?php echo $job_post['job_type'] === 'On-site' ? 'selected' : ''; ?>>On-site</option>
                                </select>
                            </div>
                            
                            <!-- Job Category -->
                            <div>
                                <label for="jobCategory" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-tag mr-2 text-green-500"></i>Job Category *
                                </label>
                                <select id="jobCategory" name="jobCategory" required
                                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Category</option>
                                    <?php
                                    $categories = ['IT', 'Education', 'Healthcare', 'Engineering', 'Business', 'Finance', 'Arts', 'Science', 'Other'];
                                    foreach ($categories as $category):
                                    ?>
                                        <option value="<?php echo $category; ?>" <?php echo $job_post['job_category'] === $category ? 'selected' : ''; ?>>
                                            <?php echo $category; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Application Deadline -->
                            <div>
                                <label for="deadline" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-alt mr-2 text-red-500"></i>Application Deadline *
                                </label>
                                <input type="date" id="deadline" name="deadline" 
                                       value="<?php echo $job_post['deadline']; ?>" 
                                       required
                                       class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Post Status -->
                            <div>
                                <label for="postStatus" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-toggle-on mr-2 text-purple-500"></i>Post Status *
                                </label>
                                <select id="postStatus" name="postStatus" required
                                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="draft" <?php echo $job_post['status'] === 'draft' ? 'selected' : ''; ?>>
                                        <i class="fas fa-edit"></i> Draft
                                    </option>
                                    <option value="published" <?php echo $job_post['status'] === 'published' ? 'selected' : ''; ?>>
                                        <i class="fas fa-globe"></i> Published
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Information Section -->
                    <div class="form-section rounded-2xl shadow-lg p-6 mb-8">
                        <div class="flex items-center mb-6">
                            <div class="icon-wrapper w-10 h-10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-money-bill-wave text-blue-600"></i>
                            </div>
                            <h2 class="text-xl font-semibold section-header">Salary Information</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Minimum Salary -->
                            <div>
                                <label for="salaryMin" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-arrow-down mr-2 text-green-500"></i>Minimum Salary
                                </label>
                                <input type="number" id="salaryMin" name="salaryMin" 
                                       value="<?php echo $job_post['salary_min']; ?>" 
                                       min="0" step="1000"
                                       class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Maximum Salary -->
                            <div>
                                <label for="salaryMax" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-arrow-up mr-2 text-red-500"></i>Maximum Salary
                                </label>
                                <input type="number" id="salaryMax" name="salaryMax" 
                                       value="<?php echo $job_post['salary_max']; ?>" 
                                       min="0" step="1000"
                                       class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Currency -->
                            <div>
                                <label for="currency" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-coins mr-2 text-yellow-500"></i>Currency *
                                </label>
                                <select id="currency" name="currency" required
                                        class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="PHP" <?php echo $job_post['currency'] === 'PHP' ? 'selected' : ''; ?>>PHP (₱)</option>
                                    <option value="USD" <?php echo $job_post['currency'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                    <option value="EUR" <?php echo $job_post['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                    <option value="GBP" <?php echo $job_post['currency'] === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                    <option value="JPY" <?php echo $job_post['currency'] === 'JPY' ? 'selected' : ''; ?>>JPY (¥)</option>
                                    <option value="AUD" <?php echo $job_post['currency'] === 'AUD' ? 'selected' : ''; ?>>AUD (A$)</option>
                                    <option value="CAD" <?php echo $job_post['currency'] === 'CAD' ? 'selected' : ''; ?>>CAD (C$)</option>
                                    <option value="CHF" <?php echo $job_post['currency'] === 'CHF' ? 'selected' : ''; ?>>CHF (Fr)</option>
                                    <option value="CNY" <?php echo $job_post['currency'] === 'CNY' ? 'selected' : ''; ?>>CNY (¥)</option>
                                    <option value="INR" <?php echo $job_post['currency'] === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                                    <option value="SGD" <?php echo $job_post['currency'] === 'SGD' ? 'selected' : ''; ?>>SGD (S$)</option>
                                    <option value="AED" <?php echo $job_post['currency'] === 'AED' ? 'selected' : ''; ?>>AED (د.إ)</option>
                                    <option value="BRL" <?php echo $job_post['currency'] === 'BRL' ? 'selected' : ''; ?>>BRL (R$)</option>
                                    <option value="MXN" <?php echo $job_post['currency'] === 'MXN' ? 'selected' : ''; ?>>MXN (Mex$)</option>
                                    <option value="NZD" <?php echo $job_post['currency'] === 'NZD' ? 'selected' : ''; ?>>NZD (NZ$)</option>
                                    <option value="ZAR" <?php echo $job_post['currency'] === 'ZAR' ? 'selected' : ''; ?>>ZAR (R)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-lightbulb text-blue-500 mt-1"></i>
                                <div>
                                    <p class="text-sm font-medium text-blue-800">Salary Range Tips</p>
                                    <p class="text-sm text-blue-600 mt-1">
                                        Leave salary fields empty if you prefer not to disclose compensation details. 
                                        Consider market rates for similar positions in your location.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="form-section rounded-2xl shadow-lg p-6 mb-8">
                        <div class="flex items-center mb-6">
                            <div class="icon-wrapper w-10 h-10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-address-book text-blue-600"></i>
                            </div>
                            <h2 class="text-xl font-semibold section-header">Contact Information</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contact Email -->
                            <div>
                                <label for="contactEmail" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-2 text-blue-500"></i>Contact Email *
                                </label>
                                <input type="email" id="contactEmail" name="contactEmail" 
                                       value="<?php echo htmlspecialchars($job_post['contact_email']); ?>" 
                                       required
                                       class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Contact Phone -->
                            <div>
                                <label for="contactPhone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-phone mr-2 text-green-500"></i>Contact Phone
                                </label>
                                <input type="tel" id="contactPhone" name="contactPhone" 
                                       value="<?php echo htmlspecialchars($job_post['contact_phone']); ?>"
                                       class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <!-- Job Application Link -->
                        <div class="mt-6">
                            <label for="jobLink" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-external-link-alt mr-2 text-purple-500"></i>Job Application Link
                            </label>
                            <input type="url" id="jobLink" name="jobLink" 
                                   value="<?php echo htmlspecialchars($job_post['job_link']); ?>"
                                   class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="https://example.com/apply">
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Direct link to the job application page (optional)
                            </p>
                        </div>
                    </div>

                    <!-- Job Content Section -->
                    <div class="form-section rounded-2xl shadow-lg p-6 mb-8">
                        <div class="flex items-center mb-6">
                            <div class="icon-wrapper w-10 h-10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-file-alt text-blue-600"></i>
                            </div>
                            <h2 class="text-xl font-semibold section-header">Job Content</h2>
                        </div>
                        
                        <!-- Job Description -->
                        <div class="mb-6">
                            <label for="jobDescription" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-2 text-blue-500"></i>Job Description *
                            </label>
                            <textarea id="jobDescription" name="jobDescription" rows="6" required
                                      class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Describe the role, responsibilities, and what the candidate will be doing..."><?php echo htmlspecialchars($job_post['job_description']); ?></textarea>
                        </div>
                        
                        <!-- Qualifications -->
                        <div class="mb-6">
                            <label for="qualifications" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-graduation-cap mr-2 text-green-500"></i>Qualifications *
                            </label>
                            <textarea id="qualifications" name="qualifications" rows="4" required
                                      class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="List required education, experience, skills, and certifications..."><?php echo htmlspecialchars($job_post['qualifications']); ?></textarea>
                        </div>
                        
                        <!-- How to Apply -->
                        <div>
                            <label for="howToApply" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-paper-plane mr-2 text-purple-500"></i>How to Apply *
                            </label>
                            <textarea id="howToApply" name="howToApply" rows="4" required
                                      class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Provide clear instructions on how candidates should apply..."><?php echo htmlspecialchars($job_post['how_to_apply']); ?></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 p-6">
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="manage-posts.php" 
                               class="btn-secondary inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-gray-700 rounded-xl">
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                            <button type="button" id="previewBtn"
                                    class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition-all duration-200">
                                <i class="fas fa-eye mr-2"></i>
                                Preview Changes
                            </button>
                            <button type="submit" name="update_job_post" id="updateBtn"
                                    class="btn-primary inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white rounded-xl">
                                <i class="fas fa-save mr-2"></i>
                                <span id="updateBtnText">Update Job Post</span>
                                <div id="updateSpinner" class="hidden ml-2">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
    // Form validation and enhancement
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editJobForm');
        const updateBtn = document.getElementById('updateBtn');
        const updateBtnText = document.getElementById('updateBtnText');
        const updateSpinner = document.getElementById('updateSpinner');
        const previewBtn = document.getElementById('previewBtn');
        
        // Form submission handling
        form.addEventListener('submit', function(e) {
            updateBtn.disabled = true;
            updateBtnText.textContent = 'Updating...';
            updateSpinner.classList.remove('hidden');
        });
        
        // Preview functionality
        previewBtn.addEventListener('click', function() {
            const jobId = document.querySelector('input[name="job_id"]').value;
            window.open(`view-post.php?id=${jobId}`, '_blank');
        });
        
        // Auto-save draft functionality
        let autoSaveTimeout;
        const formInputs = form.querySelectorAll('input, textarea, select');
        
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(autoSaveDraft, 30000); // Auto-save after 30 seconds of inactivity
            });
        });
        
        function autoSaveDraft() {
            const formData = new FormData(form);
            formData.append('auto_save', '1');
            
            fetch('job_post_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Draft saved automatically', 'success');
                }
            })
            .catch(error => {
                console.log('Auto-save failed:', error);
            });
        }
        
        // Salary validation
        const salaryMin = document.getElementById('salaryMin');
        const salaryMax = document.getElementById('salaryMax');
        
        function validateSalary() {
            const min = parseFloat(salaryMin.value) || 0;
            const max = parseFloat(salaryMax.value) || 0;
            
            if (min > 0 && max > 0 && min >= max) {
                salaryMax.setCustomValidity('Maximum salary must be greater than minimum salary');
            } else {
                salaryMax.setCustomValidity('');
            }
        }
        
        salaryMin.addEventListener('input', validateSalary);
        salaryMax.addEventListener('input', validateSalary);
        
        // Character count for textareas
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            const maxLength = textarea.getAttribute('maxlength');
            if (maxLength) {
                const counter = document.createElement('div');
                counter.className = 'text-sm text-gray-500 mt-1 text-right';
                counter.textContent = `0 / ${maxLength}`;
                textarea.parentNode.appendChild(counter);
                
                textarea.addEventListener('input', function() {
                    const currentLength = this.value.length;
                    counter.textContent = `${currentLength} / ${maxLength}`;
                    
                    if (currentLength > maxLength * 0.9) {
                        counter.className = 'text-sm text-orange-500 mt-1 text-right';
                    } else {
                        counter.className = 'text-sm text-gray-500 mt-1 text-right';
                    }
                });
            }
        });
        
        // Show notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    });

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
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
