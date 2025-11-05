<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'is_premium' => false,
        'message' => 'Not logged in'
    ]);
    exit;
}

// Database connection
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Check premium status from database
$stmt = $conn->prepare('SELECT is_premium FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $is_premium = (bool)$user['is_premium'];
    
    // Update session
    $_SESSION['is_premium'] = $is_premium;
    
    echo json_encode([
        'is_premium' => $is_premium
    ]);
} else {
    echo json_encode([
        'is_premium' => false,
        'message' => 'User not found'
    ]);
}

$stmt->close();
$conn->close();
?>

