<?php
require_once '../../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('professeur');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $seance_id = $data['seance_id'];

    $conn->beginTransaction();

    // Vérifier l'état actuel de la séance
    $stmt = $conn->prepare("
        SELECT statut 
        FROM seances 
        WHERE id = ? AND id_professeur = ?
    ");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);
    $seance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seance) {
        throw new Exception('Séance non trouvée');
    }

    if ($seance['statut'] !== 'programmee') {
        throw new Exception('La séance n\'est pas en état programmée');
    }

    // Mettre à jour le statut de la séance
    $stmt = $conn->prepare("
        UPDATE seances 
        SET statut = 'en_cours',
            updated_at = NOW()
        WHERE id = ? 
        AND id_professeur = ? 
        AND statut = 'programmee'
    ");
    
    $result = $stmt->execute([$seance_id, $_SESSION['user_id']]);
    
    if (!$result) {
        throw new Exception('Erreur lors de la mise à jour de la séance');
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Séance démarrée avec succès'
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>