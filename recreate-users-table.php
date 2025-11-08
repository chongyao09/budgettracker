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

// Drop users table if it exists
$sql = "DROP TABLE IF EXISTS users";
if ($conn->query($sql) === TRUE) {
    echo "Table users dropped successfully<br>";
} else {
    echo "Error dropping table: " . $conn->error . "<br>";
}

// If drop failed due to tablespace, discard it
if (strpos($conn->error, 'tablespace') !== false) {
    $sql = "ALTER TABLE users DISCARD TABLESPACE";
    if ($conn->query($sql) === TRUE) {
        echo "Tablespace discarded<br>";
    } else {
        echo "Error discarding tablespace: " . $conn->error . "<br>";
    }
}

// Create users table
$sql = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$conn->close();
?>
