<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: autho/login.php");
    exit();
}

$tasks_file = '../processing/showtaskpr.php';
if (!file_exists($tasks_file)) {
    die("Error: showtaskspr.php not found at $tasks_file");
}
$tasks_data = require $tasks_file;
if (!is_array($tasks_data) || !isset($tasks_data['this_week']) || !isset($tasks_data['next_week'])) {
    error_log("Tasks data is invalid: " . var_export($tasks_data, true));
    $tasks_data = ['this_week' => [], 'next_week' => []]; // Fallback
}
$this_week_tasks = $tasks_data['this_week'];
$next_week_tasks = $tasks_data['next_week'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management Dashboard</title>
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

        .content h2 {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .content p {
            font-size: 18px;
            color: #555;
        }

        .task-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .task-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            width: 300px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .task-card:hover {
            transform: scale(1.05);
        }

        .task-card h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .task-card p {
            font-size: 14px;
            color: #666;
        }

        .task-section {
            margin-top: 40px;
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
        <!-- Tasks Due This Week Section -->
        <div class="task-section">
            <h2>Tasks Due This Week</h2>
            <?php if (!empty($this_week_tasks)): ?>
                <div class="task-list">
                    <?php foreach ($this_week_tasks as $task): ?>
                        <div class="task-card" 
                             <?php if (isset($task['team_id'])): ?>
                                 onclick="window.location.href='teamdetails.php?team_id=<?php echo $task['team_id']; ?>'"
                             <?php endif; ?>>
                            <h3><?php echo htmlspecialchars($task['task_name']); ?></h3>
                            <p>Due: <?php echo htmlspecialchars($task['deadline'] ?? 'Not set'); ?></p>
                            <p>Status: <?php echo htmlspecialchars($task['status'] ?? 'Unknown'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No tasks due this week.</p>
            <?php endif; ?>
        </div>

        <!-- Tasks Due Next Week Section -->
        <div class="task-section">
            <h2>Tasks Due Next Week</h2>
            <?php if (!empty($next_week_tasks)): ?>
                <div class="task-list">
                    <?php foreach ($next_week_tasks as $task): ?>
                        <div class="task-card" 
                             <?php if (isset($task['team_id'])): ?>
                                 onclick="window.location.href='teamdetails.php?team_id=<?php echo $task['team_id']; ?>'"
                             <?php endif; ?>>
                            <h3><?php echo htmlspecialchars($task['task_name']); ?></h3>
                            <p>Due: <?php echo htmlspecialchars($task['deadline'] ?? 'Not set'); ?></p>
                            <p>Status: <?php echo htmlspecialchars($task['status'] ?? 'Unknown'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No tasks due next week.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const sideNav = document.getElementById('sideNav');
        const menuIcon = document.getElementById('menuIcon');
        const closeBtn = document.getElementById('closeBtn');
        const contentArea = document.getElementById('contentArea');

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
    </script>
</body>
</html>