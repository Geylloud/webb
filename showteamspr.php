<?php
require_once "../konek/db.php";

$user_id = $_SESSION['user_id'];

$teams = [];

// Query all teams the user is associated with (as creator or member)
$stmt = $conn->prepare("
    SELECT 
        t.id, 
        t.name, 
        t.admin_id, 
        COALESCE(tm.role, 'admin') AS role
    FROM teams t
    LEFT JOIN team_members tm ON t.id = tm.team_id AND tm.user_id = ?
    WHERE t.admin_id = ? OR tm.user_id = ?
    GROUP BY t.id, t.name, t.admin_id, tm.role
");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $teams[] = $row;
}

$stmt->close();
$conn->close();
return $teams;
?>