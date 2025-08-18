<?php
session_start();
require_once 'config/database.php';

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
include 'includes/breadcrumb.php';

// Set current page for sidebar highlighting
$current_page = 'create-posts';

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

<style>
/* Main content spacing for fixed navbar and sidebar */
.main-content {
    margin-left: 0;
    width: 100%;
    padding-top: 10rem;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

@media (min-width: 1024px) {
    .main-content {
        margin-left: 16rem;
        width: calc(100% - 16rem);
        padding-top: 8rem;
    }
}

/* Enhanced Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: #1F2937;
    font-size: 0.95rem;
    letter-spacing: 0.025em;
}

.form-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    background-color: #FFFFFF;
    color: #374151;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.form-input:hover {
    border-color: #D1D5DB;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.form-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

/* Enhanced Textarea Styles */
.form-input[type="textarea"], 
textarea.form-input {
    min-height: 150px;
    resize: vertical;
    line-height: 1.6;
    font-family: inherit;
}

/* Enhanced Select Styles */
select.form-input {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    appearance: none;
}

select.form-input:focus {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%233b82f6' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
}

/* Enhanced File Input Styles */
.file-input-wrapper {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.file-input-wrapper input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-input-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.25rem;
    background: linear-gradient(135deg, #F3F4F6 0%, #E5E7EB 100%);
    border: 2px solid #E5E7EB;
    border-radius: 0.75rem;
    font-weight: 500;
    color: #374151;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-input-label:hover {
    background: linear-gradient(135deg, #E5E7EB 0%, #D1D5DB 100%);
    border-color: #D1D5DB;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Enhanced Radio Button Styles */
.form-radio {
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid #D1D5DB;
    border-radius: 50%;
    background-color: #FFFFFF;
    transition: all 0.3s ease;
    cursor: pointer;
}

.form-radio:checked {
    border-color: #3B82F6;
    background-color: #3B82F6;
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
    background-size: 0.75rem 0.75rem;
    background-position: center;
    background-repeat: no-repeat;
}

.form-radio:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

/* Enhanced Button Styles */
.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #4B5563 0%, #374151 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
}

/* Form Layout Enhancements */
.form-section {
    background: #FFFFFF;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #E5E7EB;
}

.form-section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #F3F4F6;
}

/* Long Text Fields Enhancement */
.long-text-field {
    margin-bottom: 2rem;
}

.long-text-field .form-input {
    min-height: 180px;
    font-size: 1rem;
    line-height: 1.7;
}

.long-text-field .form-label {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1rem;
}

/* Character Counter */
.char-counter {
    font-size: 0.875rem;
    color: #6B7280;
    margin-top: 0.5rem;
    text-align: right;
}

.char-counter.near-limit {
    color: #F59E0B;
}

.char-counter.at-limit {
    color: #EF4444;
}
</style>

<!-- Main Content -->
<div class="main-content min-h-screen transition-all duration-300">
    <div class="p-6">
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Manage Posts', 'url' => 'manage-posts.php'],
            ['title' => 'Create Job Post', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>
        
        <div class="form-section">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-plus-circle text-blue-600"></i>
                    Create Job Post
                </h1>
            </div>

            <!-- Form -->
            <form id="jobPostForm" action="job_post_actions.php" method="POST" enctype="multipart/form-data" class="space-y-8" onsubmit="return validateForm()">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h2 class="form-section-title flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        Basic Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Job Title -->
                        <div class="form-group">
                            <label for="jobTitle" class="form-label">Job Title <span class="text-red-500">*</span></label>
                            <input type="text" id="jobTitle" name="jobTitle" class="form-input" required placeholder="e.g., Junior Software Developer">
                        </div>
                        <!-- Company Name -->
                        <div class="form-group">
                            <label for="companyName" class="form-label">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" id="companyName" name="companyName" class="form-input" required placeholder="e.g., TechNova Inc.">
                        </div>
                        <!-- Company Logo -->
                        <div class="form-group">
                            <label for="companyLogo" class="form-label">Company Logo</label>
                            <div class="mt-1 flex items-center gap-3">
                                <input type="file" id="companyLogo" name="companyLogo" class="hidden" accept="image/*">
                                <label for="companyLogo" class="file-input-label">
                                    <i class="fas fa-upload"></i>
                                    Choose File
                                </label>
                                <span id="selectedFileName" class="text-sm text-gray-500">No file chosen</span>
                            </div>
                        </div>
                        <!-- Job Type -->
                        <div class="form-group">
                            <label for="jobType" class="form-label">Job Type <span class="text-red-500">*</span></label>
                            <select id="jobType" name="jobType" class="form-input" required>
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
                        <div class="form-group">
                            <label for="jobCategory" class="form-label">Job Category <span class="text-red-500">*</span></label>
                            <select id="jobCategory" name="jobCategory" class="form-input" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Location -->
                        <div class="form-group">
                            <label for="location" class="form-label">Location <span class="text-red-500">*</span></label>
                            <input type="text" id="location" name="location" class="form-input" required placeholder="e.g., Manila, Philippines or Remote">
                        </div>
                    </div>
                </div>

                <!-- Compensation Section -->
                <div class="form-section">
                    <h2 class="form-section-title flex items-center gap-2">
                        <i class="fas fa-dollar-sign text-green-600"></i>
                        Compensation & Benefits
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Currency -->
                        <div class="form-group">
                            <label for="currency" class="form-label">Currency <span class="text-red-500">*</span></label>
                            <select id="currency" name="currency" class="form-input" required>
                                <option value="">Select Currency</option>
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="JPY">JPY (¥)</option>
                                <option value="AUD">AUD (A$)</option>
                                <option value="CAD">CAD (C$)</option>
                                <option value="CHF">CHF (Fr)</option>
                                <option value="CNY">CNY (¥)</option>
                                <option value="INR">INR (₹)</option>
                                <option value="PHP">PHP (₱)</option>
                                <option value="SGD">SGD (S$)</option>
                                <option value="AED">AED (د.إ)</option>
                                <option value="BRL">BRL (R$)</option>
                                <option value="MXN">MXN (Mex$)</option>
                                <option value="NZD">NZD (NZ$)</option>
                                <option value="ZAR">ZAR (R)</option>
                            </select>
                        </div>
                        <!-- Salary Range -->
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Salary Range</label>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="number" id="salaryMin" name="salaryMin" class="form-input" placeholder="Minimum">
                                <input type="number" id="salaryMax" name="salaryMax" class="form-input" placeholder="Maximum">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact & Deadline Section -->
                <div class="form-section">
                    <h2 class="form-section-title flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-purple-600"></i>
                        Contact & Deadline
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Application Deadline -->
                        <div class="form-group">
                            <label for="deadline" class="form-label">Application Deadline <span class="text-red-500">*</span></label>
                            <input type="date" id="deadline" name="deadline" class="form-input" required>
                        </div>
                        <!-- Contact Email -->
                        <div class="form-group">
                            <label for="contactEmail" class="form-label">Contact Email <span class="text-red-500">*</span></label>
                            <input type="email" id="contactEmail" name="contactEmail" class="form-input" required placeholder="e.g., careers@company.com">
                        </div>
                        <!-- Contact Phone -->
                        <div class="form-group">
                            <label for="contactPhone" class="form-label">Contact Phone</label>
                            <input type="text" id="contactPhone" name="contactPhone" class="form-input" placeholder="e.g., +63 912 345 6789">
                        </div>
                    </div>
                </div>

                <!-- Job Details Section -->
                <div class="form-section">
                    <h2 class="form-section-title flex items-center gap-2">
                        <i class="fas fa-file-alt text-indigo-600"></i>
                        Job Details
                    </h2>
                    
                    <!-- Job Description -->
                    <div class="long-text-field">
                        <label for="jobDescription" class="form-label">Job Description <span class="text-red-500">*</span></label>
                        <textarea id="jobDescription" name="jobDescription" class="form-input" required placeholder="Enter detailed job description including responsibilities, duties, and what the role entails..."></textarea>
                        <div class="char-counter">
                            <span id="jobDescCounter">0</span> characters
                        </div>
                    </div>
                    
                    <!-- Qualifications -->
                    <div class="long-text-field">
                        <label for="qualifications" class="form-label">Qualifications <span class="text-red-500">*</span></label>
                        <textarea id="qualifications" name="qualifications" class="form-input" required placeholder="Enter required qualifications, skills, experience, and educational requirements..."></textarea>
                        <div class="char-counter">
                            <span id="qualificationsCounter">0</span> characters
                        </div>
                    </div>
                    
                    <!-- How to Apply -->
                    <div class="long-text-field">
                        <label for="howToApply" class="form-label">How to Apply <span class="text-red-500">*</span></label>
                        <textarea id="howToApply" name="howToApply" class="form-input" required placeholder="Enter detailed application instructions, required documents, and submission process..."></textarea>
                        <div class="char-counter">
                            <span id="howToApplyCounter">0</span> characters
                        </div>
                    </div>
                    
                    <!-- Job Link -->
                    <div class="form-group">
                        <label for="jobLink" class="form-label">Job Link (URL)</label>
                        <input type="url" id="jobLink" name="jobLink" class="form-input" placeholder="https://example.com/apply">
                        <p class="mt-2 text-sm text-gray-600">Optional: Provide a direct link to the job application page.</p>
                    </div>
                </div>

                <!-- Post Status Section -->
                <div class="form-section">
                    <h2 class="form-section-title flex items-center gap-2">
                        <i class="fas fa-cog text-gray-600"></i>
                        Post Settings
                    </h2>
                    <div class="form-group">
                        <label class="form-label">Post Status</label>
                        <div class="flex items-center space-x-6">
                            <label class="inline-flex items-center">
                                <input type="radio" name="postStatus" value="draft" class="form-radio" checked>
                                <span class="ml-3 font-medium text-gray-700">Draft</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="postStatus" value="published" class="form-radio">
                                <span class="ml-3 font-medium text-gray-700">Published</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <button type="button" id="previewBtn" class="btn btn-secondary">
                        <i class="fas fa-eye"></i>Preview
                    </button>
                    <button type="submit" name="create_job_post" value="draft" class="btn btn-secondary">
                        <i class="fas fa-save"></i>Save as Draft
                    </button>
                    <button type="submit" name="create_job_post" value="published" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>Submit Job Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// File Upload Preview
document.getElementById('companyLogo').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'No file chosen';
    document.getElementById('selectedFileName').textContent = fileName;
});

// Character Counter for Textareas
function setupCharCounter(textareaId, counterId, maxChars = 2000) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    const counterElement = counter.parentElement;
    
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        counter.textContent = length;
        
        if (length > maxChars * 0.9) {
            counterElement.classList.add('near-limit');
            counterElement.classList.remove('at-limit');
        } else if (length >= maxChars) {
            counterElement.classList.add('at-limit');
            counterElement.classList.remove('near-limit');
        } else {
            counterElement.classList.remove('near-limit', 'at-limit');
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
    const previewWindow = window.open('', '_blank');
    previewWindow.document.write('<html><head><title>Job Post Preview</title><style>body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }</style></head><body>');
    previewWindow.document.write('<h1>Job Post Preview</h1>');
    for (let [key, value] of formData.entries()) {
        if (value) {
            previewWindow.document.write(`<p><strong>${key}:</strong> ${value}</p>`);
        }
    }
    previewWindow.document.write('</body></html>');
});

function validateForm() {
    const requiredFields = ['jobTitle', 'companyName', 'jobType', 'jobCategory', 'location', 'currency', 'deadline', 'contactEmail', 'jobDescription', 'qualifications', 'howToApply'];
    let isValid = true;

    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.classList.add('border-red-500');
            isValid = false;
        } else {
            input.classList.remove('border-red-500');
        }
    });

    if (!isValid) {
        alert('Please fill in all required fields.');
        return false;
    }

    return true;
}

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