<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('admin');

header('Content-Type: application/json');


try {
    $salle_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM salles WHERE id = ?");
    $stmt->execute([$salle_id]);
    $salle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$salle) {
        throw new Exception('Salle non trouvée');
    }

    echo json_encode([
        'success' => true,
        'data' => $salle
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>