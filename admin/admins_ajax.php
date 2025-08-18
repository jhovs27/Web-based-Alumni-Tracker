<?php
session_start();
require_once 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get parameters
    $search = trim($_GET['search'] ?? '');
    $per_page = intval($_GET['per_page'] ?? 10);
    $page = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * $per_page;
    $sort_id = strtolower(trim($_GET['sort_id'] ?? 'desc'));
    $status = trim($_GET['status'] ?? '');

    // Build query conditions
    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($status !== '') {
        $where[] = "status = ?";
        $params[] = $status;
    }

    $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Count total for pagination
    $count_sql = "SELECT COUNT(*) FROM admins $where_sql";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetchColumn();
    $total_pages = max(1, ceil($total / $per_page));

    // Sort by id asc/desc
    $order_by = ($sort_id === 'asc') ? 'id ASC' : 'id DESC';

    // Fetch paginated admins
    $sql = "SELECT id, name, email, status, created_at FROM admins $where_sql ORDER BY $order_by LIMIT $per_page OFFSET $offset";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate table HTML
    $html = '';
    if (empty($admins)) {
        $html = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-400">No admin users found.</td></tr>';
    } else {
        foreach ($admins as $admin) {
            $status_class = $admin['status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            $status_text = $admin['status'] === 'Active' ? 'Active' : 'Inactive';
            $toggle_class = $admin['status'] === 'Active' ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 'bg-green-50 text-green-700 hover:bg-green-100';
            $toggle_icon = $admin['status'] === 'Active' ? 'fa-user-slash' : 'fa-user-check';
            $toggle_title = $admin['status'] === 'Active' ? 'Set Inactive' : 'Set Active';

            $html .= '<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($admin['id']) . '</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">' . htmlspecialchars($admin['name']) . '</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($admin['email']) . '</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-block px-3 py-1 rounded-full ' . $status_class . ' text-xs font-bold uppercase">' . $status_text . '</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . htmlspecialchars($admin['created_at']) . '</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center flex gap-2 justify-center items-center">
                            <button class="text-blue-600 hover:text-blue-900 font-semibold mr-1 p-2 rounded-full bg-blue-50 hover:bg-blue-100 transition" title="Edit" onclick="openAdminModal(' . $admin['id'] . ', \'' . htmlspecialchars(addslashes($admin['name'])) . '\', \'' . htmlspecialchars(addslashes($admin['email'])) . '\')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="toggleAdminStatus(' . $admin['id'] . ')"
                                   class="font-semibold p-2 rounded-full transition ' . $toggle_class . '"
                                   title="' . $toggle_title . '">
                                <i class="fas ' . $toggle_icon . '"></i>
                            </button>
                        </td>
                    </tr>';
        }
    }

    // Generate pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = $i == $page ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-blue-100';
            $pagination_html .= '<a href="#" onclick="loadPage(' . $i . ')" class="px-3 py-1 rounded ' . $active_class . ' transition text-sm">' . $i . '</a>';
        }
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'pagination' => $pagination_html,
        'total_records' => $total,
        'current_page' => $page,
        'total_pages' => $total_pages,
        'showing_from' => min($total, $offset + 1),
        'showing_to' => min($offset + $per_page, $total)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
