<?php
// Test script to check events in database
require_once '../admin/config/database.php';

echo "<h2>Testing Events Database</h2>";

try {
    // Check all events regardless of status
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM alumni_events");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Total events in database:</strong> $total</p>";
    
    // Check events by status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM alumni_events GROUP BY status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Events by status:</strong></p>";
    echo "<ul>";
    foreach ($statuses as $status) {
        echo "<li>Status: " . htmlspecialchars($status['status']) . " - Count: " . $status['count'] . "</li>";
    }
    echo "</ul>";
    
    // Check published/Active events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM alumni_events WHERE status IN ('published', 'Active')");
    $published = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p><strong>Published/Active events:</strong> $published</p>";
    
    // Show sample events
    if ($published > 0) {
        $stmt = $pdo->query("SELECT event_title, event_type, status, start_datetime FROM alumni_events WHERE status IN ('published', 'Active') LIMIT 5");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Sample events:</strong></p>";
        echo "<ul>";
        foreach ($events as $event) {
            echo "<li>" . htmlspecialchars($event['event_title']) . " (" . htmlspecialchars($event['event_type']) . ") - Status: " . htmlspecialchars($event['status']) . " - Date: " . htmlspecialchars($event['start_datetime']) . "</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 