<?php
require_once '../../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('admin');
header('Content-Type: application/json');

try {
    $nom_salle = $_POST['nom_salle'];
    $type_salle = $_POST['type_salle'];
    $capacite = $_POST['capacite'];
    $salle_id = isset($_POST['salle_id']) ? $_POST['salle_id'] : null;

    // Log pour déboguer
    error_log("Données reçues : " . print_r($_POST, true));

    if (empty($nom_salle) || empty($type_salle) || empty($capacite)) {
        throw new Exception('Tous les champs sont requis');
    }

    if ($salle_id) {
        $stmt = $conn->prepare("
            UPDATE salles 
            SET nom_salle = ?, type_salle = ?, capacite = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([$nom_salle, $type_salle, $capacite, $salle_id]);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO salles (nom_salle, type_salle, capacite)
            VALUES (?, ?, ?)
        ");
        $result = $stmt->execute([$nom_salle, $type_salle, $capacite]);
    }

    if (!$result) {
        throw new Exception('Erreur lors de l\'enregistrement');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Salle ' . ($salle_id ? 'modifiée' : 'créée') . ' avec succès'
    ]);

} catch (Exception $e) {
    error_log("Erreur save_salle.php : " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
