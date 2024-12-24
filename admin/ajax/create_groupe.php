<?php
require_once '../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('admin');

header('Content-Type: application/json');


try {
    $conn->beginTransaction();

    // Récupération des données
    $nom_groupe = $_POST['nom_groupe'];
    $annee_scolaire = $_POST['annee_scolaire'];
    $etudiants = json_decode($_POST['etudiants']);

    // Validation
    if (empty($nom_groupe) || empty($annee_scolaire) || empty($etudiants)) {
        throw new Exception('Tous les champs sont requis');
    }

    // Créer le groupe
    $stmt = $conn->prepare("
        INSERT INTO groupes_td (nom_groupe, annee_scolaire)
        VALUES (?, ?)
    ");
    $stmt->execute([$nom_groupe, $annee_scolaire]);
    $groupe_id = $conn->lastInsertId();

    // Ajouter les étudiants au groupe
    $stmt = $conn->prepare("
        INSERT INTO groupes_td_etudiants (id_groupe_td, id_etudiant)
        VALUES (?, ?)
    ");

    foreach ($etudiants as $etudiant_id) {
        $stmt->execute([$groupe_id, $etudiant_id]);
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Groupe créé avec succès'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>