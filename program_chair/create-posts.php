<?php
session_start();
require_once '../admin/config/database.php';
include 'includes/header.php';

// Set current page for sidebar highlighting
$current_page = 'create-posts';

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'],
    ['label' => 'Manage Posts', 'url' => 'manage-posts.php', 'icon' => 'fa-newspaper'],
    ['label' => 'Create Job Post', 'icon' => 'fa-plus'],
];

// Get job categories from database or define statically
$categories = [
    'IT', 'Education', 'Healthcare', 'Engineering', 'Business', 'Finance', 'Arts', 'Science', 'Other'
];

// Display error message if any
if (isset($_SESSION['error'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">' . $_SESSION['error'] . '</span>
          </div>';
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job Post - SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Job Post
                        </h1>
                        <p class="mt-2 text-gray-600">Create and publish new job opportunities for alumni</p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <a href="manage-posts.php" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Manage Posts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form Container -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200">
                <form id="jobPostForm" action="job_post_actions.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                    
                    <!-- Basic Information Section -->
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Basic Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Job Title -->
                            <div>
                                <label for="jobTitle" class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="jobTitle" name="jobTitle" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                       placeholder="e.g., Junior Software Developer">
                            </div>

                            <!-- Company Name -->
                            <div>
                                <label for="companyName" class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="companyName" name="companyName" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                       placeholder="e.g., TechNova Inc.">
                            </div>

                            <!-- Job Type -->
                            <div>
                                <label for="jobType" class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Type <span class="text-red-500">*</span>
                                </label>
                                <select id="jobType" name="jobType" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">Select Job Type</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Freelance">Freelance</option>
                                    <option value="Remote">Remote</option>
                                    <option value="On-site">On-site</option>
                                </select>
                            </div>

                            <!-- Job Category -->
                            <div>
                                <label for="jobCategory" class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Category <span class="text-red-500">*</span>
                                </label>
                                <select id="jobCategory" name="jobCategory" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Location -->
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                    Location <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="location" name="location" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                       placeholder="e.g., Manila, Philippines or Remote">
                            </div>

                            <!-- Company Logo -->
                            <div>
                                <label for="companyLogo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Logo
                                </label>
                                <div class="relative">
                                    <input type="file" id="companyLogo" name="companyLogo" accept="image/*" class="hidden">
                                    <label for="companyLogo" class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200">
                                        <div class="text-center">
                                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <span id="selectedFileName" class="text-sm text-gray-600">Choose file or drag here</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compensation Section -->
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Compensation & Benefits
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Currency -->
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Currency <span class="text-red-500">*</span>
                                </label>
                                <select id="currency" name="currency" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">Select Currency</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                    <option value="JPY">JPY (¥)</option>
                                    <option value="PHP" selected>PHP (₱)</option>
                                    <option value="SGD">SGD (S$)</option>
                                    <option value="AUD">AUD (A$)</option>
                                    <option value="CAD">CAD (C$)</option>
                                </select>
                            </div>

                            <!-- Salary Min -->
                            <div>
                                <label for="salaryMin" class="block text-sm font-medium text-gray-700 mb-2">
                                    Minimum Salary
                                </label>
                                <input type="number" id="salaryMin" name="salaryMin"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                       placeholder="e.g., 25000">
                            </div>

                            <!-- Salary Max -->
                            <div>
                                <label for="salaryMax" class="block text-sm font-medium text-gray-700 mb-2">
                                    Maximum Salary
                                </label>
                                <input type="number" id="salaryMax" name="salaryMax"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                       placeholder="e.g., 35000">
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Deadline Section -->
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Contact & Deadline
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Application Deadline -->
                            <div>
                                <label for="deadline" class="block text-sm font-medium text-gray-700 mb-2">
                                    Application Deadline <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="deadline" name="deadline" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                            </div>

                            <!-- Contact Email -->
                            <div>
                                <label for="contactEmail" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contact Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="contactEmail" name="contactEmail" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                       placeholder="e.g., careers@company.com">
                            </div>

                            <!-- Contact Phone -->
                            <div>
                                <label for="contactPhone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contact Phone
                                </label>
                                <input type="text" id="contactPhone" name="contactPhone"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                       placeholder="e.g., +63 912 345 6789">
                            </div>
                        </div>
                    </div>

                    <!-- Job Details Section -->
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Job Details
                        </h2>
                        
                        <div class="space-y-6">
                            <!-- Job Description -->
                            <div>
                                <label for="jobDescription" class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Description <span class="text-red-500">*</span>
                                </label>
                                <textarea id="jobDescription" name="jobDescription" required rows="6"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-vertical"
                                          placeholder="Enter detailed job description including responsibilities, duties, and what the role entails..."></textarea>
                                <div class="mt-2 text-right">
                                    <span id="jobDescCounter" class="text-sm text-gray-500">0 characters</span>
                                </div>
                            </div>

                            <!-- Qualifications -->
                            <div>
                                <label for="qualifications" class="block text-sm font-medium text-gray-700 mb-2">
                                    Qualifications <span class="text-red-500">*</span>
                                </label>
                                <textarea id="qualifications" name="qualifications" required rows="6"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-vertical"
                                          placeholder="Enter required qualifications, skills, experience, and educational requirements..."></textarea>
                                <div class="mt-2 text-right">
                                    <span id="qualificationsCounter" class="text-sm text-gray-500">0 characters</span>
                                </div>
                            </div>

                            <!-- How to Apply -->
                            <div>
                                <label for="howToApply" class="block text-sm font-medium text-gray-700 mb-2">
                                    How to Apply <span class="text-red-500">*</span>
                                </label>
                                <textarea id="howToApply" name="howToApply" required rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-vertical"
                                          placeholder="Enter detailed application instructions, required documents, and submission process..."></textarea>
                                <div class="mt-2 text-right">
                                    <span id="howToApplyCounter" class="text-sm text-gray-500">0 characters</span>
                                </div>
                            </div>

                            <!-- Job Link -->
                            <div>
                                <label for="jobLink" class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Link (URL)
                                </label>
                                <input type="url" id="jobLink" name="jobLink"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                       placeholder="https://example.com/apply">
                                <p class="mt-2 text-sm text-gray-600">Optional: Provide a direct link to the job application page.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Post Status Section -->
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543.826 3.31 2.37 2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Post Settings
                        </h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">Post Status</label>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="postStatus" value="draft" checked
                                           class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700">Save as Draft</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="postStatus" value="published"
                                           class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700">Publish Immediately</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                            <button type="button" id="previewBtn" 
                                    class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 bg-white rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview
                            </button>
                            
                            <button type="submit" name="create_job_post" value="draft"
                                    class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 bg-white rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Save as Draft
                            </button>
                            
                            <button type="submit" name="create_job_post" value="published"
                                    class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 shadow-sm">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Publish Job Post
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

    // File Upload Preview
    document.getElementById('companyLogo').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Choose file or drag here';
        document.getElementById('selectedFileName').textContent = fileName;
    });

    // Character Counter for Textareas
    function setupCharCounter(textareaId, counterId, maxChars = 2000) {
        const textarea = document.getElementById(textareaId);
        const counter = document.getElementById(counterId);
        
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            counter.textContent = `${length} characters`;
            
            if (length > maxChars * 0.9) {
                counter.classList.add('text-yellow-600');
                counter.classList.remove('text-red-600', 'text-gray-500');
            } else if (length >= maxChars) {
                counter.classList.add('text-red-600');
                counter.classList.remove('text-yellow-600', 'text-gray-500');
            } else {
                counter.classList.add('text-gray-500');
                counter.classList.remove('text-yellow-600', 'text-red-600');
            }
        });
    }

    // Setup character counters
    setupCharCounter('jobDescription', 'jobDescCounter', 3000);
    setupCharCounter('qualifications', 'qualificationsCounter', 2000);
    setupCharCounter('howToApply', 'howToApplyCounter', 1500);

    // Preview Post
    document.getElementById('previewBtn').addEventListener('click', function() {
        const form = document.getElementById('jobPostForm');
        const formData = new FormData(form);
        const previewWindow = window.open('', '_blank', 'width=800,height=600');
        
        let previewContent = `
            <html>
            <head>
                <title>Job Post Preview</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; max-width: 800px; margin: 0 auto; }
                    .header { border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 20px; }
                    .section { margin-bottom: 30px; }
                    .label { font-weight: bold; color: #374151; margin-bottom: 5px; }
                    .content { color: #6b7280; margin-bottom: 15px; }
                    .salary { background: #f3f4f6; padding: 10px; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Job Post Preview</h1>
                    <p style="color: #6b7280;">This is how your job post will appear to applicants</p>
                </div>
        `;
        
        for (let [key, value] of formData.entries()) {
            if (value && key !== 'companyLogo') {
                const label = key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
                previewContent += `
                    <div class="section">
                        <div class="label">${label}:</div>
                        <div class="content">${value}</div>
                    </div>
                `;
            }
        }
        
        previewContent += '</body></html>';
        previewWindow.document.write(previewContent);
        previewWindow.document.close();
    });

    // Form Validation
    function validateForm() {
        const requiredFields = ['jobTitle', 'companyName', 'jobType', 'jobCategory', 'location', 'currency', 'deadline', 'contactEmail', 'jobDescription', 'qualifications', 'howToApply'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                input.classList.add('border-red-500', 'ring-red-500');
                input.classList.remove('border-gray-300');
                isValid = false;
            } else {
                input.classList.remove('border-red-500', 'ring-red-500');
                input.classList.add('border-gray-300');
            }
        });
        
        if (!isValid) {
            alert('Please fill in all required fields.');
            return false;
        }
        
        return true;
    }

    // Close mobile menu when clicking on links
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
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
