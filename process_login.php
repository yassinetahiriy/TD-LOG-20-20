<?php
require_once 'config/database.php';
require_once 'config/session_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Vérifier si l'utilisateur existe
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Démarrer une nouvelle session pour ce type d'utilisateur
            startUserSession($user['user_type']);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];

            // Redirection selon le type d'utilisateur
            switch($user['user_type']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'professeur':
                    header('Location: professeur/dashboard.php');
                    break;
                case 'etudiant':
                    header('Location: etudiant/dashboard.php');
                    break;
            }
            exit();
        } else {
            $_SESSION['error_message'] = "Email ou mot de passe incorrect";
            header('Location: login.php');
            exit();
        }
    } catch(Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: login.php');
        exit();
    }
}
?>
