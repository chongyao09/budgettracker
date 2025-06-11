<?php
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
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user by email
    $stmt = $conn->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password_hash);
        $stmt->fetch();
        if (password_verify($password, $password_hash)) {
            // You can redirect or show a success message here
            if ($id == 1) { // admin
                header('Location: admin.html');
                exit();
            } else {
                header('Location: dashboard.html');
                exit();
            }
        } else {
            echo '<h2>Invalid password.</h2>';
            echo '<a href="credential.html">Try Again</a>';
        }
    } else {
        echo '<h2>User not found.</h2>';
        echo '<a href="credential.html">Try Again</a>';
    }
    $stmt->close();
} else {
    header('Location: credential.html');
    exit();
}
$conn->close();
?> 