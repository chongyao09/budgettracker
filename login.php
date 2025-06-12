<?php
session_start();
// Database config
$host = 'localhost';
$db   = 'budgettracker';
$user = 'root';
$pass = '';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user by email
    $stmt = $conn->prepare('SELECT id, email, password_hash FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            
            // Log successful login
            error_log("User logged in successfully. User ID: " . $user['id']);
            
            if ($user['id'] == 1) {
                header('Location: admin.html');
                exit();
            } else {
                header('Location: dashboard.html');
                exit();
            }
        } else {
            header('Location: credential.html?error=invalid_password');
            exit();
        }
    } else {
        header('Location: credential.html?error=user_not_found');
        exit();
    }
    $stmt->close();
} else {
    header('Location: credential.html');
    exit();
}
$conn->close();
?> 