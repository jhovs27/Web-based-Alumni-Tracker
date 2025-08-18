<?php
session_start();
header('Content-Type: application/json');
require_once '../admin/config/database.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$survey_id = $input['id'] ?? null;

if (!$survey_id) {
    echo json_encode(['success' => false, 'error' => 'No survey ID']);
    exit;
}

// Delete the survey
$stmt = $conn->prepare('DELETE FROM survey WHERE id = :id');
$success = $stmt->execute([':id' => $survey_id]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Delete failed']);
} 