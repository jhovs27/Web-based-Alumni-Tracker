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
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $program_filter = isset($_GET['program_filter']) ? $_GET['program_filter'] : '';
    $status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
    $entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $entries_per_page;

    // Build query conditions
    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }

    if (!empty($program_filter)) {
        $where_conditions[] = "program = ?";
        $params[] = $program_filter;
    }

    if (!empty($status_filter)) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Get total records for pagination
    $count_query = "SELECT COUNT(*) as total FROM program_chairs $where_clause";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $entries_per_page);

    // Fetch program chairs with pagination
    $query = "SELECT pc.*, d.DepartmentName, d.Description, d.Designation
              FROM program_chairs pc
              JOIN department d ON pc.program = d.id
              $where_clause
              ORDER BY pc.created_at DESC
              LIMIT $offset, $entries_per_page";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $chairs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate table HTML
    $html = '';
    if (empty($chairs)) {
        $html = '<tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-2 block"></i>
                        No Program Chairs found
                    </td>
                </tr>';
    } else {
        foreach ($chairs as $chair) {
            $profile_img = '';
            if ($chair['profile_picture']) {
                $profile_img = '<img class="h-10 w-10 rounded-full object-cover" 
                                    src="chair-uploads/' . htmlspecialchars($chair['profile_picture']) . '" 
                                    alt="Profile">';
            } else {
                $profile_img = '<div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>';
            }

            $status_class = $chair['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            $toggle_class = $chair['status'] === 'Active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900';
            $toggle_icon = $chair['status'] === 'Active' ? 'fa-user-slash' : 'fa-user-check';
            $toggle_title = $chair['status'] === 'Active' ? 'Set Inactive' : 'Set Active';

            $html .= '<tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">' . $profile_img . '</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">' . htmlspecialchars($chair['full_name']) . '</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">' . htmlspecialchars($chair['email']) . '</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">' . htmlspecialchars($chair['username']) . '</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ' . htmlspecialchars(($chair['DepartmentName'] ?? '') . ' - ' . ($chair['Description'] ?? '')) . '
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ' . htmlspecialchars($chair['Designation'] ?? '') . '
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ' . $status_class . '">
                                ' . ($chair['status'] === 'Active' ? 'Active' : 'Inactive') . '
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <button onclick="viewProfile(' . htmlspecialchars(json_encode($chair)) . ')"
                                         class="text-blue-600 hover:text-blue-900" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editChair(' . htmlspecialchars(json_encode($chair)) . ')"
                                         class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleStatus(' . $chair['id'] . ', \'' . $chair['status'] . '\')"
                                         class="' . $toggle_class . '"
                                         title="' . $toggle_title . '">
                                    <i class="fas ' . $toggle_icon . '"></i>
                                </button>
                            </div>
                        </td>
                    </tr>';
        }
    }

    // Generate pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = $i == $current_page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700';
            $pagination_html .= '<a href="#" onclick="loadPage(' . $i . ')" class="px-3 py-1 rounded ' . $active_class . '">' . $i . '</a>';
        }
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'pagination' => $pagination_html,
        'total_records' => $total_records,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'showing_from' => min($total_records, $offset + 1),
        'showing_to' => min($offset + $entries_per_page, $total_records)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
