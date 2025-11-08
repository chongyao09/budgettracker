<?php
// Database config
$host = 'localhost';
$db   = 'budgettracker_new';
$user = 'root';
$pass = '';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if user already exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo '<h2>Email already registered.</h2>';
        echo '<a href="signup.html">Try Again</a>';
    } else {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $name, $email, $password_hash);
        if ($stmt->execute()) {
            header('Location: credential.html?signup=success');
            exit();
        } else {
            echo '<h2>Error: Could not register.</h2>';
            echo '<a href="signup.html">Try Again</a>';
        }
    }
    $stmt->close();
} else {
    header('Location: signup.html');
    exit();
}
$conn->close();
?>
