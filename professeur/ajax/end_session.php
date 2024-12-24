<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('professeur');

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$seance_id = $data['seance_id'];

try {
    $conn->beginTransaction();

    // Vérifier l'autorisation
    $stmt = $conn->prepare("SELECT id FROM seances WHERE id = ? AND id_professeur = ?");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Non autorisé');
    }

    // Récupérer tous les étudiants du groupe qui ne sont pas marqués présents
    $stmt = $conn->prepare("
        SELECT DISTINCT u.id 
        FROM users u
        JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
        JOIN seances s ON s.id_groupe_td = gte.id_groupe_td
        LEFT JOIN presences p ON u.id = p.id_etudiant AND p.id_seance = ?
        WHERE s.id = ? 
        AND u.user_type = 'etudiant'
        AND p.id IS NULL
    ");
    $stmt->execute([$seance_id, $seance_id]);
    $absents = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Marquer les étudiants absents
    if (!empty($absents)) {
        $stmt = $conn->prepare("
            INSERT INTO presences (id_seance, id_etudiant, status, heure_marquage)
            VALUES (?, ?, 'absent', NOW())
        ");
        
        foreach ($absents as $etudiant_id) {
            $stmt->execute([$seance_id, $etudiant_id]);
        }
    }

    // Mettre à jour le statut de la séance
    $stmt = $conn->prepare("UPDATE seances SET statut = 'terminee' WHERE id = ?");
    $stmt->execute([$seance_id]);

    // Valider toutes les modifications
    $conn->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>