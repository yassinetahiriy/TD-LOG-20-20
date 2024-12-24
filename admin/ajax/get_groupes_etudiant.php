<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('admin');

header('Content-Type: application/json');


try {
    $etudiant_id = $_GET['id'];
    
    $stmt = $conn->prepare("
        SELECT id_groupe_td 
        FROM groupes_td_etudiants 
        WHERE id_etudiant = ?
    ");
    $stmt->execute([$etudiant_id]);
    $groupes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'groupes' => $groupes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>