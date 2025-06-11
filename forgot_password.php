<?php
$host = 'localhost';
$db   = 'budgettracker';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // In a real app, generate a token, save it, and email a reset link.
        echo '<h2>If this email is registered, a password reset link has been sent.</h2>';
        echo '<a href="credential.html">Back to Sign In</a>';
    } else {
        echo '<h2>If this email is registered, a password reset link has been sent.</h2>';
        echo '<a href="credential.html">Back to Sign In</a>';
    }
    $stmt->close();
} else {
    header('Location: forgot_password.html');
    exit();
}
$conn->close();
?>