<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get parameters
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';
$id_sort = isset($_POST['id_sort']) ? $_POST['id_sort'] : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Build query with filters
    $query = "SELECT * FROM job_posts WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM job_posts WHERE 1=1";
    $params = [];

    if ($status) {
        $query .= " AND status = :status";
        $count_query .= " AND status = :status";
        $params[':status'] = $status;
    }

    if ($category) {
        $query .= " AND category = :category";
        $count_query .= " AND category = :category";
        $params[':category'] = $category;
    }

    if ($type) {
        $query .= " AND job_type = :type";
        $count_query .= " AND job_type = :type";
        $params[':type'] = $type;
    }

    if ($search) {
        $query .= " AND (job_title LIKE :search OR company_name LIKE :search OR location LIKE :search)";
        $count_query .= " AND (job_title LIKE :search OR company_name LIKE :search OR location LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Add sorting
    if ($id_sort === 'asc') {
        $query .= " ORDER BY id ASC";
    } elseif ($id_sort === 'desc') {
        $query .= " ORDER BY id DESC";
    } else {
        $query .= " ORDER BY posted_date DESC";
    }

    // Get total records for pagination
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total_records / $entries_per_page);

    // Add pagination
    $query .= " LIMIT :offset, :limit";

    // Execute main query
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate table HTML
    $table_html = '';
    if (count($result) > 0) {
        foreach ($result as $row) {
            // Currency symbols
            $currencySymbols = [
                'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'AUD' => 'A$',
                'CAD' => 'C$', 'CHF' => 'Fr', 'CNY' => '¥', 'INR' => '₹', 'PHP' => '₱',
                'SGD' => 'S$', 'AED' => 'د.إ', 'BRL' => 'R$', 'MXN' => 'Mex$', 'NZD' => 'NZ$', 'ZAR' => 'R'
            ];
            
            $currency = isset($row['currency']) ? $row['currency'] : 'USD';
            $symbol = $currencySymbols[$currency] ?? '$';
            
            $salary_display = 'Not specified';
            if ($row['salary_min'] && $row['salary_max']) {
                $salary_display = $symbol . ' ' . number_format($row['salary_min']) . ' - ' . $symbol . ' ' . number_format($row['salary_max']);
            }
            
            // Status badge
            $status_class = '';
            if ($row['status'] === 'published') {
                $status_class = 'bg-green-100 text-green-800';
            } elseif ($row['status'] === 'draft') {
                $status_class = 'bg-yellow-100 text-yellow-800';
            } else {
                $status_class = 'bg-gray-100 text-gray-800';
            }
            
            $table_html .= '<tr class="hover:bg-gray-50 transition-colors duration-150">';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['id']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">' . htmlspecialchars($row['job_title']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['company_name']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['job_type']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . date('M d, Y', strtotime($row['posted_date'])) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . date('M d, Y', strtotime($row['deadline'])) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_class . '">' . ucfirst($row['status']) . '</span></td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . $salary_display . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
            $table_html .= '<div class="flex items-center space-x-3">';
            $table_html .= '<a href="view-post.php?id=' . $row['id'] . '" class="text-blue-600 hover:text-blue-900 hover:bg-blue-50 p-1 rounded transition-colors duration-200" title="View"><i class="fas fa-eye"></i></a>';
            $table_html .= '<a href="edit-post.php?id=' . $row['id'] . '" class="text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 p-1 rounded transition-colors duration-200" title="Edit"><i class="fas fa-edit"></i></a>';
            $table_html .= '<button onclick="confirmDelete(' . $row['id'] . ')" class="text-red-600 hover:text-red-900 hover:bg-red-50 p-1 rounded transition-colors duration-200" title="Delete"><i class="fas fa-trash"></i></button>';
            $table_html .= '</div></td></tr>';
        }
    } else {
        $table_html = '<tr><td colspan="9" class="px-6 py-8 text-center">';
        $table_html .= '<div class="flex flex-col items-center justify-center text-gray-500">';
        $table_html .= '<i class="fas fa-briefcase text-4xl mb-3 text-gray-300"></i>';
        $table_html .= '<p class="text-lg font-medium">No job posts found</p>';
        $table_html .= '<p class="text-sm mt-1">Try adjusting your search or filter to find what you\'re looking for.</p>';
        $table_html .= '</div></td></tr>';
    }

    // Generate pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        // Previous Button
        if ($current_page > 1) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page - 1) . ')" class="px-3 py-1 border rounded-md hover:bg-gray-50 transition-colors duration-200">Previous</button>';
        }
        
        // Page Numbers
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        
        if ($start_page > 1) {
            $pagination_html .= '<button onclick="loadPage(1)" class="px-3 py-1 border rounded-md hover:bg-gray-50 transition-colors duration-200">1</button>';
            if ($start_page > 2) {
                $pagination_html .= '<span class="px-2">...</span>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = $i === $current_page ? 'bg-blue-500 text-white' : 'hover:bg-gray-50';
            $pagination_html .= '<button onclick="loadPage(' . $i . ')" class="px-3 py-1 border rounded-md ' . $active_class . ' transition-colors duration-200">' . $i . '</button>';
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                $pagination_html .= '<span class="px-2">...</span>';
            }
            $pagination_html .= '<button onclick="loadPage(' . $total_pages . ')" class="px-3 py-1 border rounded-md hover:bg-gray-50 transition-colors duration-200">' . $total_pages . '</button>';
        }
        
        // Next Button
        if ($current_page < $total_pages) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page + 1) . ')" class="px-3 py-1 border rounded-md hover:bg-gray-50 transition-colors duration-200">Next</button>';
        }
    }

    // Generate pagination info
    $showing_from = $offset + 1;
    $showing_to = min($offset + $entries_per_page, $total_records);
    $pagination_info = "Showing <span class=\"font-medium\">$showing_from</span> to <span class=\"font-medium\">$showing_to</span> of <span class=\"font-medium\">$total_records</span> results";

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
