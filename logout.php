<?php
session_start();
session_unset();  
session_destroy(); 

if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 42000, '/');
}

header("Location: ../autho/login.php?message=You have been logged out.");
exit();
