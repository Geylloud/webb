<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: autho/login.php");
    exit();
}

$team_name = require '../processing/teamdetailspr.php';
require '../processing/teamdetailspr.php';
$team_id = $_GET['team_id'] ?? '';

// Debug: Log the team_id to check its value
error_log("team_id received: " . var_export($team_id, true));

if (!$team_id || !is_numeric($team_id)) {
    $error_message = "No valid board selected. Please choose a board.";
    $tasks = []; // Avoid undefined variable issues
} else {
    $lists_data = include '../processing/show_lists.php';
    if (isset($lists_data['error'])) {
        $error_message = $lists_data['error'];
        $tasks = [];
    } else {
        $lists = $lists_data;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/finalproject/processing/fetch_tasks.php?team_id=" . urlencode($team_id));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $task_response = curl_exec($ch);
        if ($task_response === false) {
            $task_error = "cURL error: " . curl_error($ch);
            $tasks = [];
        } else {
            $tasks_data = json_decode($task_response, true);
            if ($tasks_data['success']) {
                $tasks = $tasks_data['tasks'];
            } else {
                $tasks = [];
                $task_error = $tasks_data['error'] ?? 'Failed to fetch tasks';
            }
        }
        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
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
            --trello-blue: #0079bf;
            --trello-list-bg: #ebecf0;
            --trello-text: #172b4d;
            --trello-green: #5aac44;
            --trello-add-bg: #091e4221;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        body { background-color: var(--background-color); color: #333; overflow-x: auto; overflow-y: hidden; min-height: 100vh; }
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
        .logo { display: flex; align-items: center; text-decoration: none; margin-right: 20px; }
        .logo img { width: 150px; height: 40px; margin-right: 10px; }
        .menu-icon { display: none; flex-direction: column; cursor: pointer; padding: 5px; }
        .menu-icon span { width: 25px; height: 3px; background-color: var(--text-color); margin: 2px 0; transition: 0.3s; }
        .right-section { display: flex; align-items: center; gap: 15px; }
        .notifications { position: relative; cursor: pointer; }
        .bell-icon { font-size: 24px; color: var(--text-color); }
        .notification-badge { position: absolute; top: -5px; right: -5px; background-color: red; color: white; font-size: 12px; width: 15px; height: 15px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .user-profile { position: relative; display: flex; align-items: center; }
        .profile-img { width: 40px; height: 40px; background-color: var(--accent-color); color: white; font-size: 18px; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 50%; text-transform: uppercase; cursor: pointer; }
        .profile-dropdown { position: absolute; top: 50px; right: 0; background: var(--card-bg); box-shadow: var(--shadow); border-radius: 8px; display: none; flex-direction: column; width: 180px; padding: 10px; }
        .profile-dropdown p { margin: 5px 0; padding: 5px; font-size: 14px; text-align: center; color: #333; }
        .profile-dropdown a { text-decoration: none; color: var(--primary-color); text-align: center; padding: 8px; display: block; font-size: 14px; font-weight: bold; border-radius: 5px; transition: 0.3s; }
        .profile-dropdown a:hover { background-color: var(--accent-color); color: white; }
        .user-profile:hover .profile-dropdown { display: flex; }
        .side-nav { height: calc(100vh - 60px); width: 250px; position: fixed; top: 60px; left: 0; background-color: var(--secondary-color); padding: 20px; transition: 0.3s; z-index: 999; box-shadow: var(--shadow); color: var(--text-color); }
        .side-nav-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .side-nav-header h2 { font-size: 18px; font-weight: bold; }
        .close-btn { font-size: 24px; cursor: pointer; color: var(--text-color); }
        .side-nav-links { list-style: none; padding: 0; }
        .side-nav-links li { margin-bottom: 15px; }
        .side-nav-links li a { color: var(--text-color); text-decoration: none; font-size: 16px; font-weight: 500; padding: 10px; display: block; border-radius: 5px; transition: 0.3s; }
        .side-nav-links li a:hover { background-color: var(--hover-bg); }
        .content { position: fixed; top: 60px; left: 0; width: 100%; height: calc(100vh - 60px); margin: 0; padding: 0; transition: all 0.3s; text-align: center; font-family: 'Roboto', sans-serif; overflow: hidden; }
        .content.sidebar-open { left: 250px; width: calc(100% - 250px); }
        .content.sidebar-closed { left: 0; width: 100%; }
        .trello-header { background-color: var(--trello-blue); color: white; padding: 10px 20px; display: flex; align-items: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin: 0; border-radius: 0; width: 100%; }
        .trello-header h1 { margin: 0; font-size: 20px; font-weight: 500; }
        .trello-board { display: flex; flex-direction: row; gap: 10px; padding: 10px; background-color: #f4f5f7; width: 100%; height: calc(100% - 40px); overflow-x: auto; overflow-y: hidden; flex-wrap: nowrap; white-space: nowrap; }
        .trello-list { background-color: var(--trello-list-bg); width: 270px; min-width: 270px; border-radius: 3px; padding: 10px; box-shadow: 0 1px 0 rgba(9, 30, 66, 0.25); height: calc(100% - 20px); display: inline-block; vertical-align: top; }
        .trello-list h3 { font-size: 14px; font-weight: 600; color: var(--trello-text); padding: 4px 8px; margin: 0 0 10px 0; }
        .trello-list .cards { display: flex; flex-direction: column; gap: 8px; min-height: 50px; max-height: calc(100% - 80px); overflow-y: auto; }
        .trello-list .card { background-color: var(--card-bg); padding: 8px; border-radius: 3px; box-shadow: 0 1px 0 rgba(9, 30, 66, 0.25); font-size: 14px; color: var(--trello-text); cursor: move; transition: background-color 0.2s; }
        .trello-list .card:hover { background-color: #f4f5f7; }
        .trello-list .card .task-name { font-weight: bold; margin-bottom: 4px; display: flex; align-items: center; }
        .trello-list .card .task-name input[type="checkbox"] { margin-right: 8px; }
        .trello-list .card .task-name input[type="checkbox"]:disabled { cursor: not-allowed; opacity: 0.5; }
        .trello-list .card .status, .trello-list .card .assigned-to, .trello-list .card .deadline { font-size: 12px; color: #5e6c84; }
        .trello-list .due-date { font-size: 12px; color: #5e6c84; margin-top: 4px; }
        .trello-list .add-form { margin-top: 10px; display: flex; flex-direction: column; gap: 8px; }
        .trello-list .add-form input[type="text"], .trello-list .add-form input[type="date"] { width: 100%; padding: 8px; border: 1px solid #dfe1e6; border-radius: 3px; font-size: 14px; }
        .trello-list .add-form button, .add-task-btn { background-color: var(--trello-green); color: white; border: none; padding: 8px 12px; border-radius: 3px; font-size: 14px; cursor: pointer; transition: background-color 0.3s; width: 100%; text-align: center; }
        .trello-list .add-form button:hover, .add-task-btn:hover { background-color: #519839; }
        .trello-list p { font-size: 14px; color: var(--trello-text); padding: 10px; text-align: center; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1001; }
        .modal-content { background-color: var(--card-bg); width: 400px; max-width: 90%; margin: 100px auto; padding: 20px; border-radius: 8px; box-shadow: var(--shadow); }
        .modal-content h2 { font-size: 18px; margin-bottom: 15px; color: var(--trello-text); }
        .modal-content label { display: block; font-size: 14px; color: var(--trello-text); margin-bottom: 5px; }
        .modal-content input, .modal-content select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #dfe1e6; border-radius: 3px; font-size: 14px; }
        .modal-content #assignedToCheckboxes { max-height: 100px; overflow-y: auto; margin-bottom: 10px; }
        .modal-content #assignedToCheckboxes label { display: flex; align-items: center; padding: 4px 0; font-size: 14px; color: var(--trello-text); }
        .modal-content #assignedToCheckboxes input[type="checkbox"] { margin-right: 8px; }
        .modal-content .buttons { display: flex; gap: 10px; }
        .modal-content button { flex: 1; padding: 8px; border: none; border-radius: 3px; font-size: 14px; cursor: pointer; }
        .modal-content .submit-btn { background-color: var(--trello-green); color: white; }
        .modal-content .submit-btn:hover { background-color: #519839; }
        .modal-content .cancel-btn { background-color: #dfe1e6; color: var(--trello-text); }
        .modal-content .cancel-btn:hover { background-color: #ccd0d5; }
        @media (max-width: 768px) {
            .menu-icon { display: flex; }
            .search-bar { display: none; }
            .side-nav { top: 0; left: -250px; height: 100vh; }
            .content { left: 0; width: 100%; }
            .content.sidebar-open { left: 0; width: 100%; }
            .content.sidebar-closed { left: 0; width: 100%; }
            .trello-list { width: 240px; min-width: 240px; }
        }
        @media (min-width: 769px) { .menu-icon { display: none; } }
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
        <div class="trello-header">
            <h1><?php echo htmlspecialchars($team_name); ?></h1> <!-- Removed " - Project Board" -->
        </div>

        <div class="trello-board" id="trelloBoard">
            <?php if (isset($error_message)): ?>
                <div class="trello-list">
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php elseif (isset($task_error)): ?>
                <div class="trello-list">
                    <p><?php echo $task_error; ?></p>
                </div>
            <?php elseif (is_array($lists)): ?>
                <?php foreach ($lists as $list): ?>
                    <div class="trello-list" ondrop="drop(event)" ondragover="allowDrop(event)" data-list-id="<?php echo htmlspecialchars($list['id']); ?>">
                        <h3><?php echo htmlspecialchars($list['name']); ?></h3>
                        <div class="due-date">Due Date: <?php echo htmlspecialchars($list['due_date'] ?? 'None'); ?></div>
                        <div class="cards">
                            <?php
                            $list_tasks = array_filter($tasks, fn($task) => $task['list_id'] == $list['id']);
                            if (empty($list_tasks)): ?>
                                <p>No tasks yet</p>
                            <?php else: ?>
                                <?php foreach ($list_tasks as $task): 
                                    $is_admin = isset($task['admin_id']) && (string)$task['admin_id'] === (string)$_SESSION['user_id'];
                                    $assigned_ids = !empty($task['assigned_members']) ? explode(',', $task['assigned_members']) : [];
                                    $is_assigned = in_array((string)$_SESSION['user_id'], array_map('trim', $assigned_ids));
                                    $can_edit = $is_admin || $is_assigned;
                                ?>
                                    <div class="card" draggable="true" ondragstart="drag(event)" id="card<?php echo htmlspecialchars($task['id']); ?>">
                                        <div class="task-name">
                                            <input type="checkbox" 
                                                   name="task_<?php echo htmlspecialchars($task['id']); ?>" 
                                                   data-task-id="<?php echo htmlspecialchars($task['id']); ?>"
                                                   <?php echo $task['status'] === 'Done' ? 'checked' : ''; ?>
                                                   <?php echo $can_edit ? '' : 'disabled'; ?>>
                                            <?php echo htmlspecialchars($task['task_name']); ?>
                                        </div>
                                        <div class="status">Status: <?php echo htmlspecialchars($task['status']); ?></div>
                                        <div class="assigned-to">Assigned: <?php echo htmlspecialchars($task['assigned_names']); ?></div>
                                        <div class="deadline">Deadline: <?php echo htmlspecialchars($task['deadline'] ?? 'None'); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button class="add-task-btn" onclick="showAddTaskModal('<?php echo htmlspecialchars($list['name']); ?>', '<?php echo htmlspecialchars($list['id']); ?>')">Add a Task</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="trello-list">
                    <p>Unable to load lists. Please try again.</p>
                </div>
            <?php endif; ?>

            <div class="trello-list">
                <form action="../processing/addlistpro.php" method="POST" class="add-form">
                    <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team_id); ?>">
                    <input type="text" name="list_name" placeholder="Enter project name..." required>
                    <label for="due_date" style="font-size: 12px; color: var(--trello-text); margin-top: 8px;">Due Date</label>
                    <input type="date" name="due_date" id="due_date">
                    <button type="submit">Create Project</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal" id="addTaskModal">
        <div class="modal-content">
            <h2>Add a Task</h2>
            <form id="addTaskForm">
                <input type="hidden" id="listId" name="list_id">
                <label for="taskName">Task Name</label>
                <input type="text" id="taskName" name="task_name" required>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="To Do">To Do</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Done">Done</option>
                </select>
                <label>Assigned To</label>
                <div id="assignedToCheckboxes"></div>
                <label for="deadline">Deadline</label>
                <input type="date" id="deadline" name="deadline">
                <div class="buttons">
                    <button type="submit" class="submit-btn">Add Task</button>
                    <button type="button" class="cancel-btn" onclick="closeAddTaskModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const sideNav = document.getElementById('sideNav');
        const menuIcon = document.getElementById('menuIcon');
        const closeBtn = document.getElementById('closeBtn');
        const contentArea = document.getElementById('contentArea');
        const modal = document.getElementById('addTaskModal');
        const form = document.getElementById('addTaskForm');
        const teamId = '<?php echo htmlspecialchars($team_id); ?>';
        const currentUserId = '<?php echo htmlspecialchars($_SESSION['user_id']); ?>';

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
            attachCheckboxListeners();
        });

        document.getElementById('bellIcon').addEventListener('click', function() {
            alert('Notifications clicked! Add your notification logic here.');
        });

        function allowDrop(event) {
            event.preventDefault();
        }

        function drag(event) {
            event.dataTransfer.setData("text", event.target.id);
        }

        function drop(event) {
            event.preventDefault();
            const cardId = event.dataTransfer.getData("text");
            const card = document.getElementById(cardId);
            const targetList = event.target.closest('.trello-list');
            if (targetList) {
                targetList.querySelector('.cards').appendChild(card);
                const listId = targetList.getAttribute('data-list-id');
                console.log(`Moved task ${cardId} to list ${listId}`);
            }
        }

        async function loadTeamMembers() {
            try {
                const response = await fetch(`../processing/get_team_members.php?team_id=${teamId}`);
                const users = await response.json();

                const checkboxContainer = document.getElementById('assignedToCheckboxes');
                if (users.error) {
                    checkboxContainer.innerHTML = '<p style="color: #5e6c84; font-size: 12px;">Error loading team members</p>';
                    return;
                }

                const filteredUsers = users.filter(user => user.id !== currentUserId);
                checkboxContainer.innerHTML = filteredUsers.length > 0 ? 
                    filteredUsers.map(user => `
                        <label>
                            <input type="checkbox" name="assigned_to[]" value="${user.id}"> ${user.name}
                        </label>
                    `).join('') :
                    '<p style="color: #5e6c84; font-size: 12px;">No assignable team members available</p>';
            } catch (error) {
                document.getElementById('assignedToCheckboxes').innerHTML = 
                    '<p style="color: #5e6c84; font-size: 12px;">Failed to load team members</p>';
                console.error('Error fetching team members:', error);
            }
        }

        function showAddTaskModal(listTitle, listId) {
            document.getElementById('listId').value = listId;
            document.querySelector('#addTaskModal h2').textContent = `Add a Task to "${listTitle}"`;
            modal.style.display = 'block';
            loadTeamMembers();
        }

        function closeAddTaskModal() {
            modal.style.display = 'none';
            form.reset();
            document.getElementById('assignedToCheckboxes').innerHTML = '';
        }

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const listId = document.getElementById('listId').value;
            const taskName = document.getElementById('taskName').value;
            const status = document.getElementById('status').value;
            const assignedToCheckboxes = document.querySelectorAll('#assignedToCheckboxes input[type="checkbox"]:checked');
            const assignedTo = Array.from(assignedToCheckboxes).map(checkbox => checkbox.value);
            const deadline = document.getElementById('deadline').value || null;

            fetch('../processing/task_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    list_id: listId,
                    task_name: taskName,
                    status: status,
                    assigned_to: assignedTo,
                    deadline: deadline,
                    team_id: teamId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const list = document.querySelector(`.trello-list[data-list-id="${listId}"]`);
                    if (list) {
                        const newCard = document.createElement('div');
                        newCard.className = 'card';
                        newCard.draggable = true;
                        newCard.id = `card${data.task_id}`;
                        const assignedText = data.assigned_to && data.assigned_to.length > 0 ?
                            data.assigned_to.map(user => user.name).join(', ') : 'None';
                        const assignedIds = data.assigned_to && data.assigned_to.length > 0 ?
                            data.assigned_to.map(user => user.id) : [];
                        const isAdmin = '<?php echo $_SESSION['user_id']; ?>' === data.admin_id;
                        const isAssigned = assignedIds.includes('<?php echo $_SESSION['user_id']; ?>');
                        const canEdit = isAdmin || isAssigned;
                        newCard.innerHTML = `
                            <div class="task-name">
                                <input type="checkbox" name="task_${data.task_id}" data-task-id="${data.task_id}" ${data.status === 'Done' ? 'checked' : ''} ${canEdit ? '' : 'disabled'}>
                                ${data.task_name}
                            </div>
                            <div class="status">Status: ${data.status}</div>
                            <div class="assigned-to">Assigned: ${assignedText}</div>
                            <div class="deadline">Deadline: ${data.deadline || 'None'}</div>
                        `;
                        newCard.ondragstart = drag;
                        const cardsContainer = list.querySelector('.cards');
                        cardsContainer.appendChild(newCard);
                        attachCheckboxListeners();
                    }
                    closeAddTaskModal();
                } else {
                    console.error('Server error:', data.error);
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Failed to add task: ' + error.message);
            });
        });

        function attachCheckboxListeners() {
            document.querySelectorAll('.card input[type="checkbox"]:not(:disabled)').forEach(checkbox => {
                checkbox.removeEventListener('change', handleCheckboxChange);
                checkbox.addEventListener('change', handleCheckboxChange);
            });
        }

        function handleCheckboxChange(event) {
            const taskId = event.target.dataset.taskId;
            const newStatus = event.target.checked ? 'Done' : 'To Do';
            const card = event.target.closest('.card');
            const statusElement = card.querySelector('.status');

            fetch('../processing/update_task_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    task_id: taskId,
                    status: newStatus
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    statusElement.textContent = `Status: ${newStatus}`;
                    console.log(`Task ${taskId} updated to ${newStatus}`);
                } else {
                    console.error('Update failed:', data.error);
                    alert('Error: ' + data.error);
                    event.target.checked = !event.target.checked;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Failed to update task: ' + error.message);
                event.target.checked = !event.target.checked;
            });
        }
    </script>
</body>
</html>