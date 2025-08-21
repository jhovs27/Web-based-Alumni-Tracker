<?php
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
include 'includes/breadcrumb.php';
include 'config/database.php';

// Set current page for sidebar highlighting
$current_page = 'manage-posts';

// Get job post ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch job post details using PDO prepared statement
$stmt = $conn->prepare("SELECT * FROM job_posts WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    $_SESSION['error'] = "Job post not found!";
    header("Location: manage-posts.php");
    exit();
}

// Get job categories from database or define statically
$categories = [
    'IT', 'Education', 'Healthcare', 'Engineering', 'Business', 'Finance', 'Arts', 'Science', 'Other'
];
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

/* Modern Form Styling */
.form-container {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.form-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    padding: 2rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.form-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

.form-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 2;
}

.form-header p {
    opacity: 0.9;
    font-size: 1.1rem;
    position: relative;
    z-index: 2;
}

.header-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    position: relative;
    z-index: 2;
}

.header-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.header-btn-secondary {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.header-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.header-btn-outline {
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.header-btn-outline:hover {
    background: white;
    color: #1e40af;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.form-content {
    padding: 2.5rem;
}

.form-section {
    background: #f8fafc;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.form-section:hover {
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.section-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.section-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.form-control:hover {
    border-color: #9ca3af;
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.currency-input-wrapper {
    position: relative;
}

.currency-symbol {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-weight: 600;
    z-index: 2;
}

.currency-input {
    padding-left: 2.5rem;
}

.logo-upload-section {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    border: 2px dashed #d1d5db;
    transition: all 0.3s ease;
}

.logo-upload-section:hover {
    border-color: #3b82f6;
    background: #f8fafc;
}

.current-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    padding: 0.5rem;
    background: white;
}

.upload-info {
    flex: 1;
}

.upload-info h4 {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.upload-info p {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.file-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-input:hover {
    border-color: #3b82f6;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 2rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    margin: 0 -2.5rem -2.5rem -2.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-cancel {
    background: white;
    color: #6b7280;
    border: 2px solid #e5e7eb;
}

.btn-cancel:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

/* Alert Styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
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
    border: 1px solid #fca5a5;
}

.alert-icon {
    font-size: 1.2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-content {
        padding: 1.5rem;
    }
    
    .form-section {
        padding: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .header-actions {
        flex-direction: column;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .logo-upload-section {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 1rem;
        padding-top: 8rem;
    }
    
    .form-header {
        padding: 1.5rem;
    }
    
    .form-header h1 {
        font-size: 1.5rem;
    }
}
</style>

<!-- Main Content -->
<div class="main-content">
    <div class="p-6">
        <?php
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php'],
            ['title' => 'Manage Posts', 'url' => 'manage-posts.php'],
            ['title' => 'Edit Job Post', 'url' => '']
        ];
        renderBreadcrumb($breadcrumbs);
        ?>

        <!-- Success Message -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle alert-icon"></i>
                <span><?php echo $_SESSION['success']; ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span><?php echo $_SESSION['error']; ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="form-container">
            <!-- Header -->
            <div class="form-header">
                <h1><i class="fas fa-edit"></i> Edit Job Post</h1>
                <p>Update job posting details and requirements</p>
                <div class="header-actions">
                    <a href="view-post.php?id=<?php echo $job['id']; ?>" class="header-btn header-btn-secondary">
                        <i class="fas fa-eye"></i>View Post
                    </a>
                    <a href="manage-posts.php" class="header-btn header-btn-outline">
                        <i class="fas fa-arrow-left"></i>Back to List
                    </a>
                </div>
            </div>

            <!-- Form Content -->
            <div class="form-content">
                <form action="job_post_actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">

                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="section-title">Basic Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="jobTitle" class="form-label">Job Title</label>
                                <input type="text" name="jobTitle" id="jobTitle" value="<?php echo htmlspecialchars($job['job_title']); ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="companyName" class="form-label">Company Name</label>
                                <input type="text" name="companyName" id="companyName" value="<?php echo htmlspecialchars($job['company_name']); ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="jobType" class="form-label">Job Type</label>
                                <select name="jobType" id="jobType" required class="form-control">
                                    <option value="Full-time" <?php echo $job['job_type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                    <option value="Part-time" <?php echo $job['job_type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                    <option value="Contract" <?php echo $job['job_type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                    <option value="Internship" <?php echo $job['job_type'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="jobCategory" class="form-label">Job Category</label>
                                <select name="jobCategory" id="jobCategory" required class="form-control">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category; ?>" <?php echo $job['job_category'] === $category ? 'selected' : ''; ?>>
                                            <?php echo $category; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Company Logo Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-image"></i>
                            </div>
                            <h3 class="section-title">Company Logo</h3>
                        </div>
                        <div class="logo-upload-section">
                            <?php if ($job['company_logo']): ?>
                                <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" alt="Current Logo" class="current-logo">
                            <?php endif; ?>
                            <div class="upload-info">
                                <h4>Update Company Logo</h4>
                                <p>Upload a new logo or leave empty to keep the current one. Recommended size: 200x200px</p>
                                <input type="file" name="companyLogo" id="companyLogo" accept="image/*" class="file-input">
                            </div>
                        </div>
                    </div>

                    <!-- Location and Salary Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3 class="section-title">Location & Compensation</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($job['location']); ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="currency" class="form-label">Currency</label>
                                <select name="currency" id="currency" required class="form-control">
                                    <option value="USD" <?php echo $job['currency'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                    <option value="EUR" <?php echo $job['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                    <option value="GBP" <?php echo $job['currency'] === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                    <option value="JPY" <?php echo $job['currency'] === 'JPY' ? 'selected' : ''; ?>>JPY (¥)</option>
                                    <option value="AUD" <?php echo $job['currency'] === 'AUD' ? 'selected' : ''; ?>>AUD (A$)</option>
                                    <option value="CAD" <?php echo $job['currency'] === 'CAD' ? 'selected' : ''; ?>>CAD (C$)</option>
                                    <option value="CHF" <?php echo $job['currency'] === 'CHF' ? 'selected' : ''; ?>>CHF (Fr)</option>
                                    <option value="CNY" <?php echo $job['currency'] === 'CNY' ? 'selected' : ''; ?>>CNY (¥)</option>
                                    <option value="INR" <?php echo $job['currency'] === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                                    <option value="PHP" <?php echo $job['currency'] === 'PHP' ? 'selected' : ''; ?>>PHP (₱)</option>
                                    <option value="SGD" <?php echo $job['currency'] === 'SGD' ? 'selected' : ''; ?>>SGD (S$)</option>
                                    <option value="AED" <?php echo $job['currency'] === 'AED' ? 'selected' : ''; ?>>AED (د.إ)</option>
                                    <option value="BRL" <?php echo $job['currency'] === 'BRL' ? 'selected' : ''; ?>>BRL (R$)</option>
                                    <option value="MXN" <?php echo $job['currency'] === 'MXN' ? 'selected' : ''; ?>>MXN (Mex$)</option>
                                    <option value="NZD" <?php echo $job['currency'] === 'NZD' ? 'selected' : ''; ?>>NZD (NZ$)</option>
                                    <option value="ZAR" <?php echo $job['currency'] === 'ZAR' ? 'selected' : ''; ?>>ZAR (R)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="salaryMin" class="form-label">Minimum Salary</label>
                                <div class="currency-input-wrapper">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" name="salaryMin" id="salaryMin" value="<?php echo $job['salary_min']; ?>" class="form-control currency-input">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="salaryMax" class="form-label">Maximum Salary</label>
                                <div class="currency-input-wrapper">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" name="salaryMax" id="salaryMax" value="<?php echo $job['salary_max']; ?>" class="form-control currency-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Description Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="section-title">Job Description</h3>
                        </div>
                        <div class="form-group">
                            <label for="jobDescription" class="form-label">Description</label>
                            <textarea name="jobDescription" id="jobDescription" required class="form-control form-textarea"><?php echo htmlspecialchars($job['job_description']); ?></textarea>
                        </div>
                    </div>

                    <!-- Qualifications Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3 class="section-title">Qualifications</h3>
                        </div>
                        <div class="form-group">
                            <label for="qualifications" class="form-label">Required Qualifications</label>
                            <textarea name="qualifications" id="qualifications" required class="form-control form-textarea"><?php echo htmlspecialchars($job['qualifications']); ?></textarea>
                        </div>
                    </div>

                    <!-- How to Apply Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <h3 class="section-title">Application Process</h3>
                        </div>
                        <div class="form-group">
                            <label for="howToApply" class="form-label">How to Apply</label>
                            <textarea name="howToApply" id="howToApply" required class="form-control form-textarea"><?php echo htmlspecialchars($job['how_to_apply']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="jobLink" class="form-label">Application Link (Optional)</label>
                            <input type="url" name="jobLink" id="jobLink" placeholder="https://example.com/apply" value="<?php echo isset($job['job_link']) ? htmlspecialchars($job['job_link']) : ''; ?>" class="form-control">
                            <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">Provide a direct link to the job application page.</p>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-address-book"></i>
                            </div>
                            <h3 class="section-title">Contact Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="contactEmail" class="form-label">Contact Email</label>
                                <input type="email" name="contactEmail" id="contactEmail" value="<?php echo htmlspecialchars($job['contact_email']); ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="contactPhone" class="form-label">Contact Phone</label>
                                <input type="text" name="contactPhone" id="contactPhone" value="<?php echo htmlspecialchars($job['contact_phone']); ?>" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="manage-posts.php" class="btn btn-cancel">
                            <i class="fas fa-times"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Currency symbol updates
const currencySymbols = {
    'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥', 'AUD': 'A$', 'CAD': 'C$',
    'CHF': 'Fr', 'CNY': '¥', 'INR': '₹', 'PHP': '₱', 'SGD': 'S$', 'AED': 'د.إ',
    'BRL': 'R$', 'MXN': 'Mex$', 'NZD': 'NZ$', 'ZAR': 'R'
};

document.getElementById('currency').addEventListener('change', function() {
    const symbol = currencySymbols[this.value];
    document.querySelectorAll('.currency-symbol').forEach(el => {
        el.textContent = symbol;
    });
});

// Initialize currency symbols on page load
document.addEventListener('DOMContentLoaded', function() {
    const currency = document.getElementById('currency').value;
    const symbol = currencySymbols[currency];
    document.querySelectorAll('.currency-symbol').forEach(el => {
        el.textContent = symbol;
    });

    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Session refresh
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
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
