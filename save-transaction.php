<?php
// Start session
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header to return JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$host = 'localhost';
$db   = 'budgettracker_new';
$user = 'root';
$pass = '';

// Log the start of the script
error_log("Starting save-transaction.php");

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the POST data
    error_log("POST data received: " . print_r($_POST, true));

    // Validate required fields
    $required_fields = ['date', 'description', 'amount', 'type', 'category', 'account'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        error_log("Missing required fields: " . implode(', ', $missing_fields));
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
        exit();
    }

    $date = $_POST['date'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $account = $_POST['account'];
    $user_id = $_SESSION['user_id']; // Use session user_id instead of form data

    // Prepare the SQL statement
    $stmt = $conn->prepare('INSERT INTO transactions (date, description, amount, type, category, account, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Error preparing statement: ' . $conn->error]);
        exit();
    }

    // Bind parameters
    $stmt->bind_param('ssdsssi', $date, $description, $amount, $type, $category, $account, $user_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        error_log("Transaction saved successfully. ID: " . $stmt->insert_id);
        echo json_encode(['status' => 'success', 'message' => 'Transaction saved successfully', 'id' => $stmt->insert_id]);
    } else {
        error_log("Error saving transaction: " . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Error saving transaction: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
