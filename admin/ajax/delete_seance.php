<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Méthode non autorisée']));
}

checkUserSession('admin');

header('Content-Type: application/json');


try {
    $data = json_decode(file_get_contents('php://input'), true);
    $seance_id = $data['id'];

    $conn->beginTransaction();

    // Vérifier que la séance existe et n'est pas déjà terminée
    $stmt = $conn->prepare("
        SELECT statut 
        FROM seances 
        WHERE id = ?
    ");
    $stmt->execute([$seance_id]);
    $seance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seance) {
        throw new Exception('Séance non trouvée');
    }

    if ($seance['statut'] !== 'programmee') {
        throw new Exception('Impossible de supprimer une séance en cours ou terminée');
    }

    // Supprimer les présences associées si elles existent
    $stmt = $conn->prepare("DELETE FROM presences WHERE id_seance = ?");
    $stmt->execute([$seance_id]);

    // Supprimer les conflits de place associés si ils existent
    $stmt = $conn->prepare("DELETE FROM conflits_place WHERE id_seance = ?");
    $stmt->execute([$seance_id]);

    // Supprimer la séance
    $stmt = $conn->prepare("DELETE FROM seances WHERE id = ?");
    $stmt->execute([$seance_id]);

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Séance supprimée avec succès'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>