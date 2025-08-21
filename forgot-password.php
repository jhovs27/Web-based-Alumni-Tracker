<?php
session_start();
require_once __DIR__ . '/admin/config/database.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if admin exists
        $stmt = $conn->prepare('SELECT id, name FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            // Generate secure token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
            // Store token and expiry in DB (add columns if needed)
            $stmt = $conn->prepare('UPDATE admins SET reset_token = ?, reset_token_expires = ? WHERE id = ?');
            $stmt->execute([$token, $expires, $admin['id']]);
            // Send email
            $reset_link = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=$token";
            $subject = "SLSU Alumni Admin Password Reset";
            $body = "Hello {$admin['name']},\n\nWe received a request to reset your admin password. Click the link below to set a new password:\n$reset_link\n\nIf you did not request this, you can ignore this email.\n\nThis link will expire in 1 hour.";
            @mail($email, $subject, $body, "From: no-reply@slsu-hinunangan.edu.ph");
        }
        // Always show the same message
        $message = 'If your email is registered, you will receive a password reset link.';
    } else {
        $message = 'Please enter a valid email address.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .forgot-container { background: #fff; padding: 2.5rem 2rem; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); max-width: 400px; width: 100%; }
        h1 { color: #0056b3; font-size: 2rem; margin-bottom: 1.5rem; text-align: center; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #222; font-weight: 600; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
        .btn { width: 100%; background: #0056b3; color: #fff; border: none; padding: 0.9rem; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: #003e80; }
        .message { margin-bottom: 1rem; color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 1rem; border-radius: 6px; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 1.5rem; color: #0056b3; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="forgot-container">
        <h1><i class="fas fa-unlock-alt"></i> Forgot Password</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Admin Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your admin email" required autofocus>
            </div>
            <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
        </form>
        <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
    </div>
</body>
</html> 