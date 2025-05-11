<?php
session_start();
include 'db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.html");
    exit();
}

// Get form data
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = trim($_POST['role'] ?? '');

// Validate inputs
if (empty($username) || empty($password) || empty($role)) {
    die('<div style="font-family: \'Poppins\', sans-serif; background: #0a192f; color: #fff; height: 100vh; display: flex; justify-content: center; align-items: center; flex-direction: column;">
            <h1 style="color: #00F9FF; margin-bottom: 20px;">Error: Missing Fields</h1>
            <p>Please fill in all required fields.</p>
            <a href="../login.html" style="margin-top: 20px; color: #00F9FF; text-decoration: none;">← Back to Login</a>
        </div>');
}

// Prevent SQL injection
$username = mysqli_real_escape_string($conn, $username);
$password = mysqli_real_escape_string($conn, $password);
$role = mysqli_real_escape_string($conn, $role);

// Hash password (you should be storing hashed passwords in DB)
// $hashed_password = password_hash($password, PASSWORD_DEFAULT);
// For now we'll use plain text since your DB seems to store plain text

$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND role = '$role'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die('<div style="font-family: \'Poppins\', sans-serif; background: #0a192f; color: #fff; height: 100vh; display: flex; justify-content: center; align-items: center; flex-direction: column;">
            <h1 style="color: #ff5555; margin-bottom: 20px;">Database Error</h1>
            <p>Please try again later.</p>
            <a href="../login.html" style="margin-top: 20px; color: #00F9FF; text-decoration: none;">← Back to Login</a>
        </div>');
}

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    
    // Set session variables
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['id'] = $row['id'];
    
    // Redirect based on role with success animation
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Success | LawLink</title>
        <style>
            body {
                font-family: \'Poppins\', sans-serif;
                background: #0a192f;
                color: #fff;
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                text-align: center;
            }
            .success-icon {
                color: #00F9FF;
                font-size: 5rem;
                margin-bottom: 20px;
                animation: pulse 1.5s infinite;
            }
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
            h1 {
                color: #00F9FF;
                margin-bottom: 10px;
            }
            p {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="success-icon">✓</div>
        <h1>Login Successful</h1>
        <p>Redirecting to your dashboard...</p>
        <script>
            setTimeout(function() {
                window.location.href = "../dashboard/' . $role . '.php";
            }, 1500);
        </script>
    </body>
    </html>';
    exit();
} else {
    die('<div style="font-family: \'Poppins\', sans-serif; background: #0a192f; color: #fff; height: 100vh; display: flex; justify-content: center; align-items: center; flex-direction: column;">
            <h1 style="color: #ff5555; margin-bottom: 20px;">Login Failed</h1>
            <p>Invalid username, password, or role.</p>
            <a href="../login.html" style="margin-top: 20px; color: #00F9FF; text-decoration: none;">← Try Again</a>
        </div>');
}
?>