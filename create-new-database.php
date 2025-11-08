<?php
$host = 'localhost';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE budgettracker_new";
if ($conn->query($sql) === TRUE) {
    echo "Database budgettracker_new created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("budgettracker_new");

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
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create transactions table
$sql = "CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('income','expense') NOT NULL,
    category VARCHAR(50) NOT NULL,
    account VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table transactions created successfully<br>";
} else {
    echo "Error creating transactions table: " . $conn->error . "<br>";
}

// Create budget_goals table
$sql = "CREATE TABLE budget_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    target_amount DECIMAL(10,2) NOT NULL,
    current_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    target_date DATE NOT NULL,
    category VARCHAR(50) DEFAULT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table budget_goals created successfully<br>";
} else {
    echo "Error creating budget_goals table: " . $conn->error . "<br>";
}

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

$conn->close();
?>
