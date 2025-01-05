<?php
require_once '../../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('professeur');
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $seance_id = $data['seance_id'];
    $etudiant_id = $data['etudiant_id'];

    $conn->beginTransaction();

    // Vérifier que la séance est en cours
    $stmt = $conn->prepare("SELECT statut FROM seances WHERE id = ? AND id_professeur = ?");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);
    $seance = $stmt->fetch();

    if (!$seance || $seance['statut'] !== 'en_cours') {
        throw new Exception("Opération non autorisée");
    }

    // Supprimer présence existante si elle existe
    $stmt = $conn->prepare("DELETE FROM presences WHERE id_seance = ? AND id_etudiant = ?");
    $stmt->execute([$seance_id, $etudiant_id]);

    // Marquer absent
    $stmt = $conn->prepare("
        INSERT INTO presences (id_seance, id_etudiant, status)
        VALUES (?, ?, 'absent')
    ");
    $stmt->execute([$seance_id, $etudiant_id]);

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>