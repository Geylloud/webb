<?php
session_start();
require_once "../konek/db.php";

$user_id = $_SESSION['user_id'];

// Get this week's range (Monday to Sunday)
$today = new DateTime(); // Current date: March 30, 2025
$today->setISODate($today->format('Y'), $today->format('W')); // Set to Monday
$this_week_start = $today->format('Y-m-d'); // e.g., 2025-03-24
$this_week_end = $today->modify('+6 days')->format('Y-m-d'); // e.g., 2025-03-30

// Get next week's range (Monday to Sunday)
$next_week_start = $today->modify('+1 day')->format('Y-m-d'); // e.g., 2025-03-31
$next_week_end = $today->modify('+6 days')->format('Y-m-d'); // e.g., 2025-04-06

$tasks = ['this_week' => [], 'next_week' => []];

try {
    // Query tasks due this week
    $stmt = $conn->prepare("
        SELECT t.id, t.task_name, t.deadline, t.status, t.list_id, l.team_id
        FROM tasks t
        LEFT JOIN lists l ON t.list_id = l.id
        INNER JOIN task_assignments ta ON t.id = ta.task_id
        WHERE ta.user_id = ?
        AND t.deadline BETWEEN ? AND ?
        AND t.status != 'Done'
        ORDER BY t.deadline ASC
    ");
    if ($stmt === false) {
        error_log("Prepare failed for this week: " . $conn->error);
        return $tasks;
    }
    $stmt->bind_param("iss", $user_id, $this_week_start, $this_week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tasks['this_week'][] = $row;
    }
    $stmt->close();

    // Query tasks due next week
    $stmt = $conn->prepare("
        SELECT t.id, t.task_name, t.deadline, t.status, t.list_id, l.team_id
        FROM tasks t
        LEFT JOIN lists l ON t.list_id = l.id
        INNER JOIN task_assignments ta ON t.id = ta.task_id
        WHERE ta.user_id = ?
        AND t.deadline BETWEEN ? AND ?
        AND t.status != 'Done'
        ORDER BY t.deadline ASC
    ");
    if ($stmt === false) {
        error_log("Prepare failed for next week: " . $conn->error);
        return $tasks;
    }
    $stmt->bind_param("iss", $user_id, $next_week_start, $next_week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tasks['next_week'][] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error in showtaskspr.php: " . $e->getMessage());
}

$conn->close();
return $tasks;
?> 