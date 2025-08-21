<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['alumni_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
require_once '../admin/config/database.php';

// Use the existing $conn variable from database.php
$pdo = $conn;

// Fetch events with search and filter functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$event_type = isset($_GET['event_type']) ? trim($_GET['event_type']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

$query = "SELECT * FROM alumni_events WHERE status = 'Published'";
$params = [];

if (!empty($search)) {
    $query .= " AND (event_title LIKE ? OR event_description LIKE ? OR contact_person LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($event_type)) {
    $query .= " AND event_type = ?";
    $params[] = $event_type;
}

if (!empty($location)) {
    $query .= " AND (physical_address LIKE ? OR online_link LIKE ?)";
    $locationParam = "%$location%";
    $params[] = $locationParam;
    $params[] = $locationParam;
}

$query .= " ORDER BY start_datetime ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts for stats
$total_events = count($events);
$upcoming_events = 0;
$online_events = 0;
$in_person_events = 0;

foreach ($events as $event) {
    if (strtotime($event['start_datetime']) > time()) {
        $upcoming_events++;
    }
    if (!empty($event['online_link'])) {
        $online_events++;
    }
    if (!empty($event['physical_address'])) {
        $in_person_events++;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'events' => $events,
    'stats' => [
        'total_events' => $total_events,
        'upcoming_events' => $upcoming_events,
        'online_events' => $online_events,
        'in_person_events' => $in_person_events
    ],
    'timestamp' => time()
]);
?> 