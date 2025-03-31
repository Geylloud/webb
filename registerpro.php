<?php
session_start();
require_once "../konek/db.php";

header('Content-Type: application/json'); // Set response as JSON

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Email is already registered!";
        } else {
            // Register the user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id; // Get the new user's ID

                // Log the user in by setting session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['email'] = $email;

                // Return success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Account created successfully!'
                ]);
                exit();
            } else {
                $errors[] = "Registration failed. Try again!";
            }
        }
        $stmt->close();
    }

    // Return error response if validation fails
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ]);
    exit();
}

$conn->close();
?>