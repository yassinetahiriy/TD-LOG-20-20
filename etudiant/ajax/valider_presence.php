<?php
require_once '../../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('etudiant');
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['presence_id'])) {
        throw new Exception('Données manquantes');
    }

    $presence_id = $data['presence_id'];

    // Vérifier si la validation est active pour cette séance
    $stmt = $conn->prepare("
        SELECT s.validation_active, s.statut 
        FROM seances s 
        JOIN presences p ON s.id = p.id_seance 
        WHERE p.id = ?
    ");
    $stmt->execute([$presence_id]);
    $seance = $stmt->fetch();

    if (!$seance) {
        throw new Exception('Présence non trouvée');
    }

    if (!$seance['validation_active']) {
        throw new Exception('La validation des présences n\'a pas encore été activée par le professeur');
    }

    if ($seance['statut'] !== 'en_cours') {
        throw new Exception('La séance n\'est plus en cours');
    }

    // Vérifier que l'étudiant n'a pas déjà validé 4 présences
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM validations_presence v
        JOIN presences p ON v.id_presence = p.id
        JOIN seances s ON p.id_seance = s.id
        WHERE v.id_validateur = ? AND s.id = p.id_seance
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $count = $stmt->fetch()['count'];
    if ($count >= 4) {
        throw new Exception('Vous avez déjà validé 4 présences pour cette séance');
    }

    // Vérifier que l'étudiant ne valide pas sa propre présence
    $stmt = $conn->prepare("
        SELECT id_etudiant 
        FROM presences 
        WHERE id = ?
    ");
    $stmt->execute([$presence_id]);
    $presence = $stmt->fetch();
    if ($presence['id_etudiant'] === $_SESSION['user_id']) {
        throw new Exception('Vous ne pouvez pas valider votre propre présence');
    }

    // Vérifier que la validation n'existe pas déjà
    $stmt = $conn->prepare("
        SELECT id 
        FROM validations_presence 
        WHERE id_presence = ? AND id_validateur = ?
    ");
    $stmt->execute([$presence_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        throw new Exception('Vous avez déjà validé cette présence');
    }

    // Ajouter la validation
    $stmt = $conn->prepare("
        INSERT INTO validations_presence (id_presence, id_validateur, date_validation)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$presence_id, $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Présence validée avec succès'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
