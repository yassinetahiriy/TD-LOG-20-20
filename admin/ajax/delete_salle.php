<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('admin');

header('Content-Type: application/json');


try {
    $conn->beginTransaction();

    $data = json_decode(file_get_contents('php://input'), true);
    $salle_id = $data['id'];

    // Vérifier si la salle existe
    $stmt = $conn->prepare("SELECT id FROM salles WHERE id = ?");
    $stmt->execute([$salle_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Salle non trouvée');
    }

    // Vérifier si la salle n'est pas utilisée dans des séances
    $stmt = $conn->prepare("SELECT id FROM seances WHERE id_salle = ?");
    $stmt->execute([$salle_id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Impossible de supprimer la salle car elle est utilisée dans des séances');
    }

    // Supprimer les places de la salle
    $stmt = $conn->prepare("DELETE FROM places WHERE id_salle = ?");
    $stmt->execute([$salle_id]);

    // Supprimer la salle
    $stmt = $conn->prepare("DELETE FROM salles WHERE id = ?");
    $stmt->execute([$salle_id]);

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Salle supprimée avec succès'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>