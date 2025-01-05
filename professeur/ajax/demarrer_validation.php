<?php
require_once '../../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('professeur');
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['seance_id'])) {
        throw new Exception('Données manquantes');
    }
    
    $seance_id = $data['seance_id'];

    // Vérifier que la séance appartient au professeur
    $stmt = $conn->prepare("
        SELECT id FROM seances 
        WHERE id = ? AND id_professeur = ?
    ");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Séance non autorisée');
    }

    // Activer la validation
    $stmt = $conn->prepare("
        UPDATE seances 
        SET validation_active = TRUE 
        WHERE id = ? AND id_professeur = ?
    ");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Validation des présences activée'
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
