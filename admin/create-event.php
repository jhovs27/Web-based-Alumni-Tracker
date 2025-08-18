<?php
session_start();
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle file uploads
        $poster_image = '';
        $event_document = '';
        
        if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] === 0) {
            $upload_dir = 'uploads/events/posters/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $poster_image = $upload_dir . time() . '_' . basename($_FILES['poster_image']['name']);
            move_uploaded_file($_FILES['poster_image']['tmp_name'], $poster_image);
        }

        if (isset($_FILES['event_document']) && $_FILES['event_document']['error'] === 0) {
            $upload_dir = 'uploads/events/documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $event_document = $upload_dir . time() . '_' . basename($_FILES['event_document']['name']);
            move_uploaded_file($_FILES['event_document']['tmp_name'], $event_document);
        }

        $sql = "INSERT INTO alumni_events (
            event_title, event_description, event_type, event_category,
            start_datetime, end_datetime, physical_address, online_link,
            timezone, poster_image, event_document, registration_required,
            max_attendees, allow_guests, auto_confirm, contact_person,
            contact_email, contact_phone, visibility, status
        ) VALUES (
            :event_title, :event_description, :event_type, :event_category,
            :start_datetime, :end_datetime, :physical_address, :online_link,
            :timezone, :poster_image, :event_document, :registration_required,
            :max_attendees, :allow_guests, :auto_confirm, :contact_person,
            :contact_email, :contact_phone, :visibility, :status
        )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':event_title' => $_POST['event_title'],
            ':event_description' => $_POST['event_description'],
            ':event_type' => $_POST['event_type'],
            ':event_category' => $_POST['event_category'],
            ':start_datetime' => $_POST['start_datetime'],
            ':end_datetime' => $_POST['end_datetime'],
            ':physical_address' => $_POST['physical_address'],
            ':online_link' => $_POST['online_link'],
            ':timezone' => $_POST['timezone'] ?: 'Asia/Manila',
            ':poster_image' => $poster_image,
            ':event_document' => $event_document,
            ':registration_required' => isset($_POST['registration_required']) ? 1 : 0,
            ':max_attendees' => $_POST['max_attendees'] ?: null,
            ':allow_guests' => isset($_POST['allow_guests']) ? 1 : 0,
            ':auto_confirm' => isset($_POST['auto_confirm']) ? 1 : 0,
            ':contact_person' => $_POST['contact_person'],
            ':contact_email' => $_POST['contact_email'],
            ':contact_phone' => $_POST['contact_phone'],
            ':visibility' => $_POST['visibility'],
            ':status' => $_POST['status']
        ]);

        $_SESSION['success'] = "Event created successfully!";
        header("Location: manage-events.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error creating event: " . $e->getMessage();
    }
}

// Include header and other files after processing the form
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/navbar.php';

// Set breadcrumbs for this page
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false],
    ['title' => 'Manage Events', 'url' => 'manage-events.php', 'active' => false],
    ['title' => 'Create Event', 'url' => 'create-event.php', 'active' => true]
];
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-gray-200 rounded-lg mt-14">
        <?php include 'includes/breadcrumb.php'; ?>
        
        <!-- Header with gradient background -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Create New Event</h2>
                    <p class="text-blue-100 mt-1">Fill in the details below to create a new alumni event</p>
                </div>
                <a href="manage-events.php" class="inline-flex items-center px-4 py-2 bg-white/10 text-white rounded-md hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Events
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md animate-fade-in" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-8" id="eventForm">
            <!-- Basic Event Information -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-6">
                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Basic Event Information</h3>
                        <p class="text-sm text-gray-600">Enter the essential details about your event</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="event_title" class="form-label required">Event Title</label>
                        <input type="text" name="event_title" id="event_title" required
                               class="form-input"
                               placeholder="Enter event title">
                        <p class="form-helper">Choose a clear and descriptive title for your event</p>
                    </div>
                    <div class="space-y-2">
                        <label for="event_type" class="form-label required">Event Type</label>
                        <select name="event_type" id="event_type" required class="form-select">
                            <option value="">Select Event Type</option>
                            <option value="Reunion">Reunion</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Webinar">Webinar</option>
                            <option value="Career Fair">Career Fair</option>
                            <option value="Outreach">Outreach</option>
                            <option value="Other">Other</option>
                        </select>
                        <p class="form-helper">Select the type of event you're creating</p>
                    </div>
                    <div class="space-y-2">
                        <label for="event_category" class="form-label">Event Category</label>
                        <select name="event_category" id="event_category" class="form-select">
                            <option value="">Select Category (Optional)</option>
                            <option value="Academic">Academic</option>
                            <option value="Social">Social</option>
                            <option value="Career">Career</option>
                            <option value="Other">Other</option>
                        </select>
                        <p class="form-helper">Choose a category that best describes your event</p>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label for="event_description" class="form-label">Event Description</label>
                        <textarea id="event_description" name="event_description" required class="form-textarea w-full min-h-[120px] resize-y rounded-md border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-3 text-gray-800" placeholder="Describe the event in detail..."></textarea>
                        <p class="form-helper">Include all relevant details about your event</p>
                    </div>
                </div>
            </div>

            <!-- Event Timing and Venue -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-6">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <i class="fas fa-calendar-alt text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Event Timing and Venue</h3>
                        <p class="text-sm text-gray-600">Set when and where your event will take place</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="start_datetime" class="form-label required">Start Date and Time</label>
                        <input type="datetime-local" name="start_datetime" id="start_datetime" required
                               class="form-input">
                        <p class="form-helper">When will your event begin?</p>
                    </div>
                    <div class="space-y-2">
                        <label for="end_datetime" class="form-label required">End Date and Time</label>
                        <input type="datetime-local" name="end_datetime" id="end_datetime" required
                               class="form-input">
                        <p class="form-helper">When will your event end?</p>
                    </div>
                    <div class="space-y-2">
                        <label for="physical_address" class="form-label">Physical Address</label>
                        <input type="text" name="physical_address" id="physical_address"
                               class="form-input"
                               placeholder="Enter venue address">
                        <p class="form-helper">Location of the event (if applicable)</p>
                    </div>
                    <div class="space-y-2">
                        <label for="online_link" class="form-label">Online Link</label>
                        <input type="url" name="online_link" id="online_link"
                               class="form-input"
                               placeholder="Enter online meeting link">
                        <p class="form-helper">URL for online events</p>
                    </div>
                    <div class="space-y-2">
                        <label for="timezone" class="form-label">Timezone</label>
                        <select name="timezone" id="timezone" class="form-select">
                            <option value="Asia/Manila">Philippines (Asia/Manila)</option>
                            <option value="UTC">UTC (Coordinated Universal Time)</option>
                            <option value="America/New_York">Eastern Time (ET)</option>
                            <option value="America/Chicago">Central Time (CT)</option>
                            <option value="America/Denver">Mountain Time (MT)</option>
                            <option value="America/Los_Angeles">Pacific Time (PT)</option>
                            <option value="Europe/London">London (GMT)</option>
                            <option value="Europe/Paris">Paris (CET)</option>
                            <option value="Asia/Tokyo">Tokyo (JST)</option>
                            <option value="Asia/Shanghai">Shanghai (CST)</option>
                            <option value="Asia/Dubai">Dubai (GST)</option>
                            <option value="Australia/Sydney">Sydney (AEST)</option>
                        </select>
                        <p class="form-helper">Select the timezone for your event</p>
                    </div>
                </div>
            </div>

            <!-- Registration Settings -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-6">
                    <div class="bg-purple-100 p-3 rounded-full mr-4">
                        <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Registration Settings</h3>
                        <p class="text-sm text-gray-600">Configure how people can register for your event</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="registration_required" value="1"
                                   class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Require Registration</span>
                        </label>
                        <p class="form-helper">Make registration mandatory for attendance</p>
                    </div>
                    <div class="space-y-2">
                        <label for="max_attendees" class="form-label">Maximum Attendees</label>
                        <input type="number" name="max_attendees" id="max_attendees" min="0"
                               class="form-input"
                               placeholder="Enter maximum number of attendees">
                        <p class="form-helper">Leave empty for unlimited attendees</p>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="allow_guests" value="1"
                                   class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Allow Guests</span>
                        </label>
                        <p class="form-helper">Let participants bring additional guests</p>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="auto_confirm" value="1"
                                   class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Auto-confirm Registrations</span>
                        </label>
                        <p class="form-helper">Automatically approve registration requests</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-6">
                    <div class="bg-yellow-100 p-3 rounded-full mr-4">
                        <i class="fas fa-address-card text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Contact Information</h3>
                        <p class="text-sm text-gray-600">Provide contact details for event inquiries</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="contact_person" class="form-label required">Contact Person</label>
                        <input type="text" name="contact_person" id="contact_person" required
                               class="form-input"
                               placeholder="Enter contact person's name">
                    </div>
                    <div class="space-y-2">
                        <label for="contact_email" class="form-label required">Contact Email</label>
                        <input type="email" name="contact_email" id="contact_email" required
                               class="form-input"
                               placeholder="Enter contact email">
                    </div>
                    <div class="space-y-2">
                        <label for="contact_phone" class="form-label required">Contact Phone</label>
                        <input type="tel" name="contact_phone" id="contact_phone" required
                               class="form-input"
                               placeholder="Enter contact phone number">
                    </div>
                </div>
            </div>

            <!-- Media and Documents -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-6">
                    <div class="bg-pink-100 p-3 rounded-full mr-4">
                        <i class="fas fa-images text-pink-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Media and Documents</h3>
                        <p class="text-sm text-gray-600">Upload event materials and promotional content</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Event Poster Upload -->
                    <div class="space-y-2">
                        <label for="poster_image" class="form-label">Event Poster</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-500 transition-colors duration-200">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="poster_image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="poster_image" name="poster_image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Event Document Upload -->
                    <div class="space-y-2">
                        <label for="event_document" class="form-label">Event Document</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-500 transition-colors duration-200">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-file-upload text-gray-400 text-3xl"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="event_document" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="event_document" name="event_document" type="file" class="sr-only" accept=".pdf,.doc,.docx">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, DOC up to 10MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Settings -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-6">
                    <div class="bg-gray-100 p-3 rounded-full mr-4">
                        <i class="fas fa-cog text-gray-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Event Settings</h3>
                        <p class="text-sm text-gray-600">Configure additional event settings</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="visibility" class="form-label">Visibility</label>
                        <select name="visibility" id="visibility" class="form-select">
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                        </select>
                        <p class="form-helper">Control who can see your event</p>
                    </div>
                    <div class="space-y-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="Draft">Draft</option>
                            <option value="Published">Published</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <p class="form-helper">Set the current status of your event</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
                <a href="manage-events.php" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    Create Event
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add custom styles -->
<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Enhanced Input Field Styles */
    .form-input {
        @apply w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-700;
        @apply focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none;
        @apply transition-all duration-200 ease-in-out;
        @apply placeholder-gray-400;
        @apply hover:border-blue-400;
    }

    .form-input:disabled {
        @apply bg-gray-100 cursor-not-allowed;
    }

    .form-input.error {
        @apply border-red-500 focus:ring-red-500 focus:border-red-500;
    }

    /* Enhanced Select Field Styles */
    .form-select {
        @apply w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-700;
        @apply focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none;
        @apply transition-all duration-200 ease-in-out;
        @apply appearance-none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    .form-select:hover {
        @apply border-blue-400;
    }

    .form-select option {
        @apply py-2 px-4;
    }

    /* Enhanced Textarea Styles */
    .form-textarea {
        @apply w-full px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-700;
        @apply focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none;
        @apply transition-all duration-200 ease-in-out;
        @apply placeholder-gray-400;
        @apply hover:border-blue-400;
        min-height: 120px;
        resize: vertical;
    }

    /* Enhanced File Upload Styles */
    .file-upload-container {
        @apply relative border-2 border-dashed rounded-lg p-8;
        @apply transition-all duration-300 ease-in-out;
        @apply bg-gradient-to-br from-gray-50 to-gray-100;
        min-height: 200px;
    }

    .file-upload-container.dragover {
        @apply border-blue-500 bg-blue-50;
        @apply transform scale-105;
        @apply shadow-lg;
    }

    .file-upload-container:hover {
        @apply border-blue-400;
        @apply shadow-md;
    }

    .file-upload-input {
        @apply absolute inset-0 w-full h-full opacity-0 cursor-pointer;
    }

    .file-upload-label {
        @apply flex flex-col items-center justify-center text-center;
        @apply h-full;
    }

    .file-upload-icon {
        @apply text-5xl text-gray-400 mb-4;
        @apply transition-transform duration-300;
    }

    .file-upload-container:hover .file-upload-icon {
        @apply transform scale-110 text-blue-500;
    }

    .file-upload-text {
        @apply text-lg font-medium text-gray-700 mb-2;
    }

    .file-upload-subtext {
        @apply text-sm text-gray-500;
    }

    .file-preview {
        @apply mt-4 p-4 bg-white rounded-lg shadow-sm;
        @apply border border-gray-200;
    }

    .file-preview-image {
        @apply max-h-48 w-auto mx-auto rounded-lg;
        @apply shadow-md;
    }

    .file-preview-name {
        @apply mt-2 text-sm font-medium text-gray-700 text-center;
    }

    .file-preview-size {
        @apply text-xs text-gray-500 text-center;
    }

    .file-upload-progress {
        @apply mt-4 w-full bg-gray-200 rounded-full h-2.5;
        @apply hidden;
    }

    .file-upload-progress-bar {
        @apply bg-blue-600 h-2.5 rounded-full;
        @apply transition-all duration-300 ease-in-out;
    }

    /* Custom Scrollbar for Dropdowns */
    .form-select::-webkit-scrollbar {
        width: 8px;
    }

    .form-select::-webkit-scrollbar-track {
        @apply bg-gray-100 rounded-full;
    }

    .form-select::-webkit-scrollbar-thumb {
        @apply bg-gray-400 rounded-full;
    }

    .form-select::-webkit-scrollbar-thumb:hover {
        @apply bg-gray-500;
    }

    /* Enhanced Checkbox Styles */
    .form-checkbox {
        @apply w-5 h-5 rounded border-gray-300 text-blue-600;
        @apply focus:ring-2 focus:ring-blue-500 focus:ring-offset-0;
        @apply transition-all duration-200 ease-in-out;
    }

    .form-checkbox:hover {
        @apply border-blue-400;
    }

    /* Enhanced Radio Button Styles */
    .form-radio {
        @apply w-5 h-5 border-gray-300 text-blue-600;
        @apply focus:ring-2 focus:ring-blue-500 focus:ring-offset-0;
        @apply transition-all duration-200 ease-in-out;
    }

    .form-radio:hover {
        @apply border-blue-400;
    }

    /* Enhanced Label Styles */
    .form-label {
        @apply block text-sm font-medium text-gray-700 mb-2;
    }

    .form-label.required:after {
        content: " *";
        @apply text-red-500;
    }

    /* Enhanced Helper Text Styles */
    .form-helper {
        @apply mt-1 text-sm text-gray-500;
    }

    /* Enhanced Error Text Styles */
    .form-error {
        @apply mt-1 text-sm text-red-600;
    }

    /* Input Group Styles */
    .input-group {
        @apply relative flex items-center;
    }

    .input-group .form-input {
        @apply pr-10;
    }

    .input-group-icon {
        @apply absolute right-3 text-gray-400;
    }

    /* Enhanced Date/Time Input Styles */
    input[type="datetime-local"] {
        @apply form-input;
    }

    /* Enhanced Number Input Styles */
    input[type="number"] {
        @apply form-input;
    }

    /* Enhanced URL Input Styles */
    input[type="url"] {
        @apply form-input;
    }

    /* Enhanced Email Input Styles */
    input[type="email"] {
        @apply form-input;
    }

    /* Enhanced Tel Input Styles */
    input[type="tel"] {
        @apply form-input;
    }
</style>

<!-- Add JavaScript for enhanced interactivity -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const container = this.closest('.border-dashed');
                const label = container.querySelector('.space-y-1');
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        label.innerHTML = `
                            <img src="${e.target.result}" class="mx-auto h-32 w-auto object-cover rounded-lg mb-4">
                            <div class="flex text-sm text-gray-600">
                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Change file</span>
                                    <input type="file" class="sr-only" accept="${input.accept}">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">${file.name}</p>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    label.innerHTML = `
                        <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
                        <div class="flex text-sm text-gray-600">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Change file</span>
                                <input type="file" class="sr-only" accept="${input.accept}">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">${file.name}</p>
                    `;
                }
            }
        });
    });

    // Drag and drop functionality
    const dropZones = document.querySelectorAll('.border-dashed');
    
    dropZones.forEach(zone => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('border-blue-500');
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove('border-blue-500');
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('border-blue-500');
            const file = e.dataTransfer.files[0];
            const input = zone.querySelector('input[type="file"]');
            if (input) {
                input.files = e.dataTransfer.files;
                const event = new Event('change');
                input.dispatchEvent(event);
            }
        });
    });
});
</script>

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

<?php require_once 'includes/footer.php'; ?> 