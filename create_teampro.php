<?php
session_start();
require_once "../konek/db.php";

header('Content-Type: application/json'); // Set JSON header

$team_name = $_POST['team_name'] ?? '';
$members = $_POST['members'] ?? '';
$user_id = $_SESSION['user_id'];

$response = ['success' => false, 'error' => ''];

// Log the request for debugging
error_log("create_teampro.php called with team_name: '$team_name', user_id: $user_id, members: '$members'");

if (empty($team_name)) {
    $response['error'] = 'Team name is required!';
    echo json_encode($response);
    exit;
}

// Check if team name already exists for this user
$stmt = $conn->prepare("SELECT id FROM teams WHERE name = ? AND admin_id = ?");
$stmt->bind_param("si", $team_name, $user_id);
$stmt->execute();
if ($stmt->fetch()) {
    $response['error'] = 'You already have a team with this name!';
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// Insert the team
$stmt = $conn->prepare("INSERT INTO teams (name, admin_id) VALUES (?, ?)");
$stmt->bind_param("si", $team_name, $user_id);
if ($stmt->execute()) {
    $team_id = $stmt->insert_id;
    error_log("Team created with ID: $team_id");

    // Insert the creator as admin in team_members
    $stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id, role, status) VALUES (?, ?, 'admin', 'accepted')");
    $stmt->bind_param("ii", $team_id, $user_id);
    if ($stmt->execute()) {
        error_log("Added creator (user_id: $user_id) as admin to team $team_id");
    } else {
        $response['error'] = 'Failed to add creator as admin: ' . $conn->error;
        error_log("Failed to add creator as admin: " . $conn->error);
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Add members if provided
    if (!empty($members)) {
        $member_emails = explode(',', $members);
        $failed_members = [];
        foreach ($member_emails as $email) {
            $email = trim($email);
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $stmt->bind_result($member_id);
                if ($stmt->fetch()) {
                    $stmt->close();
                    // Modified to automatically set status as 'accepted' and role as 'member'
                    $stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id, role, status) VALUES (?, ?, 'member', 'accepted')");
                    $stmt->bind_param("ii", $team_id, $member_id);
                    if ($stmt->execute()) {
                        error_log("Added member with ID $member_id to team $team_id with accepted status");
                    } else {
                        $failed_members[] = $email;
                        error_log("Failed to add member with email $email: " . $conn->error);
                    }
                } else {
                    $failed_members[] = $email;
                    error_log("No user found for email: $email");
                }
            } else {
                $failed_members[] = $email;
                error_log("Error executing user lookup for $email: " . $conn->error);
            }
            $stmt->close();
        }
        if (!empty($failed_members)) {
            $response['warning'] = "Some members couldn’t be added: " . implode(', ', $failed_members);
        }
    }

    $response['success'] = true;
    $response['team_id'] = $team_id;
} else {
    $response['error'] = 'Failed to create team in database: ' . $conn->error;
    error_log("Team creation failed: " . $conn->error);
}

$conn->close();
echo json_encode($response);
?>