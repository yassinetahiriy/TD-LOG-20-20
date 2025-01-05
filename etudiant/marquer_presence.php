<?php
require_once '../config/session_config.php';
require_once '../config/database.php';

checkUserSession('etudiant');

$seance_id = isset($_GET['seance_id']) ? $_GET['seance_id'] : null;

if (!$seance_id) {
    die("ID de séance manquant");
}

try {
    // Récupérer les informations de la séance et le statut de validation
    $stmt = $conn->prepare("
        SELECT s.*, sl.type_salle, sl.nom_salle, g.nom_groupe, s.validation_active
        FROM seances s
        JOIN salles sl ON s.id_salle = sl.id
        JOIN groupes_td g ON s.id_groupe_td = g.id
        JOIN groupes_td_etudiants gte ON g.id = gte.id_groupe_td
        WHERE s.id = ? AND s.statut = 'en_cours' AND gte.id_etudiant = ?
    ");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);
    $seance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seance) {
        die("Séance non trouvée ou non autorisée");
    }

    // Récupérer les places occupées
    $stmt = $conn->prepare("
        SELECT p.id, p.numero_place, p.id_etudiant, u.nom, u.prenom, u.photo_url
        FROM presences p
        JOIN users u ON p.id_etudiant = u.id
        WHERE p.id_seance = ?
    ");
    $stmt->execute([$seance_id]);
    $places_occupees = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $places_occupees[$row['numero_place']] = $row;
    }

    // Vérifier si l'étudiant a déjà marqué sa présence
    $stmt = $conn->prepare("
        SELECT id, numero_place 
        FROM presences 
        WHERE id_seance = ? AND id_etudiant = ?
    ");
    $stmt->execute([$seance_id, $_SESSION['user_id']]);
    $presence_existante = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les étudiants à valider seulement si la validation est active
    $etudiants_a_valider = [];
    if ($presence_existante && $seance['validation_active']) {
        $stmt = $conn->prepare("
            SELECT DISTINCT u.id, u.nom, u.prenom, u.photo_url, p.id as presence_id
            FROM presences p
            JOIN users u ON p.id_etudiant = u.id
            LEFT JOIN validations_presence v ON p.id = v.id_presence 
                AND v.id_validateur = :validateur_id
            WHERE p.id_seance = :seance_id 
            AND p.id_etudiant != :etudiant_id
            AND p.status = 'present'
            AND v.id IS NULL
            ORDER BY RAND()
            LIMIT 4
        ");
        
        $stmt->execute([
            ':validateur_id' => $_SESSION['user_id'],
            ':seance_id' => $seance_id,
            ':etudiant_id' => $_SESSION['user_id']
        ]);
        $etudiants_a_valider = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marquer sa présence</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f4f4f4;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .salle {
        margin-top: 20px;
    }
    .rangee {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 20px;
    }
    .table {
        border: 2px solid #333;
        border-radius: 4px;
        padding: 5px;
        background: white;
    }
    .table-binome {
        width: 100px;
        height: 40px;
        display: flex;
        justify-content: space-between;
    }
    .table-groupe {
        width: 200px;
        height: 40px;
        display: flex;
        justify-content: space-between;
    }
    .place {
        width: 35px;
        height: 35px;
        background: #e3f2fd;
        border: 1px solid #90caf9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        cursor: pointer;
    }
    .place.occupied {
        background: #f5f5f5;
        cursor: not-allowed;
    }
    .place img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    .place:hover:not(.occupied) {
        background: #bbdefb;
    }
    .validation-section {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .validation-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .etudiant-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: white;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .etudiant-card img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .btn-valider {
        margin-left: auto;
        padding: 8px 16px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="info-section">
            <h2>Marquer sa présence</h2>
            <p>
                <strong>Groupe:</strong> <?php echo htmlspecialchars($seance['nom_groupe']); ?><br>
                <strong>Salle:</strong> <?php echo htmlspecialchars($seance['nom_salle']); ?><br>
                <strong>Type:</strong> <?php echo $seance['type_salle'] === 'binome' ? 'Tables binômes' : 'Tables groupées'; ?>
            </p>
        </div>

        <div class="salle">
            <?php if ($seance['type_salle'] === 'binome'): ?>
                <?php for($rangee = 0; $rangee < 5; $rangee++): ?>
                    <div class="rangee">
                        <?php for($table = 0; $table < 3; $table++): 
                            $place_base = ($rangee * 6) + ($table * 2) + 1;
                        ?>
                            <div class="table table-binome">
                                <?php for($i = 0; $i < 2; $i++): 
                                    $place_num = $place_base + $i;
                                ?>
                                    <div class="place <?php echo isset($places_occupees[$place_num]) ? 'occupied' : ''; ?>"
                                         onclick="choisirPlace(<?php echo $place_num; ?>)">
                                        <?php if (isset($places_occupees[$place_num])): ?>
                                            <img src="../<?php echo htmlspecialchars($places_occupees[$place_num]['photo_url']); ?>"
                                                 alt="<?php echo htmlspecialchars($places_occupees[$place_num]['prenom']); ?>">
                                        <?php else: ?>
                                            <?php echo $place_num; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
            <?php else: ?>
                <?php for($rangee = 0; $rangee < 4; $rangee++): ?>
                    <div class="rangee">
                        <?php for($table = 0; $table < 2; $table++): 
                            $place_base = ($rangee * 8) + ($table * 4) + 1;
                        ?>
                            <div class="table table-groupe">
                                <?php for($i = 0; $i < 4; $i++): 
                                    $place_num = $place_base + $i;
                                ?>
                                    <div class="place <?php echo isset($places_occupees[$place_num]) ? 'occupied' : ''; ?>"
                                         onclick="choisirPlace(<?php echo $place_num; ?>)">
                                        <?php if (isset($places_occupees[$place_num])): ?>
                                            <img src="../<?php echo htmlspecialchars($places_occupees[$place_num]['photo_url']); ?>"
                                                 alt="<?php echo htmlspecialchars($places_occupees[$place_num]['prenom']); ?>">
                                        <?php else: ?>
                                            <?php echo $place_num; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>

        <?php if ($presence_existante && !empty($etudiants_a_valider) && $seance['validation_active']): ?>
            <div class="validation-section">
                <h3>Validation des présences (4 étudiants requis)</h3>
                <div class="validation-list">
                    <?php foreach($etudiants_a_valider as $etudiant): ?>
                        <div class="etudiant-card" id="etudiant-<?php echo $etudiant['presence_id']; ?>">
                            <img src="../<?php echo htmlspecialchars($etudiant['photo_url']); ?>" alt="Photo">
                            <div class="etudiant-info">
                                <?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?>
                            </div>
                            <button onclick="validerPresence(<?php echo $etudiant['presence_id']; ?>)" class="btn-valider">
                                Valider
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($presence_existante && !$seance['validation_active']): ?>
            <div class="message-info">
                La validation des présences n'a pas encore été activée par le professeur.
            </div>
        <?php endif; ?>
    </div>

    <script>
    let validationsCount = 0;

    function choisirPlace(placeNum) {
        if (event.target.classList.contains('occupied')) {
            return;
        }

        if (confirm('Voulez-vous choisir la place ' + placeNum + ' ?')) {
            fetch('ajax/marquer_presence.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    seance_id: <?php echo $seance_id; ?>,
                    place_id: placeNum
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue');
            });
        }
    }

    function validerPresence(presenceId) {
        fetch('ajax/valider_presence.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                presence_id: presenceId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`etudiant-${presenceId}`).remove();
                validationsCount++;
                const remainingCards = document.querySelectorAll('.etudiant-card');
                if (remainingCards.length === 0) {
                    alert('Validations terminées ! Vous pouvez revenir plus tard pour valider d\'autres présences.');
                    location.reload(); // Recharge la page au lieu de rediriger
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue');
        });
    }
    </script>
</body>
</html>