<?php
require_once '../../config/session_config.php';
require_once '../../config/database.php';

checkUserSession('professeur');
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $seance_id = $data['seance_id'];
    $numero_place = $data['numero_place'];

    // Récupérer les détails de l'étudiant et ses validations
    $stmt = $conn->prepare("
        SELECT p.*, u.nom, u.prenom, u.photo_url,
               COUNT(v.id) as nb_validations,
               GROUP_CONCAT(DISTINCT CONCAT(val.prenom, ' ', val.nom)) as validateurs
        FROM presences p
        JOIN users u ON p.id_etudiant = u.id
        LEFT JOIN validations_presence v ON p.id = v.id_presence
        LEFT JOIN users val ON v.id_validateur = val.id
        WHERE p.id_seance = ? AND p.numero_place = ?
        GROUP BY p.id
    ");
    $stmt->execute([$seance_id, $numero_place]);
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    $html = "
        <div class='etudiant-info'>
            <img src='../{$etudiant['photo_url']}' alt='Photo'>
            <div>
                <h4>{$etudiant['prenom']} {$etudiant['nom']}</h4>
                <p>Place: {$etudiant['numero_place']}</p>
                <p>Heure d'arrivée: " . date('H:i', strtotime($etudiant['heure_marquage'])) . "</p>
            </div>
            <button onclick='marquerAbsent({$etudiant['id_etudiant']})' class='btn btn-end'>
                Marquer absent
            </button>
        </div>";

    if ($etudiant['nb_validations'] > 0) {
        $html .= "
            <div class='validations'>
                <p>Validé par {$etudiant['nb_validations']} étudiant(s):</p>
                <p>{$etudiant['validateurs']}</p>
            </div>";
    }

    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>