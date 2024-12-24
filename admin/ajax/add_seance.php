<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Méthode non autorisée']));
}

checkUserSession('admin');

header('Content-Type: application/json');

try {
    $conn->beginTransaction();

    // Récupérer les données du formulaire
    $date_seance = $_POST['date_seance'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $id_professeur = $_POST['id_professeur'];
    $id_salle = $_POST['id_salle'];
    $id_groupe_td = $_POST['id_groupe_td'];

    // Validation des données
    if (strtotime($date_seance) < strtotime(date('Y-m-d'))) {
        throw new Exception('La date ne peut pas être dans le passé');
    }

    if ($heure_debut >= $heure_fin) {
        throw new Exception("L'heure de fin doit être après l'heure de début");
    }

    // Vérifier que la salle n'est pas déjà réservée
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM seances 
        WHERE date_seance = ? 
        AND id_salle = ?
        AND (
            (heure_debut BETWEEN ? AND ?) 
            OR (heure_fin BETWEEN ? AND ?)
            OR (? BETWEEN heure_debut AND heure_fin)
            OR (? BETWEEN heure_debut AND heure_fin)
        )
    ");
    $stmt->execute([
        $date_seance, 
        $id_salle, 
        $heure_debut, $heure_fin,
        $heure_debut, $heure_fin,
        $heure_debut, $heure_fin
    ]);

    if ($stmt->fetchColumn() > 0) {
        throw new Exception('La salle est déjà réservée pour ce créneau');
    }

    // Vérifier que le professeur n'est pas déjà occupé
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM seances 
        WHERE date_seance = ? 
        AND id_professeur = ?
        AND (
            (heure_debut BETWEEN ? AND ?) 
            OR (heure_fin BETWEEN ? AND ?)
            OR (? BETWEEN heure_debut AND heure_fin)
            OR (? BETWEEN heure_debut AND heure_fin)
        )
    ");
    $stmt->execute([
        $date_seance, 
        $id_professeur, 
        $heure_debut, $heure_fin,
        $heure_debut, $heure_fin,
        $heure_debut, $heure_fin
    ]);

    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Le professeur est déjà occupé sur ce créneau');
    }

    // Vérifier que le groupe n'est pas déjà en cours
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM seances 
        WHERE date_seance = ? 
        AND id_groupe_td = ?
        AND (
            (heure_debut BETWEEN ? AND ?) 
            OR (heure_fin BETWEEN ? AND ?)
            OR (? BETWEEN heure_debut AND heure_fin)
            OR (? BETWEEN heure_debut AND heure_fin)
        )
    ");
    $stmt->execute([
        $date_seance, 
        $id_groupe_td, 
        $heure_debut, $heure_fin,
        $heure_debut, $heure_fin,
        $heure_debut, $heure_fin
    ]);

    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Le groupe a déjà un cours sur ce créneau');
    }

    // Ajouter la séance
    $stmt = $conn->prepare("
        INSERT INTO seances (
            date_seance,
            heure_debut,
            heure_fin,
            id_professeur,
            id_salle,
            id_groupe_td,
            statut,
            created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 'programmee', NOW()
        )
    ");

    $stmt->execute([
        $date_seance,
        $heure_debut,
        $heure_fin,
        $id_professeur,
        $id_salle,
        $id_groupe_td
    ]);

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Séance ajoutée avec succès'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>