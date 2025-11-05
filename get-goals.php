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

try {
    $stmt = $conn->prepare("SELECT id, name, target_amount, current_amount, target_date, category, description, created_at FROM budget_goals WHERE user_id = ? ORDER BY target_date ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $goals = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate progress percentage
        $progress = $row['target_amount'] > 0 ? ($row['current_amount'] / $row['target_amount']) * 100 : 0;
        $row['progress_percentage'] = round(min($progress, 100), 1);

        // Calculate days remaining
        $targetDate = new DateTime($row['target_date']);
        $today = new DateTime();
        $interval = $today->diff($targetDate);
        $row['days_remaining'] = $interval->days * ($targetDate > $today ? 1 : -1);

        // Calculate monthly savings needed
        if ($row['days_remaining'] > 0) {
            $remaining = $row['target_amount'] - $row['current_amount'];
            $row['monthly_needed'] = round($remaining / ($row['days_remaining'] / 30), 2);
        } else {
            $row['monthly_needed'] = 0;
        }

        $goals[] = $row;
    }

    echo json_encode(['success' => true, 'goals' => $goals]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
