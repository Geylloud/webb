<?php
session_start();
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
unset($_SESSION['errors']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Task Manager</title>
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

     
        body {
    background-image: url('gg.jpg'); 
    background-size: 100vw 100vh;  
    background-position: center; 
    background-repeat: no-repeat; 
    background-attachment: fixed; 
    padding-left: 900px; 
    padding-top: 250px;
    height: 100vh;
}
        
        h2 {
            margin-bottom: 20px;
        }

        
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 350px;
        }

        
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        
        .error {
            background: rgba(255, 0, 0, 0.2);
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 14px;
            text-align: center;
        }

        
        .btn {
            width: 100%;
            padding: 10px;
            border: none;
            background: #001f3f;
            color: #FFFFFF;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #f0f0f0;
        }

        
        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .register-link a {
            color: #001f3f;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="../processing/loginpro.php" method="POST">
    <h2>Login</h2>
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="password" name="password" placeholder="Enter your password" required>
        <button type="submit" class="btn">Login</button>
        <p class="register-link">Don't have an account? <a href="register.php">Sign up here</a></p>
    </form>
   
</body>
</html>
