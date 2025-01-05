<?php
require_once 'config/session_config.php';

if (isset($_SESSION['user_type'])) {
    startUserSession($_SESSION['user_type']);
    session_destroy();
}

header('Location: login.php');
exit();
?>
