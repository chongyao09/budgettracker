<?php
session_start();
header('Content-Type: application/json');
require 'db.php'; // Make sure this file sets up $conn (mysqli)

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$email = $_POST['email'] ?? '';
$currency = $_POST['currency'] ?? '';
$current_password = $_POST['current-password'] ?? '';
$new_password = $_POST['new-password'] ?? '';

// Get current user info
$stmt = $conn->prepare('SELECT email, password_hash FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($db_email, $db_password_hash);
$stmt->fetch();
$stmt->close();

// Update email and currency if changed
$update_fields = [];
$params = [];
$types = '';
if ($email && $email !== $db_email) {
    $update_fields[] = 'email = ?';
    $params[] = $email;
    $types .= 's';
}

// Handle password change
if ($new_password) {
    if (!$current_password) {
        echo json_encode(['success' => false, 'message' => 'Current password required to change password']);
        exit;
    }
    if (!password_verify($current_password, $db_password_hash)) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $update_fields[] = 'password_hash = ?';
    $params[] = $new_hash;
    $types .= 's';
}

if (count($update_fields) > 0) {
    $params[] = $user_id;
    $types .= 'i';
    $sql = 'UPDATE users SET ' . implode(', ', $update_fields) . ' WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'No changes detected']);
    exit;
}