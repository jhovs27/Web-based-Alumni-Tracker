<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get parameters
$search = isset($_POST['search']) ? $_POST['search'] : '';
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Build WHERE clause for search
    $where = '1=1';
    $params = [];
    if ($search !== '') {
        $where .= " AND (s.StudentNo LIKE :search OR s.LastName LIKE :search OR s.FirstName LIKE :search OR s.MiddleName LIKE :search OR c.accro LIKE :search OR a.alumni_id LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Sorting
    $sort_options = [
        'StudentNo ASC' => 'Student No (ASC)',
        'StudentNo DESC' => 'Student No (DESC)',
        'LastName ASC' => 'Name (A-Z)',
        'LastName DESC' => 'Name (Z-A)',
        'accro ASC' => 'Course (A-Z)',
        'accro DESC' => 'Course (Z-A)',
        'alumni_id ASC' => 'Alumni ID (ASC)',
        'alumni_id DESC' => 'Alumni ID (DESC)'
    ];
    $order_by = 's.LastName, s.FirstName';
    if ($filter && array_key_exists($filter, $sort_options)) {
        $order_by = $filter;
    }

    // Get total records for pagination
    $count_query = "SELECT COUNT(*) as total FROM students s LEFT JOIN course c ON s.Course = c.id LEFT JOIN alumni_ids a ON a.student_no = s.StudentNo WHERE $where";
    $count_stmt = $conn->prepare($count_query);
    foreach ($params as $k => $v) {
        $count_stmt->bindValue($k, $v);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total_records / $entries_per_page);

    // Fetch students for current page
    $query = "
        SELECT
            s.StudentNo,
            s.LastName,
            s.FirstName,
            s.MiddleName,
            c.accro,
            a.alumni_id,
            -- Generate on the fly if not present
            IFNULL(
                a.alumni_id,
                CONCAT(
                    RIGHT(YEAR(lgm.DateOfGraduation), 2),
                    lgm.Semester,
                    '-',
                    LPAD(COALESCE(reg_counts.reg_count, 0), 4, '0')
                )
            ) AS display_alumni_id
        FROM students s
        LEFT JOIN course c ON s.Course = c.id
        LEFT JOIN alumni_ids a ON a.student_no = s.StudentNo
        LEFT JOIN listgradsub lgs ON lgs.StudentNo = s.StudentNo
        LEFT JOIN listgradmain lgm ON lgm.id = lgs.MainID
        LEFT JOIN (
            SELECT StudentNo, COUNT(*) as reg_count
            FROM registration
            GROUP BY StudentNo
        ) reg_counts ON reg_counts.StudentNo = s.StudentNo
        WHERE $where
        ORDER BY $order_by
        LIMIT :offset, :limit";

    $stmt = $conn->prepare($query);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate table HTML
    $table_html = '';
    if (count($students) > 0) {
        foreach ($students as $stu) {
            $table_html .= '<tr class="hover:bg-gray-50 transition-colors duration-200">';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm font-mono text-gray-600">' . htmlspecialchars($stu['StudentNo']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm text-gray-800">' . htmlspecialchars($stu['LastName'] . ', ' . $stu['FirstName'] . ' ' . $stu['MiddleName']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm text-gray-500">' . htmlspecialchars($stu['accro']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm font-mono">';
            
            if ($stu['alumni_id']) {
                $table_html .= '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">';
                $table_html .= htmlspecialchars($stu['alumni_id']);
                $table_html .= '</span>';
            } else {
                $table_html .= '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">';
                $table_html .= 'Not Generated';
                $table_html .= '</span>';
            }
            
            $table_html .= '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-center align-middle text-sm font-medium">';
            
            if (!$stu['alumni_id']) {
                $table_html .= '<button type="button" onclick="openConfirmationModal(\'' . htmlspecialchars($stu['StudentNo']) . '\', \'' . htmlspecialchars($stu['LastName'] . ', ' . $stu['FirstName']) . '\')" class="text-indigo-600 hover:text-indigo-900 transition duration-150 ease-in-out font-semibold">';
                $table_html .= '<i class="fas fa-magic mr-1"></i>Generate ID';
                $table_html .= '</button>';
            } else {
                $table_html .= '<span class="text-gray-400 cursor-not-allowed">Generated</span>';
            }
            
            $table_html .= '</td>';
            $table_html .= '</tr>';
        }
    } else {
        $table_html = '<tr><td colspan="5" class="px-6 py-12 text-center align-middle text-gray-500">';
        $table_html .= '<i class="fas fa-info-circle fa-2x mb-2"></i><br>';
        $table_html .= 'No students found.';
        $table_html .= '</td></tr>';
    }

    // Generate pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        // First Page
        if ($current_page > 1) {
            $pagination_html .= '<button onclick="loadPage(1)" class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">First</button>';
        }
        
        // Previous Page
        if ($current_page > 1) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page - 1) . ')" class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">Previous</button>';
        }
        
        // Page Numbers
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = $i === $current_page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100';
            $pagination_html .= '<button onclick="loadPage(' . $i . ')" class="px-3 py-1 border rounded-lg ' . $active_class . ' transition-colors duration-200">' . $i . '</button>';
        }
        
        // Next Page
        if ($current_page < $total_pages) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page + 1) . ')" class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">Next</button>';
        }
        
        // Last Page
        if ($current_page < $total_pages) {
            $pagination_html .= '<button onclick="loadPage(' . $total_pages . ')" class="px-3 py-1 border rounded-lg hover:bg-gray-100 transition-colors duration-200">Last</button>';
        }
    }

    // Generate pagination info
    $showing_from = $offset + 1;
    $showing_to = min($offset + $entries_per_page, $total_records);
    $pagination_info = "Showing $showing_from to $showing_to of $total_records entries";

    // Return JSON response
    echo json_encode([
        'success' => true,
        'table_html' => $table_html,
        'pagination_html' => $pagination_html,
        'pagination_info' => $pagination_info,
        'total_records' => $total_records,
        'current_page' => $current_page,
        'total_pages' => $total_pages
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
