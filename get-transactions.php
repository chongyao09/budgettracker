<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$host = 'localhost';
$db   = 'budgettracker';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get transactions for the logged-in user
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $transactions[] = [
            'id' => $row['id'],
            'date' => $row['date'],
            'description' => $row['description'],
            'amount' => $row['amount'],
            'type' => $row['type'],
            'category' => $row['category'],
            'account' => $row['account']
        ];
    }
}

// Calculate totals
$total_income = 0;
$total_expense = 0;
foreach ($transactions as $transaction) {
    if ($transaction['type'] === 'income') {
        $total_income += $transaction['amount'];
    } else {
        $total_expense += $transaction['amount'];
    }
}

$response = [
    'transactions' => $transactions,
    'summary' => [
        'total_income' => $total_income,
        'total_expense' => $total_expense,
        'balance' => $total_income - $total_expense
    ]
];

echo json_encode($response);
$stmt->close();
$conn->close(); 