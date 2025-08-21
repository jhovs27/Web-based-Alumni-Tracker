<?php
require_once 'includes/session_config.php';
if (!isChairSessionValid()) {
    header('Location: ../login.php');
    exit();
}

include 'includes/navbar.php';
include 'includes/sidebar.php';

require_once '../admin/config/database.php';
// Fetch school years and alumni for selection
$school_years = [];
$alumni_list = [];
try {
    $stmt = $conn->query("SELECT DISTINCT graduation_year FROM alumni WHERE graduation_year IS NOT NULL AND graduation_year != '' ORDER BY graduation_year DESC");
    $school_years = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name, ' (', email, ')') AS name, phone FROM alumni WHERE phone IS NOT NULL AND phone != '' ORDER BY last_name, first_name");
    $alumni_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Handle form submission
$success = null;
$error = null;
if (isset($_POST['send_sms'])) {
    $message = trim($_POST['message'] ?? '');
    $recipient_type = $_POST['recipient_type'] ?? 'all';
    $selected_years = $_POST['school_years'] ?? [];
    $selected_alumni = $_POST['alumni'] ?? [];
    $phones = [];
    if ($message) {
        try {
            if ($recipient_type === 'all') {
                $stmt = $conn->query("SELECT phone FROM alumni WHERE phone IS NOT NULL AND phone != ''");
                $phones = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } elseif ($recipient_type === 'year' && !empty($selected_years)) {
                $in = str_repeat('?,', count($selected_years) - 1) . '?';
                $stmt = $conn->prepare("SELECT phone FROM alumni WHERE graduation_year IN ($in) AND phone IS NOT NULL AND phone != ''");
                $stmt->execute($selected_years);
                $phones = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } elseif ($recipient_type === 'individual' && !empty($selected_alumni)) {
                $in = str_repeat('?,', count($selected_alumni) - 1) . '?';
                $stmt = $conn->prepare("SELECT phone FROM alumni WHERE id IN ($in) AND phone IS NOT NULL AND phone != ''");
                $stmt->execute($selected_alumni);
                $phones = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
            $sent = 0;
            foreach ($phones as $to) {
                // Placeholder for SMS sending logic
                $sent++;
            }
            $success = "SMS message sent to $sent alumni (simulation).";
        } catch (Exception $e) {
            $error = "Failed to send SMS: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "Please enter a message.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Management | SLSU-HC Chair Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
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
            <div class="max-w-3xl mx-auto">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-8 border border-blue-100">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2 flex items-center">
                        <svg class="w-7 h-7 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V10a2 2 0 012-2h2m10 0V6a4 4 0 00-8 0v2m8 0H7" />
                        </svg>
                        SMS Management
                    </h1>
                    <p class="text-gray-600 mb-6">Send SMS announcements or notifications to alumni. (Simulation only)</p>
                    <?php if ($success): ?>
                        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4 border border-green-200">
                            <i class="fa fa-check-circle mr-2"></i> <?php echo $success; ?>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4 border border-red-200">
                            <i class="fa fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" class="space-y-6">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Recipients</label>
                            <div class="flex flex-col gap-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="recipient_type" value="all" class="form-radio text-blue-600" <?php if (empty($_POST['recipient_type']) || $_POST['recipient_type']==='all') echo 'checked'; ?>>
                                    <span class="ml-2">All Alumni</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="recipient_type" value="year" class="form-radio text-blue-600" <?php if (!empty($_POST['recipient_type']) && $_POST['recipient_type']==='year') echo 'checked'; ?>>
                                    <span class="ml-2">By School Year</span>
                                </label>
                                <div class="ml-6" id="schoolYearSelect" style="display:<?php echo (!empty($_POST['recipient_type']) && $_POST['recipient_type']==='year') ? 'block':'none'; ?>;">
                                    <select name="school_years[]" multiple class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                                        <?php foreach ($school_years as $year): ?>
                                            <option value="<?php echo htmlspecialchars($year); ?>" <?php if (!empty($_POST['school_years']) && in_array($year, $_POST['school_years'])) echo 'selected'; ?>><?php echo htmlspecialchars($year); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-gray-500">Hold Ctrl (Windows) or Cmd (Mac) to select multiple years.</small>
                                </div>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="recipient_type" value="individual" class="form-radio text-blue-600" <?php if (!empty($_POST['recipient_type']) && $_POST['recipient_type']==='individual') echo 'checked'; ?>>
                                    <span class="ml-2">Specific Individual(s)</span>
                                </label>
                                <div class="ml-6" id="alumniSelect" style="display:<?php echo (!empty($_POST['recipient_type']) && $_POST['recipient_type']==='individual') ? 'block':'none'; ?>;">
                                    <select name="alumni[]" multiple class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                                        <?php foreach ($alumni_list as $alum): ?>
                                            <option value="<?php echo $alum['id']; ?>" <?php if (!empty($_POST['alumni']) && in_array($alum['id'], $_POST['alumni'])) echo 'selected'; ?>><?php echo htmlspecialchars($alum['name']) . ' - ' . htmlspecialchars($alum['phone']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-gray-500">Type to search or scroll to select alumni.</small>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Message</label>
                            <textarea name="message" rows="5" maxlength="320" required class="mt-1 block w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your SMS message (max 320 characters)"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="send_sms" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Send SMS</button>
                    </form>
                    <script>
                    // Show/hide recipient selectors
                    document.addEventListener('DOMContentLoaded', function() {
                        const radios = document.querySelectorAll('input[name=recipient_type]');
                        const yearSelect = document.getElementById('schoolYearSelect');
                        const alumniSelect = document.getElementById('alumniSelect');
                        radios.forEach(radio => {
                            radio.addEventListener('change', function() {
                                if (this.value === 'year') {
                                    yearSelect.style.display = 'block';
                                    alumniSelect.style.display = 'none';
                                } else if (this.value === 'individual') {
                                    yearSelect.style.display = 'none';
                                    alumniSelect.style.display = 'block';
                                } else {
                                    yearSelect.style.display = 'none';
                                    alumniSelect.style.display = 'none';
                                }
                            });
                        });
                    });
                    </script>
                </div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
