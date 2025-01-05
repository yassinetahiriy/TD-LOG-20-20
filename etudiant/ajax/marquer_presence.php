<?php
require_once '../../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('etudiant');
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['seance_id']) || !isset($data['place_id'])) {
        throw new Exception('Données manquantes');
    }

    $seance_id = $data['seance_id'];
    $numero_place = $data['place_id'];

    // Vérifier que la séance est en cours
    $stmt = $conn->prepare("SELECT statut FROM seances WHERE id = ?");
    $stmt->execute([$seance_id]);
    $seance = $stmt->fetch();
    if (!$seance || $seance['statut'] !== 'en_cours') {
        throw new Exception('Cette séance n\'est pas en cours');
    }

    // Vérifier que l'étudiant n'a pas déjà marqué sa présence à une autre place
    $stmt = $conn->prepare("SELECT id FROM presences WHERE id_etudiant = ? AND id_seance = ?");
    $stmt->execute([$_SESSION['user_id'], $seance_id]);
    $presence = $stmt->fetch();
    if ($presence) {
        throw new Exception('Vous avez déjà marqué votre présence');
    }

    // Vérifier si la place est déjà occupée
    $stmt = $conn->prepare("SELECT id FROM presences WHERE id_seance = ? AND numero_place = ?");
    $stmt->execute([$seance_id, $numero_place]);
    if ($stmt->fetch()) {
        throw new Exception('Cette place est déjà occupée');
    }

    // Marquer la présence
    $stmt = $conn->prepare("
        INSERT INTO presences (id_seance, id_etudiant, numero_place, status, heure_marquage)
        VALUES (?, ?, ?, 'present', NOW())
    ");
    $stmt->execute([$seance_id, $_SESSION['user_id'], $numero_place]);

    echo json_encode([
        'success' => true,
        'message' => 'Présence marquée avec succès'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
