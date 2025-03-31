<?php
session_start();
require_once "../konek/db.php";  

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $errors[] = "All fields are required!";
    } else {
        
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
       
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
           
            if (password_verify($password, $user['password'])) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email']; 
                
                header("Location: ../pages/dashboard.php");
                exit();
            } else {
                $errors[] = "Incorrect password!";
            }
        } else {
            $errors[] = "Email not found!";
        }
    }

    
    $_SESSION['errors'] = $errors;
    header("Location: ../autho/login.php");
    exit();
}
?>
