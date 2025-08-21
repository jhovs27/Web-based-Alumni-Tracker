<?php
// Google Sign-In backend handler
// Place this file at: auth/google-signin.php
// Google API Client src folder must be in the auth directory

require_once __DIR__ . '/../admin/config/database.php';
require_once __DIR__ . '/src/Google/Client.php';
require_once __DIR__ . '/src/Google/Service/Oauth2.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['credential'] ?? '';
$role = $data['role'] ?? '';

if (!$token || !$role) {
    echo json_encode(['success' => false, 'message' => 'Missing token or role.']);
    exit;
}

$CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID'; // <-- Replace with your actual Google Client ID
$client = new Google_Client(['client_id' => $CLIENT_ID]);

try {
    $payload = $client->verifyIdToken($token);
    if (!$payload) {
        throw new Exception('Invalid ID token');
    }
    $email = $payload['email'];
    $name = $payload['name'] ?? '';
    $picture = $payload['picture'] ?? '';

    // Check user in DB (adjust table/column names as needed)
    if ($role === 'alumni') {
        $stmt = $conn->prepare('SELECT * FROM alumni WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            // Create new alumni user
            $stmt = $conn->prepare('INSERT INTO alumni (name, email, profile_photo_path, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$name, $email, $picture]);
            $user_id = $conn->lastInsertId();
        } else {
            $user_id = $user['id'];
        }
        session_start();
        $_SESSION['alumni_id'] = $user_id;
        $_SESSION['alumni_name'] = $name;
        $_SESSION['alumni_email'] = $email;
        $_SESSION['profile_photo_path'] = $picture;
        echo json_encode(['success' => true, 'redirect' => 'alumni/index.php']);
        exit;
    } elseif ($role === 'admin') {
        $stmt = $conn->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            // Optionally, restrict admin Google sign-in to pre-approved emails only
            echo json_encode(['success' => false, 'message' => 'No admin account found for this Google account.']);
            exit;
        }
        session_start();
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'];
        $_SESSION['profile_photo_path'] = $user['profile_photo_path'] ?? $picture;
        echo json_encode(['success' => true, 'redirect' => 'admin/index.php']);
        exit;
    } elseif ($role === 'programchair') {
        $stmt = $conn->prepare('SELECT * FROM program_chairs WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            // Optionally, create or restrict program chair sign-in
            echo json_encode(['success' => false, 'message' => 'No program chair account found for this Google account.']);
            exit;
        }
        session_start();
        $_SESSION['program_chair_id'] = $user['id'];
        $_SESSION['program_chair_name'] = $user['name'];
        $_SESSION['profile_photo_path'] = $user['profile_photo_path'] ?? $picture;
        echo json_encode(['success' => true, 'redirect' => 'programchair/index.php']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid role.']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Google Sign-In failed: ' . $e->getMessage()]);
    exit;
} 