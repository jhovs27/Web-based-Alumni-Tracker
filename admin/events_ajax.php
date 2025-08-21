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
$type = isset($_POST['type']) ? $_POST['type'] : '';
$visibility = isset($_POST['visibility']) ? $_POST['visibility'] : '';
$id_sort = isset($_POST['id_sort']) ? $_POST['id_sort'] : '';
$entries_per_page = isset($_POST['entries']) ? (int)$_POST['entries'] : 10;
$current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Build query with filters
    $query = "SELECT * FROM alumni_events WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM alumni_events WHERE 1=1";
    $params = [];

    if ($status) {
        $query .= " AND status = :status";
        $count_query .= " AND status = :status";
        $params[':status'] = $status;
    }

    if ($type) {
        $query .= " AND event_type = :type";
        $count_query .= " AND event_type = :type";
        $params[':type'] = $type;
    }

    if ($visibility) {
        $query .= " AND visibility = :visibility";
        $count_query .= " AND visibility = :visibility";
        $params[':visibility'] = $visibility;
    }

    if ($search) {
        $query .= " AND (event_title LIKE :search OR event_description LIKE :search OR contact_person LIKE :search)";
        $count_query .= " AND (event_title LIKE :search OR event_description LIKE :search OR contact_person LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Add sorting
    if ($id_sort === 'asc') {
        $query .= " ORDER BY id ASC";
    } elseif ($id_sort === 'desc') {
        $query .= " ORDER BY id DESC";
    } else {
        $query .= " ORDER BY start_datetime DESC";
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
            $start = new DateTime($row['start_datetime']);
            $end = new DateTime($row['end_datetime']);
            $datetime_display = $start->format('M d, Y h:i A') . ' - ' . $end->format('M d, Y h:i A');
            
            // Location badge
            $location_badge = '';
            if ($row['physical_address']) {
                $location_badge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Onsite</span>';
            } elseif ($row['online_link']) {
                $location_badge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Virtual</span>';
            } else {
                $location_badge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">TBA</span>';
            }
            
            // Status badge
            $status_class = '';
            switch($row['status']) {
                case 'Published':
                    $status_class = 'bg-green-100 text-green-800';
                    break;
                case 'Draft':
                    $status_class = 'bg-gray-100 text-gray-800';
                    break;
                case 'Cancelled':
                    $status_class = 'bg-red-100 text-red-800';
                    break;
                case 'Completed':
                    $status_class = 'bg-blue-100 text-blue-800';
                    break;
                default:
                    $status_class = 'bg-gray-100 text-gray-800';
                    break;
            }
            
            // Visibility badge
            $visibility_class = $row['visibility'] === 'Public' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
            
            $table_html .= '<tr class="hover:bg-gray-50 transition-colors duration-150">';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['id']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">' . htmlspecialchars($row['event_title']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $datetime_display . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">' . $location_badge . '</div></td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['event_type']) . '</td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_class . '">' . htmlspecialchars($row['status']) . '</span></td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $visibility_class . '">' . htmlspecialchars($row['visibility']) . '</span></td>';
            $table_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
            $table_html .= '<div class="flex space-x-2">';
            $table_html .= '<button onclick="viewEvent(' . $row['id'] . ')" class="text-blue-600 hover:text-blue-900 hover:bg-blue-50 p-1 rounded transition-colors duration-200" title="View Details"><i class="fas fa-eye"></i></button>';
            $table_html .= '<a href="edit-event.php?id=' . $row['id'] . '" class="text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 p-1 rounded transition-colors duration-200" title="Edit Event"><i class="fas fa-edit"></i></a>';
            $table_html .= '<button onclick="deleteEvent(' . $row['id'] . ')" class="text-red-600 hover:text-red-900 hover:bg-red-50 p-1 rounded transition-colors duration-200" title="Delete Event"><i class="fas fa-trash"></i></button>';
            $table_html .= '<button onclick="toggleEventStatus(' . $row['id'] . ', \'' . $row['status'] . '\')" class="text-green-600 hover:text-green-900 hover:bg-green-50 p-1 rounded transition-colors duration-200" title="Toggle Status"><i class="fas fa-toggle-on"></i></button>';
            $table_html .= '</div>';
            $table_html .= '</td>';
            $table_html .= '</tr>';
        }
    } else {
        $table_html = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">';
        $table_html .= '<div class="flex flex-col items-center py-8">';
        $table_html .= '<i class="fas fa-calendar-times text-4xl text-gray-300 mb-2"></i>';
        $table_html .= '<p class="text-gray-500">No events found</p>';
        $table_html .= '</div></td></tr>';
    }

    // Generate pagination HTML
    $pagination_html = '';
    if ($total_pages > 1) {
        // Previous button
        if ($current_page > 1) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page - 1) . ')" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200"><span class="sr-only">Previous</span><i class="fas fa-chevron-left"></i></button>';
        }
        
        // Page numbers
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = $i === $current_page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50';
            $pagination_html .= '<button onclick="loadPage(' . $i . ')" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium ' . $active_class . ' transition-colors duration-200">' . $i . '</button>';
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $pagination_html .= '<button onclick="loadPage(' . ($current_page + 1) . ')" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200"><span class="sr-only">Next</span><i class="fas fa-chevron-right"></i></button>';
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
