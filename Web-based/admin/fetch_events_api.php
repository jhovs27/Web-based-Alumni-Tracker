<?php
require_once 'config/database.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT id, event_title, start_datetime, end_datetime, event_description, event_type, status FROM alumni_events WHERE status IN ('Published', 'Active', 'Completed')");
    $stmt->execute();
    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id' => $row['id'],
            'title' => $row['event_title'],
            'start' => $row['start_datetime'],
            'end' => $row['end_datetime'],
            'description' => $row['event_description'],
            'event_type' => $row['event_type'],
            'status' => $row['status'],
        ];
    }
    echo json_encode($events);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch events', 'details' => $e->getMessage()]);
} 