<?php
$host = 'localhost';
$db = 'budgettracker';
$user = 'root';
$pass = '';

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if admin user exists
$result = $conn->query("SELECT id FROM users WHERE email = 'admin@admin'");
if ($result->num_rows == 0) {
    // Insert admin user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, is_active) VALUES (?, ?, ?, ?)");
    $name = 'admin';
    $email = 'admin@admin';
    $password_hash = '$2y$10$eXTKLyauvTtWqj2JHFC4OeDWvobu/vgpHBxvgMo8Wo15NlS6FOlE2'; // password: admin
    $is_active = 1;
    $stmt->bind_param("sssi", $name, $email, $password_hash, $is_active);
    
    if ($stmt->execute()) {
        echo "Admin user inserted successfully<br>";
    } else {
        echo "Error inserting admin user: " . $conn->error . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

$conn->close();
?>
