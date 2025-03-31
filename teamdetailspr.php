<?php
require_once "../konek/db.php";

if (!isset($_GET['team_id'])) {
    die("No team selected.");
}

$team_id = $_GET['team_id'];

$sql = "SELECT name FROM teams WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $team = $result->fetch_assoc();
    return $team['name'];
} else {
    die("Team not found.");
}
?>
