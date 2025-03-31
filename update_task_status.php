<?php
session_start();
require_once "../konek/db.php";

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'error' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Unauthorized: Please log in';
    echo json_encode($response);
    exit;
}

if (!isset($data['task_id']) || !is_numeric($data['task_id'])) {
    $response['error'] = 'Invalid task ID';
    echo json_encode($response);
    exit;
}

if (!isset($data['status']) || !in_array($data['status'], ['To Do', 'Done'])) {
    $response['error'] = 'Invalid status';
    echo json_encode($response);
    exit;
}

$task_id = $data['task_id'];
$status = $data['status'];
$user_id = $_SESSION['user_id'];

// Verify the user is the admin or assigned
$stmt = $conn->prepare("
    SELECT t.id 
    FROM tasks t
    LEFT JOIN teams tm ON t.team_id = tm.id
    LEFT JOIN task_assignments ta ON t.id = ta.task_id
    WHERE t.id = ? 
    AND (tm.admin_id = ? OR ta.user_id = ?)
");
if ($stmt === false) {
    $response['error'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param("iii", $task_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['error'] = 'Unauthorized or task not found';
    echo json_encode($response);
    exit;
}

// Update the task status
$stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
if ($stmt === false) {
    $response['error'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param("si", $status, $task_id);

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['error'] = 'Failed to update task: ' . $conn->error;
}

$stmt->close();
$conn->close();
echo json_encode($response);
?>