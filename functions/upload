<?php
function handleFileUpload($file) {
    $target_dir = "../assets/uploads/profile_photos/";
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Vérifier le type de fichier
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    // Vérifier la taille (max 5MB)
    if ($file['size'] > 5000000) {
        return false;
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return '/assets/uploads/profile_photos/' . $new_filename;
    }
    
    return false;
}
?>
