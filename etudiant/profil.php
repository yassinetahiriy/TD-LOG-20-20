<?php
session_start();
require_once '../config/database.php';

checkUserSession('etudiant');

header('Content-Type: application/json');

try {
    // Récupérer les informations de l'étudiant
    $stmt = $conn->prepare("
        SELECT u.*, g.nom_groupe
        FROM users u
        LEFT JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
        LEFT JOIN groupes_td g ON gte.id_groupe_td = g.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $new_photo = $_FILES['photo'] ?? null;

        // Mise à jour du mot de passe si fourni
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                throw new Exception('Les mots de passe ne correspondent pas');
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        }

        // Mise à jour de la photo si fournie
        if ($new_photo && $new_photo['error'] === 0) {
            $allowed = ["jpg", "jpeg", "png"];
            $filename = $new_photo["name"];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception('Format de fichier non autorisé');
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = "../uploads/";
            
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            if (move_uploaded_file($new_photo["tmp_name"], $upload_path . $new_filename)) {
                $photo_url = 'uploads/' . $new_filename;
                $stmt = $conn->prepare("UPDATE users SET photo_url = ? WHERE id = ?");
                $stmt->execute([$photo_url, $_SESSION['user_id']]);
                $user['photo_url'] = $photo_url;
            }
        }

        $success = "Profil mis à jour avec succès";
    }

} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mon Profil</h1>
            <a href="dashboard.php" class="btn">Retour</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($user['photo_url']); ?>" alt="Photo de profil" class="profile-photo">
            <div>
                <h2><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h2>
                <p>Groupe: <?php echo htmlspecialchars($user['nom_groupe'] ?? 'Non assigné'); ?></p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" id="new_password" name="new_password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <div class="form-group">
                <label for="photo">Changer la photo de profil</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>

            <button type="submit" class="btn">Mettre à jour</button>
        </form>
    </div>
</body>
</html>