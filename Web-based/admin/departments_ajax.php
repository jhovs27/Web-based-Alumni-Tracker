<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get parameters
$search = isset($_POST['search']) ? $_POST['search'] : '';
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Get total records for pagination
    $count_query = "SELECT COUNT(*) as total FROM department WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $count_query .= " AND (DepartmentName LIKE :search OR Description LIKE :search OR DepartmentHead LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $entries_per_page);

    // Fetch all program chairs and map by department id
    $chair_map = [];
    $chair_stmt = $conn->query("SELECT full_name, program FROM program_chairs");
    while ($row = $chair_stmt->fetch(PDO::FETCH_ASSOC)) {
        $chair_map[$row['program']] = $row['full_name'];
    }

    // Build the query with search, filter, and pagination
    $query = "SELECT id, DepartmentName, Description, DepartmentHead, Designation, Active FROM department WHERE 1=1";
    
    if (!empty($search)) {
        $query .= " AND (DepartmentName LIKE :search OR Description LIKE :search OR DepartmentHead LIKE :search)";
    }
    
    if (!empty($filter)) {
        $query .= " ORDER BY " . $filter;
    } else {
        $query .= " ORDER BY DepartmentName ASC";
    }
    
    $query .= " LIMIT :offset, :limit";

    // Execute query
    $stmt = $conn->prepare($query);
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate table HTML
    $table_html = '';
    if (count($result) > 0) {
        foreach ($result as $row) {
            $dept_id = $row['id'];
            $chair_name = !empty($chair_map[$dept_id]) ? htmlspecialchars($chair_map[$dept_id]) : '<span class="text-yellow-600">Not assigned</span>';
            $status_class = $row['Active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            $status_text = $row['Active'] ? 'Active' : 'Inactive';
            
            $table_html .= '<tr class="hover:bg-gray-50 transition-colors duration-150">';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['id']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">' . htmlspecialchars($row['DepartmentName']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['Description']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $chair_name . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['Designation']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm">';
            $table_html .= '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_class . '">' . $status_text . '</span>';
            $table_html .= '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">';
            $table_html .= '<button onclick="editDepartment(' . $row['id'] . ', \'' . htmlspecialchars($row['DepartmentName']) . '\', \'' . htmlspecialchars($row['Description']) . '\', \'' . htmlspecialchars($row['DepartmentHead']) . '\', \'' . htmlspecialchars($row['Designation']) . '\', ' . $row['Active'] . ')" class="text-blue-600 hover:text-blue-900 transition-colors duration-200 mr-3 hover:bg-blue-50 p-1 rounded">';
            $table_html .= '<i class="fas fa-edit"></i>';
            $table_html .= '</button>';
            $table_html .= '<button onclick="confirmDeleteDepartment(\'' . $row['id'] . '\', \'' . htmlspecialchars($row['DepartmentName']) . '\')" class="text-red-600 hover:text-red-900 transition-colors duration-200 hover:bg-red-50 p-1 rounded">';
            $table_html .= '<i class="fas fa-trash"></i>';
            $table_html .= '</button>';
            $table_html .= '</td>';
            $table_html .= '</tr>';
        }
    } else {
        $table_html = '<tr><td colspan="7" class="px-6 py-8 text-center">';
        $table_html .= '<div class="flex flex-col items-center justify-center text-gray-500">';
        $table_html .= '<i class="fas fa-search text-4xl mb-3 text-gray-300"></i>';
        $table_html .= '<p class="text-lg font-medium">No departments found</p>';
        $table_html .= '<p class="text-sm mt-1">Try adjusting your search or filter to find what you\'re looking for.</p>';
        $table_html .= '</div></td></tr>';
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
