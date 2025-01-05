<?php
function startUserSession($user_type) {
    // Définir un nom unique pour chaque type d'utilisateur
    $session_name = 'my_app_' . $user_type;
    session_name($session_name);
    session_start();
}

// Pour vérifier la session en cours
function checkUserSession($required_type) {
    $session_name = 'my_app_' . $required_type;
    if (session_name() !== $session_name) {
        session_write_close(); // Fermer la session actuelle si elle existe
        session_name($session_name);
        session_start();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== $required_type) {
        header('Location: ../login.php');
        exit();
    }
}
?>
