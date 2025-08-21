<?php
require_once 'includes/session_config.php';

// Check if admin session is valid
if (!isAdminSessionValid()) {
    header('Location: ../login.php');
    exit;
}

include 'config/database.php';

// Handle payment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'update_status':
                $alumni_id = $_POST['alumni_id'];
                $new_status = $_POST['status'];
                
                $stmt = $conn->prepare("UPDATE payment SET status = ? WHERE alumni_id = ?");
                $stmt->execute([$new_status, $alumni_id]);
                
                $_SESSION['message'] = 'Payment status updated successfully.';
                $_SESSION['message_type'] = 'success';
                break;
                
            case 'delete_payment':
                $alumni_id = $_POST['alumni_id'];
                
                $stmt = $conn->prepare("DELETE FROM payment WHERE alumni_id = ?");
                $stmt->execute([$alumni_id]);
                
                $_SESSION['message'] = 'Payment record deleted successfully.';
                $_SESSION['message_type'] = 'success';
                break;
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    header("Location: payment.php");
    exit;
}

// Get all payments with alumni information
$stmt = $conn->prepare("
    SELECT 
        p.*,
        CONCAT(a.first_name, ' ', a.last_name) as NAME
    FROM payment p
    LEFT JOIN alumni a ON p.alumni_id = a.alumni_id
");
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header and navigation
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>

<div class="ml-64 p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Payment Management</h1>
        <!-- <p class="text-gray-600">Manage and monitor all alumni payments</p> -->
    </div>

    <!-- Payment Management Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Payment Records</h2>
            <div class="flex space-x-2">
                <button onclick="printPayments()" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-600 transition-colors">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alumni ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAME</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Option</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof Document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No payment records found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($payment['alumni_id'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($payment['NAME'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($payment['payment_option'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($payment['reference_number'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if (!empty($payment['proof_document'])): ?>
                                        <a href="<?php echo htmlspecialchars($payment['proof_document']); ?>" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-900 underline">
                                            View Document
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">No document</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusClass($payment['status'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($payment['status'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo isset($payment['created_at']) ? date('M d, Y H:i', strtotime($payment['created_at'])) : 'N/A'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="openEditModal('<?php echo $payment['alumni_id']; ?>', '<?php echo htmlspecialchars(addslashes($payment['status'])); ?>')" 
                                                class="text-blue-600 hover:text-blue-900" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="deletePayment('<?php echo $payment['alumni_id']; ?>')" 
                                                class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Update Payment Status</h3>
            <form id="updateStatusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="alumni_id" id="updateAlumniId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="statusSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Pending Payment">Pending Payment</option>
                        <option value="Under Review">Under Review</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUpdateModal()" 
                            class="px-4 py-2 text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div id="editStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Payment Status</h3>
            <form id="editStatusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="alumni_id" id="editAlumniId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="editStatusSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Pending Payment">Pending Payment</option>
                        <option value="Under Review">Under Review</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function getStatusClass(status) {
    switch(status) {
        case 'Confirmed': return 'bg-green-100 text-green-800';
        case 'Pending Payment': return 'bg-yellow-100 text-yellow-800';
        case 'Under Review': return 'bg-blue-100 text-blue-800';
        case 'Rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function updatePaymentStatus(alumniId, currentStatus) {
    document.getElementById('updateAlumniId').value = alumniId;
    document.getElementById('statusSelect').value = currentStatus;
    document.getElementById('updateStatusModal').classList.remove('hidden');
}

function closeUpdateModal() {
    document.getElementById('updateStatusModal').classList.add('hidden');
}

function deletePayment(alumniId) {
    if (confirm('Are you sure you want to delete this payment record?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_payment">
            <input type="hidden" name="alumni_id" value="${alumniId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function printPayments() {
    window.print();
}

function openEditModal(alumniId, currentStatus) {
    document.getElementById('editAlumniId').value = alumniId;
    document.getElementById('editStatusSelect').value = currentStatus;
    document.getElementById('editStatusModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editStatusModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('updateStatusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUpdateModal();
    }
});
document.getElementById('editStatusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 