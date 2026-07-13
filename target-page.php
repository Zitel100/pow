<?php
session_start();

// Check if session is verified
if (!isset($_SESSION['session_verified']) || $_SESSION['session_verified'] !== true) {
    // If not verified, redirect back to verification page
    header('Location: /');
    exit;
}

// If verified, show the protected content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protected Content</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f9fafb;
        }
        .container {
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 { color: #111827; }
        .success-icon {
            font-size: 64px;
            color: #10b981;
            margin-bottom: 20px;
        }
        .verified-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 20px;
            background: #ef4444;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .logout-btn:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Access Granted</h1>
        <p style="color: #6b7280; margin-bottom: 20px;">Your session has been successfully verified.</p>
        <span class="verified-badge">✓ Verified</span>
        <div class="info">
            <p>Session ID: <?php echo htmlspecialchars(session_id()); ?></p>
            <p style="margin-top: 5px;">
                <a href="https://google.com/?logout" class="logout-btn">🚪 Logout</a>
            </p>
        </div>
    </div>
</body>
</html>
