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
    <title>Register | Task Manager</title>
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
            padding-top: 200px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 350px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
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

        button {
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

        button:hover {
            background: #f0f0f0;
            color: #001f3f;
        }

        .register-link {
            margin-top: 15px;
            font-size: 14px;
            text-align: center;
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
    <form id="signupForm">
        <h2>Sign Up</h2>

        <div id="errorContainer" class="error" style="display: <?php echo !empty($errors) ? 'block' : 'none'; ?>;">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>

        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button type="submit">Register</button>

        <p class="register-link">Already have an account? <a href="login.php">Login here</a></p>
    </form>

    <script>
        document.getElementById('signupForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const errorContainer = document.getElementById('errorContainer');

            fetch('../processing/registerpro.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the correct dashboard location
                    window.location.href = '../pages/dashboard.php';
                } else {
                    // Display errors
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = data.errors.map(error => `<p>${error}</p>`).join('');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorContainer.style.display = 'block';
                errorContainer.innerHTML = '<p>Failed to register. Please try again!</p>';
            });
        });
    </script>
</body>
</html>