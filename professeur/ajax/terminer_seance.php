<?php
require_once '../../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('professeur');
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $seance_id = $data['seance_id'];

    $conn->beginTransaction();

    // Vérifier que la séance appartient au professeur
    $stmt = $conn->prepare("SELECT id_groupe_td FROM seances WHERE id = ? AND id_professeur = ?");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);
    $seance = $stmt->fetch();

    if (!$seance) {
        throw new Exception("Séance non trouvée");
    }

    // Récupérer tous les étudiants du groupe non marqués présents
    $stmt = $conn->prepare("
        SELECT id_etudiant 
        FROM groupes_td_etudiants 
        WHERE id_groupe_td = ?
        AND id_etudiant NOT IN (
            SELECT id_etudiant FROM presences WHERE id_seance = ?
        )
    ");
    $stmt->execute([$seance['id_groupe_td'], $seance_id]);
    $absents = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Marquer les absents avec numero_place = 0
    if (!empty($absents)) {
        $stmt = $conn->prepare("
            INSERT INTO presences (id_seance, id_etudiant, status, numero_place)
            VALUES (?, ?, 'absent', 0)
        ");
        foreach ($absents as $etudiant_id) {
            $stmt->execute([$seance_id, $etudiant_id]);
        }
    }

    // Terminer la séance
    $stmt = $conn->prepare("UPDATE seances SET statut = 'terminee' WHERE id = ?");
    $stmt->execute([$seance_id]);

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}