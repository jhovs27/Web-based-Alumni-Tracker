<?php
require_once 'includes/session_config.php';
if (!isChairSessionValid()) {
    header('Location: ../login.php');
    exit();
}

include 'includes/navbar.php';
include 'includes/sidebar.php';

// Breadcrumbs for this page
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'],
    ['label' => 'Email Management', 'icon' => 'fa-envelope'],
];

// Handle form submission
$success = null;
$error = null;
if (isset($_POST['send_email'])) {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($subject && $message) {
        require_once '../admin/config/database.php';
        try {
            $stmt = $conn->query("SELECT email FROM alumni WHERE email IS NOT NULL AND email != ''");
            $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $sent = 0;
            foreach ($emails as $to) {
                // For real use, replace with PHPMailer for better reliability
                if (mail($to, $subject, $message)) {
                    $sent++;
                }
            }
            $success = "Announcement sent to $sent alumni.";
        } catch (Exception $e) {
            $error = "Failed to send emails: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "Please fill in both subject and message.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Management | SLSU-HC Chair Panel</title>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0V8a4 4 0 00-8 0v4m8 0v4a4 4 0 01-8 0v-4" />
                        </svg>
                        Email Management
                    </h1>
                    <p class="text-gray-600 mb-6">Send announcements, notifications, or updates to alumni via email.</p>
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
                            <label class="block font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" name="subject" required class="mt-1 block w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Message</label>
                            <textarea name="message" rows="6" required class="mt-1 block w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="send_email" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Send Announcement</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
