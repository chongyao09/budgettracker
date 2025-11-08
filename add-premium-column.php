<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'budgettracker_new';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add is_premium column to users table if it doesn't exist
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_premium TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash";

if ($conn->query($sql) === TRUE) {
    echo "Premium column added successfully or already exists<br>";
} else {
    echo "Error adding column: " . $conn->error . "<br>";
}

$conn->close();
?>
