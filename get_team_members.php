<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once "../konek/db.php";

$team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : null;
if (!$team_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing team_id']);
    exit;
}

// Exclude the logged-in user (admin) from the result
$stmt = $conn->prepare("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name 
    FROM users u
    INNER JOIN team_members tm ON u.id = tm.user_id
    WHERE tm.team_id = ? AND tm.status = 'accepted' AND u.id != ?
    ORDER BY name
");
$stmt->bind_param("ii", $team_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($users);

$stmt->close();
$conn->close();
?>