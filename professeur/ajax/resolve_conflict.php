<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('professeur');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$conflit_id = $data['conflit_id'];
$etudiant_confirme_id = $data['etudiant_confirme_id'];

try {
    $conn->beginTransaction();

    // Vérifier l'autorisation et récupérer les informations du conflit
    $stmt = $conn->prepare("
        SELECT c.*, s.id_professeur 
        FROM conflits_place c
        JOIN seances s ON c.id_seance = s.id
        WHERE c.id = ? AND s.id_professeur = ? AND c.status = 'en_attente'
    ");
    $stmt->execute([$conflit_id, $_SESSION['user_id']]);
    $conflit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conflit) {
        throw new Exception('Conflit non trouvé ou non autorisé');
    }

    // Vérifier que l'étudiant confirmé fait partie du conflit
    if ($etudiant_confirme_id != $conflit['id_etudiant1'] && $etudiant_confirme_id != $conflit['id_etudiant2']) {
        throw new Exception('Étudiant non valide pour ce conflit');
    }

    // Marquer l'autre étudiant comme absent
    $etudiant_absent_id = ($etudiant_confirme_id == $conflit['id_etudiant1']) 
        ? $conflit['id_etudiant2'] 
        : $conflit['id_etudiant1'];

    // Supprimer l'ancienne présence de l'étudiant non confirmé
    $stmt = $conn->prepare("
        DELETE FROM presences 
        WHERE id_seance = ? AND id_etudiant = ?
    ");
    $stmt->execute([$conflit['id_seance'], $etudiant_absent_id]);

    // Marquer l'étudiant non confirmé comme absent
    $stmt = $conn->prepare("
        INSERT INTO presences (id_seance, id_etudiant, status, heure_marquage)
        VALUES (?, ?, 'absent', NOW())
    ");
    $stmt->execute([$conflit['id_seance'], $etudiant_absent_id]);

    // Mettre à jour le statut du conflit
    $stmt = $conn->prepare("
        UPDATE conflits_place 
        SET status = 'resolu',
            resolution_timestamp = NOW(),
            id_etudiant_confirme = ?
        WHERE id = ?
    ");
    $stmt->execute([$etudiant_confirme_id, $conflit_id]);

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>