<?php
ob_start();
header('Content-Type: application/json');
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../konek/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Validate required fields for tasks
$required_fields = ['list_id', 'task_name', 'team_id'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// Extract task data
$list_id = $data['list_id'];
$task_name = $data['task_name'];
$team_id = $data['team_id'];
$status = $data['status'] ?? 'To Do';
$deadline = $data['deadline'] ?? null;
$assigned_to = $data['assigned_to'] ?? []; // Expecting an array of user IDs from frontend

// Validate status
$valid_statuses = ['To Do', 'In Progress', 'Done'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status value']);
    exit;
}

// Validate assigned_to
if (!is_array($assigned_to)) {
    http_response_code(400);
    echo json_encode(['error' => 'assigned_to must be an array of user IDs']);
    exit;
}

// Start a transaction
$conn->begin_transaction();

try {
    // Insert the task
    $stmt = $conn->prepare("
        INSERT INTO tasks (list_id, task_name, status, deadline, team_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssi", $list_id, $task_name, $status, $deadline, $team_id);
    $stmt->execute();
    $task_id = $conn->insert_id;
    $stmt->close();

    // Insert task assignments only for explicitly provided user IDs
    $assigned_names = [];
    if (!empty($assigned_to)) {
        $stmt = $conn->prepare("
            INSERT INTO task_assignments (task_id, user_id)
            VALUES (?, ?)
        ");
        foreach ($assigned_to as $user_id) {
            if (!is_numeric($user_id) || $user_id <= 0) {
                throw new Exception("Invalid user_id: $user_id");
            }
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();

            // Fetch name for response
            $name_stmt = $conn->prepare("
                SELECT CONCAT(first_name, ' ', last_name) AS name 
                FROM users 
                WHERE id = ?
            ");
            $name_stmt->bind_param("i", $user_id);
            $name_stmt->execute();
            $result = $name_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $assigned_names[$user_id] = $row['name'];
            }
            $name_stmt->close();
        }
        $stmt->close();
    }

    // Commit the transaction
    $conn->commit();

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'task_id' => $task_id,
        'task_name' => $task_name,
        'status' => $status,
        'deadline' => $deadline,
        'assigned_to' => array_map(function($user_id) use ($assigned_names) {
            return [
                'user_id' => $user_id,
                'name' => $assigned_names[$user_id] ?? 'Unknown'
            ];
        }, $assigned_to)
    ]);
} catch (Exception $e) {
    $conn->rollback();
    error_log($e->getMessage()); // Log error for debugging
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create task or assignments: ' . $e->getMessage()]);
}

$conn->close();
?>