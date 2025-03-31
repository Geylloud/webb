<?php
require_once "../konek/db.php";

$team_id = $_GET['team_id'] ?? ''; // Changed from list_id to team_id

if (!$team_id || !is_numeric($team_id)) {
    return ['error' => 'Invalid Team ID'];
}

// Get all lists for the team, including due_date
$query = "SELECT id, name, due_date 
          FROM lists 
          WHERE team_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    return ['error' => 'Query preparation failed: ' . $conn->error];
}
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();

$lists = [];
while ($row = $result->fetch_assoc()) {
    $lists[] = $row;
}

$stmt->close();
$conn->close();

return $lists; // Return array of lists with id, name, and due_date
?>