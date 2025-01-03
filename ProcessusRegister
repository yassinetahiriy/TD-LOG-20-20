<?php
require_once 'functions/auth_functions.php';
require_once 'functions/upload_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['email']; // Utilisation de l'email comme username
    $email = $_POST['email'];
    $password = $_POST['password'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $user_type = $_POST['user_type'];
    
    $result = createUser($username, $email, $password, $nom, $prenom, $user_type, $_FILES['photo']);
    
    if ($result['success']) {
        header('Location: login.php?message=success');
        exit;
    } else {
        header('Location: register.php?error=' . urlencode($result['message']));
        exit;
    }
}
?>
