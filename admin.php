<?php
session_start();

// Only allow user with user_id = 1 to access admin dashboard
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: credential.html');
    exit();
}

// Database configuration
$host = 'localhost';
$db   = 'budgettracker';
$user = 'root';
$pass = '';

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_stats':
            // Get total users
            $stmt = $conn->prepare('SELECT COUNT(*) as total FROM users');
            $stmt->execute();
            $total_users = $stmt->get_result()->fetch_assoc()['total'];
            
            // Get active users (users who logged in within last 30 days)
            $stmt = $conn->prepare('SELECT COUNT(DISTINCT user_id) as active FROM transactions WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
            $stmt->execute();
            $active_users = $stmt->get_result()->fetch_assoc()['active'];
            
            // Get total transactions
            $stmt = $conn->prepare('SELECT COUNT(*) as total FROM transactions');
            $stmt->execute();
            $total_transactions = $stmt->get_result()->fetch_assoc()['total'];
            
            // Get average transaction amount
            $stmt = $conn->prepare('SELECT AVG(amount) as avg_amount FROM transactions');
            $stmt->execute();
            $avg_transaction = $stmt->get_result()->fetch_assoc()['avg_amount'];
            
            // Get user growth data for chart
            $stmt = $conn->prepare('
                SELECT DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count 
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY month 
                ORDER BY month ASC
            ');
            $stmt->execute();
            $user_growth = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode([
                'total_users' => $total_users,
                'active_users' => $active_users,
                'total_transactions' => $total_transactions,
                'avg_transaction' => round($avg_transaction, 2),
                'user_growth' => $user_growth
            ]);
            break;
            
        case 'get_users':
            $result = $conn->query('SELECT id, name, email FROM users ORDER BY created_at DESC');
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            echo json_encode(['users' => $users]);
            break;
            
        case 'update_user':
            $user_id = $_POST['user_id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $is_active = $_POST['is_active'];
            
            $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, is_active = ? WHERE id = ?');
            $stmt->bind_param('ssii', $name, $email, $is_active, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            break;
            
        case 'delete_user':
            $user_id = intval($_POST['user_id']);
            $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $user_id);
            $success = $stmt->execute();
            echo json_encode(['success' => $success]);
            break;
    }
    
    exit();
}

// For regular page load, serve the admin dashboard HTML
include 'admin.html';
?> 