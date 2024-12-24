<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('admin');

header('Content-Type: application/json');


try {
    $conn->beginTransaction();

    // Récupération des données du formulaire
    $salle_id = $_POST['salle_id'] ?? null;
    $nom_salle = $_POST['nom_salle'];
    $type_salle = $_POST['type_salle'];
    $capacite = $_POST['capacite'];

    // Validation
    if (empty($nom_salle) || empty($type_salle) || empty($capacite)) {
        throw new Exception('Tous les champs sont requis');
    }

    if ($salle_id) {
        // Mise à jour d'une salle existante
        $stmt = $conn->prepare("
            UPDATE salles 
            SET nom_salle = ?, type_salle = ?, capacite = ?
            WHERE id = ?
        ");
        $stmt->execute([$nom_salle, $type_salle, $capacite, $salle_id]);
    } else {
        // Création d'une nouvelle salle
        $stmt = $conn->prepare("
            INSERT INTO salles (nom_salle, type_salle, capacite)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$nom_salle, $type_salle, $capacite]);
        $salle_id = $conn->lastInsertId();
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Salle ' . ($salle_id ? 'modifiée' : 'créée') . ' avec succès'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>