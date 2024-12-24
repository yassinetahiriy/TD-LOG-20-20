<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('professeur');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$seance_id = $data['seance_id'];
$student_id = $data['student_id'];

try {
    $conn->beginTransaction();

    // Vérifier l'autorisation
    $stmt = $conn->prepare("SELECT id FROM seances WHERE id = ? AND id_professeur = ? AND statut = 'en_cours'");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Non autorisé ou séance non active');
    }

    // Vérifier si l'étudiant n'est pas déjà marqué
    $stmt = $conn->prepare("SELECT id FROM presences WHERE id_seance = ? AND id_etudiant = ?");
    $stmt->execute([$seance_id, $student_id]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('La présence de cet étudiant a déjà été enregistrée');
    }

    // Marquer l'étudiant absent
    $stmt = $conn->prepare("
        INSERT INTO presences (id_seance, id_etudiant, status, heure_marquage)
        VALUES (?, ?, 'absent', NOW())
    ");
    $stmt->execute([$seance_id, $student_id]);

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>