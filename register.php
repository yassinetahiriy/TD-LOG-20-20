<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Définir le chemin absolu du dossier uploads
$upload_dir = __DIR__ . '/uploads/';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $user_type = $_POST['user_type'];
        
        // Traitement de l'image
        $photo_url = "";
        if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            // Vérifier si le dossier uploads existe, sinon le créer
            if (!file_exists($upload_dir)) {
                // Créer le dossier avec les bonnes permissions
                if (!mkdir($upload_dir, 0777, true)) {
                    die("Erreur : Impossible de créer le dossier uploads. Veuillez vérifier les permissions.");
                }
                // S'assurer que les permissions sont correctement définies
                chmod($upload_dir, 0777);
            }
            
            $allowed = ["jpg", "jpeg", "png"];
            $filename = $_FILES["photo"]["name"];
            $filetype = $_FILES["photo"]["type"];
            $filesize = $_FILES["photo"]["size"];
            
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if(!in_array($ext, $allowed)) {
                die("Erreur : Format de fichier non autorisé. Utilisez JPG, JPEG ou PNG.");
            }
            
            $maxsize = 5 * 1024 * 1024;
            if($filesize > $maxsize) {
                die("Erreur : La taille du fichier dépasse 5MB.");
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $target_file = $upload_dir . $new_filename;
            
            // Déboguer les permissions et le déplacement du fichier
            echo "Dossier temporaire : " . $_FILES["photo"]["tmp_name"] . "<br>";
            echo "Destination : " . $target_file . "<br>";
            echo "Permissions du dossier uploads : " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";
            
            if(move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $photo_url = 'uploads/' . $new_filename;
                chmod($target_file, 0644); // Définir les permissions du fichier
            } else {
                die("Erreur : Impossible de déplacer le fichier uploadé. Erreur PHP : " . error_get_last()['message']);
            }
        }

        // Vérifier si l'email existe déjà
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if($check->rowCount() > 0) {
            die("Erreur : Cet email est déjà utilisé.");
        }

        $sql = "INSERT INTO users (username, email, password, nom, prenom, user_type, photo_url) 
                VALUES (:email, :email, :password, :nom, :prenom, :user_type, :photo_url)";
        
        $stmt = $conn->prepare($sql);
        
        $result = $stmt->execute([
            ':email' => $email,
            ':password' => $password,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':user_type' => $user_type,
            ':photo_url' => $photo_url
        ]);
        
        if($result) {
            echo "Inscription réussie! Redirection vers la page de connexion...";
            header("refresh:3;url=login.php");
        } else {
            echo "Erreur lors de l'inscription.";
        }
        
    } catch(PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    } catch(Exception $e) {
        die("Erreur : " . $e->getMessage());
    }
}
?>

<!-- Le reste du HTML reste identique -->

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Inscription</h2>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" required>
        </div>

        <div class="form-group">
            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>

        <div class="form-group">
            <label for="user_type">Type d'utilisateur:</label>
            <select id="user_type" name="user_type" required>
                <option value="">Sélectionnez un type</option>
                <option value="admin">Administrateur</option>
                <option value="professeur">Professeur</option>
                <option value="etudiant">Étudiant</option>
            </select>
        </div>

        <div class="form-group">
            <label for="photo">Photo:</label>
            <input type="file" id="photo" name="photo" accept="image/*" required>
        </div>

        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
