<?php
session_start();
require_once "../konek/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['team_id'], $_POST['list_name'])) {
    $team_id = $_POST['team_id'];
    $list_name = trim($_POST['list_name']);
    $due_date = $_POST['due_date'] ?? null; // New field from form

    if (empty($list_name)) {
        die("List name cannot be empty.");
    }

    // Convert empty due_date to NULL for database
    if (empty($due_date)) {
        $due_date = null;
    }

    $sql = "INSERT INTO lists (team_id, name, due_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iss", $team_id, $list_name, $due_date); // 'i' for team_id, 's' for name, 's' for due_date

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: ../pages/teamdetails.php?team_id=" . $team_id);
        exit();
    } else {
        $stmt->close();
        $conn->close();
        die("Error adding list: " . $stmt->error);
    }
} else {
    die("Invalid request.");
}
?>