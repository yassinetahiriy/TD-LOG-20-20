<?php
require_once '../config/database.php';

function createUser($username, $email, $password, $nom, $prenom, $user_type, $photo) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Vérifier si l'email existe déjà
        $check_query = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$email]);
        
        if ($check_stmt->rowCount() > 0) {
            return ["success" => false, "message" => "Cet email existe déjà"];
        }

        // Traitement de l'image
        $photo_path = "";
        if ($photo['error'] === 0) {
            $photo_path = handleFileUpload($photo);
            if (!$photo_path) {
                return ["success" => false, "message" => "Erreur lors de l'upload de la photo"];
            }
        }

        // Créer l'utilisateur
        $query = "INSERT INTO users (username, email, password, nom, prenom, user_type, photo_url) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->execute([
            $username,
            $email,
            $hashed_password,
            $nom,
            $prenom,
            $user_type,
            $photo_path
        ]);
        
        return ["success" => true, "message" => "Compte créé avec succès"];
        
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de la création du compte: " . $e->getMessage()];
    }
}

function loginUser($email, $password) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                
                return ["success" => true, "user_type" => $user['user_type']];
            }
        }
        
        return ["success" => false, "message" => "Email ou mot de passe incorrect"];
        
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur de connexion: " . $e->getMessage()];
    }
}
?>
