<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('admin');

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Méthode non autorisée']));
}

try {
    $etudiant_id = $_POST['etudiant_id'];
    $groupes = isset($_POST['groupes']) ? $_POST['groupes'] : [];

    $conn->beginTransaction();

    // Supprimer les anciennes assignations
    $stmt = $conn->prepare("DELETE FROM groupes_td_etudiants WHERE id_etudiant = ?");
    $stmt->execute([$etudiant_id]);

    // Ajouter les nouvelles assignations
    if (!empty($groupes)) {
        $stmt = $conn->prepare("
            INSERT INTO groupes_td_etudiants (id_groupe_td, id_etudiant) 
            VALUES (?, ?)
        ");
        foreach ($groupes as $groupe_id) {
            $stmt->execute([$groupe_id, $etudiant_id]);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>