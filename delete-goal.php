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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get goal ID from query parameter
$goal_id = intval($_GET['id'] ?? 0);

if ($goal_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid goal ID']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM budget_goals WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $goal_id, $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Goal deleted successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Goal not found or already deleted']);
        }
    } else {
        throw new Exception('Failed to delete goal');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
