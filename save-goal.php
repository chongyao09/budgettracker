<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

$name = trim($input['name'] ?? '');
$target_amount = floatval($input['target_amount'] ?? 0);
$current_amount = floatval($input['current_amount'] ?? 0);
$target_date = $input['target_date'] ?? '';
$category = trim($input['category'] ?? '');
$description = trim($input['description'] ?? '');
$goal_id = intval($input['id'] ?? 0);

// Validation
if (empty($name)) {
    http_response_code(400);
    echo json_encode(['error' => 'Goal name is required']);
    exit;
}

if ($target_amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Target amount must be greater than 0']);
    exit;
}

if (empty($target_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Target date is required']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $target_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

try {
    if ($goal_id > 0) {
        // Update existing goal
        $stmt = $conn->prepare("UPDATE budget_goals SET name = ?, target_amount = ?, current_amount = ?, target_date = ?, category = ?, description = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sddsssii", $name, $target_amount, $current_amount, $target_date, $category, $description, $goal_id, $user_id);
    } else {
        // Create new goal
        $stmt = $conn->prepare("INSERT INTO budget_goals (user_id, name, target_amount, current_amount, target_date, category, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddsss", $user_id, $name, $target_amount, $current_amount, $target_date, $category, $description);
    }

    if ($stmt->execute()) {
        $response_id = $goal_id > 0 ? $goal_id : $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => $goal_id > 0 ? 'Goal updated successfully' : 'Goal created successfully',
            'goal_id' => $response_id
        ]);
    } else {
        throw new Exception('Failed to save goal');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
