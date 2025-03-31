<?php
header('Content-Type: application/json');
require_once '../konek/db.php';

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['error'] = 'Invalid request method. Use GET.';
    echo json_encode($response);
    exit;
}

if (!isset($_GET['team_id']) || !is_numeric($_GET['team_id'])) {
    $response['error'] = 'Team ID is required and must be numeric.';
    echo json_encode($response);
    exit;
}

$team_id = (int)$_GET['team_id'];

$stmt = $conn->prepare("
    SELECT 
        t.id, 
        t.list_id, 
        t.task_name, 
        t.status, 
        t.deadline,
        teams.admin_id AS admin_id,
        GROUP_CONCAT(ta.user_id) AS assigned_members,
        GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') AS assigned_names
    FROM tasks t
    LEFT JOIN teams ON t.team_id = teams.id
    LEFT JOIN task_assignments ta ON t.id = ta.task_id
    LEFT JOIN users u ON ta.user_id = u.id
    WHERE t.team_id = ?
    GROUP BY t.id, t.list_id, t.task_name, t.status, t.deadline, teams.admin_id
");
if ($stmt === false) {
    $response['error'] = 'Prepare failed: ' . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = [
        'id' => $row['id'],
        'list_id' => $row['list_id'],
        'task_name' => $row['task_name'],
        'status' => $row['status'],
        'deadline' => $row['deadline'],
        'admin_id' => $row['admin_id'],
        'assigned_members' => $row['assigned_members'] ?: '', // IDs as comma-separated string
        'assigned_names' => $row['assigned_names'] ?: 'None'  // Names for display
    ];
}

$stmt->close();
$conn->close();

$response['success'] = true;
$response['tasks'] = $tasks;

echo json_encode($response);
?>