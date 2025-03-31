<?php
require_once "../konek/db.php"; // DB connection

$team_id = $_GET['team_id'] ?? 0;

$lists = [];
if ($team_id == 0) {
    return ['error' => 'No board selected.'];
}

// Fetch lists that belong to this team
$sql = "SELECT * FROM lists WHERE team_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($list = $result->fetch_assoc()) {
        $lists[] = $list;
    }
    $stmt->close();
} else {
    return ['error' => 'Database error: ' . $conn->error];
}

return $lists;
?>