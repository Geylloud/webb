<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: autho/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Team</title>
    <style>
        :root {
            --primary-color: #001F3F;
            --secondary-color: #34495E;
            --accent-color: #1ABC9C;
            --text-color: #FFFFFF;
            --background-color: #F8F9FA;
            --card-bg: #FFFFFF;
            --hover-bg: rgba(255, 255, 255, 0.1);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: #333;
            overflow-x: hidden;
        }

        .navbar {
            width: 100%;
            background-color: var(--primary-color);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 60px;
            box-shadow: var(--shadow);
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            margin-right: 20px;
        }

        .logo img {
            width: 150px;
            height: 40px;
            margin-right: 10px;
        }

        .menu-icon {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }

        .menu-icon span {
            width: 25px;
            height: 3px;
            background-color: var(--text-color);
            margin: 2px 0;
            transition: 0.3s;
        }

        .right-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notifications {
            position: relative;
            cursor: pointer;
        }

        .bell-icon {
            font-size: 24px;
            color: var(--text-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            font-size: 12px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile {
            position: relative;
            display: flex;
            align-items: center;
        }

        .profile-img {
            width: 40px;
            height: 40px;
            background-color: var(--accent-color);
            color: white;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-transform: uppercase;
            cursor: pointer;
        }

        .profile-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            background: var(--card-bg);
            box-shadow: var(--shadow);
            border-radius: 8px;
            display: none;
            flex-direction: column;
            width: 180px;
            padding: 10px;
        }

        .profile-dropdown p {
            margin: 5px 0;
            padding: 5px;
            font-size: 14px;
            text-align: center;
            color: #333;
        }

        .profile-dropdown a {
            text-decoration: none;
            color: var(--primary-color);
            text-align: center;
            padding: 8px;
            display: block;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            transition: 0.3s;
        }

        .profile-dropdown a:hover {
            background-color: var(--accent-color);
            color: white;
        }

        .user-profile:hover .profile-dropdown {
            display: flex;
        }

        .side-nav {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 60px;
            left: 0;
            background-color: var(--secondary-color);
            padding: 20px;
            transition: 0.3s;
            z-index: 999;
            box-shadow: var(--shadow);
            color: var(--text-color);
        }

        .side-nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .side-nav-header h2 {
            font-size: 18px;
            font-weight: bold;
        }

        .close-btn {
            font-size: 24px;
            cursor: pointer;
            color: var(--text-color);
        }

        .side-nav-links {
            list-style: none;
            padding: 0;
        }

        .side-nav-links li {
            margin-bottom: 15px;
        }

        .side-nav-links li a {
            color: var(--text-color);
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: 0.3s;
        }

        .side-nav-links li a:hover {
            background-color: var(--hover-bg);
        }

        .content {
            margin-top: 80px;
            padding: 40px 5%;
            transition: all 0.3s;
            text-align: center;
        }

        .content.sidebar-open {
            margin-left: 250px;
        }

        .content.sidebar-closed {
            margin-left: auto;
            margin-right: auto;
            max-width: 800px;
        }

        .container {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            max-width: 500px;
            margin: 0 auto;
        }

        .container h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .container label {
            display: block;
            margin: 10px 0 5px;
            color: #333;
        }

        .container input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .container button {
            background-color: var(--accent-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }

        .container button:hover {
            background-color: #16a085;
        }

        @media (max-width: 768px) {
            .menu-icon {
                display: flex;
            }

            .side-nav {
                top: 0;
                left: -250px;
            }

            .content {
                margin-left: 0;
            }

            .content.sidebar-open {
                margin-left: 0;
            }

            .content.sidebar-closed {
                margin-left: auto;
            }
        }

        @media (min-width: 769px) {
            .menu-icon {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="dashboard.php" class="logo">
            <img src="l.png" alt="TeamTrack Logo">
        </a>

        <div class="menu-icon" id="menuIcon">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="right-section">
            <div class="notifications">
                <span class="bell-icon" id="bellIcon">🔔</span>
                <span class="notification-badge">3</span>
            </div>

            <div class="user-profile">
                <div class="profile-img">
                    <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)); ?>
                </div>

                <div class="profile-dropdown">
                    <p><strong><?php echo htmlspecialchars($_SESSION['first_name'] . " " . $_SESSION['last_name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <a href="/finalproject/processing/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="side-nav" id="sideNav">
        <div class="side-nav-header">
            <h2><?php echo htmlspecialchars($_SESSION['first_name']) . "'s Workplace"; ?></h2>
            <span class="close-btn" id="closeBtn">×</span>
        </div>
        <ul class="side-nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="Create_team.php">Create Team</a></li>
            <li><a href="showteams.php">Team</a></li>
        </ul>
    </nav>

    <div class="content" id="contentArea">
        <div class="container">
            <h2>Create a Team</h2>
            <form id="createTeamForm" action="../processing/create_teampro.php" method="POST">
                <label for="team_name">Team Name:</label>
                <input type="text" id="team_name" name="team_name" required>

                <label for="members">Add Members (Emails, comma-separated):</label>
                <input type="text" id="members" name="members" placeholder="example1@email.com, example2@email.com">

                <button type="submit" id="submitButton">Create Team</button>
            </form>
        </div>
    </div>

    <script>
        const sideNav = document.getElementById('sideNav');
        const menuIcon = document.getElementById('menuIcon');
        const closeBtn = document.getElementById('closeBtn');
        const contentArea = document.getElementById('contentArea');
        const form = document.getElementById('createTeamForm');
        const submitButton = document.getElementById('submitButton');
        let isSubmitting = false; // Flag to prevent multiple submissions

        function updateContentPosition() {
            if (window.innerWidth > 768) {
                if (sideNav.style.left === '0px' || sideNav.style.left === '') {
                    contentArea.classList.remove('sidebar-closed');
                    contentArea.classList.add('sidebar-open');
                } else {
                    contentArea.classList.remove('sidebar-open');
                    contentArea.classList.add('sidebar-closed');
                }
            } else {
                contentArea.classList.remove('sidebar-open', 'sidebar-closed');
            }
        }

        menuIcon.addEventListener('click', function() {
            sideNav.style.left = '0';
            updateContentPosition();
        });

        closeBtn.addEventListener('click', function() {
            sideNav.style.left = '-250px';
            updateContentPosition();
        });

        document.addEventListener('click', function(event) {
            if (!sideNav.contains(event.target) && !menuIcon.contains(event.target) && window.innerWidth <= 768) {
                sideNav.style.left = '-250px';
                updateContentPosition();
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 769) {
                sideNav.style.left = '0';
            } else {
                sideNav.style.left = '-250px';
            }
            updateContentPosition();
        });

        window.addEventListener('load', function() {
            if (window.innerWidth >= 769) {
                sideNav.style.left = '0';
            }
            updateContentPosition();
        });

        document.getElementById('bellIcon').addEventListener('click', function() {
            alert('Notifications clicked! Add your notification logic here.');
        });

        // Handle form submission with AJAX
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            if (isSubmitting) {
                console.log('Submission already in progress, ignoring.');
                return; // Prevent multiple submissions
            }

            isSubmitting = true; // Set flag
            submitButton.disabled = true; // Disable button to prevent double-clicks
            console.log('Form submitted, sending request...');

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('Parsed JSON data:', data);
                if (data.success) {
                    console.log(`Team created successfully, redirecting to teamdetails.php?team_id=${data.team_id}`);
                    window.location.href = `teamdetails.php?team_id=${data.team_id}`;
                } else {
                    alert('Error creating team: ' + (data.error || 'Unknown error'));
                    isSubmitting = false;
                    submitButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while creating the team.');
                isSubmitting = false;
                submitButton.disabled = false;
            });
        });
    </script>
</body>
</html>