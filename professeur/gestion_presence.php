<?php
require_once '../config/session_config.php';
require_once '../config/database.php';

checkUserSession('professeur');

$seance_id = $_GET['seance_id'] ?? null;
if (!$seance_id) {
   header('Location: dashboard.php');
   exit();
}

try {
   // Récupérer les détails de la séance
   $stmt = $conn->prepare("
       SELECT s.*, sl.type_salle, sl.nom_salle, g.nom_groupe, s.validation_active 
       FROM seances s
       JOIN salles sl ON s.id_salle = sl.id
       JOIN groupes_td g ON s.id_groupe_td = g.id
       WHERE s.id = ? AND s.id_professeur = ?
   ");
   $stmt->execute([$seance_id, $_SESSION['user_id']]);
   $seance = $stmt->fetch(PDO::FETCH_ASSOC);

   if (!$seance) {
       header('Location: dashboard.php');
       exit();
   }

   // Récupérer les présences et leurs validations
   $stmt = $conn->prepare("
       SELECT p.*, u.nom, u.prenom, u.photo_url,
              COUNT(DISTINCT v.id) as nb_validations,
              GROUP_CONCAT(DISTINCT CONCAT(val.nom, ' ', val.prenom)) as validateurs
       FROM presences p
       JOIN users u ON p.id_etudiant = u.id
       LEFT JOIN validations_presence v ON p.id = v.id_presence
       LEFT JOIN users val ON v.id_validateur = val.id
       WHERE p.id_seance = ?
       GROUP BY p.id
   ");
   $stmt->execute([$seance_id]);
   $presences = [];
   while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
       $presences[$row['numero_place']] = $row;
   }

   // Récupérer les étudiants du groupe
   $stmt = $conn->prepare("
       SELECT u.id, u.nom, u.prenom, u.photo_url
       FROM users u
       JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
       WHERE gte.id_groupe_td = ? AND u.user_type = 'etudiant'
       ORDER BY u.nom, u.prenom
   ");
   $stmt->execute([$seance['id_groupe_td']]);
   $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
   die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Présences</title>
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
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
            width: 120px;
            height: 50px;
            display: flex;
            justify-content: space-between;
        }
        .table-groupe {
            width: 240px;
            height: 50px;
            display: flex;
            justify-content: space-between;
        }
        .place {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            background: #f8f9fa;
        }
        .place img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .place.occupee {
            background: #e3f2fd;
            border: 2px solid #2196f3;
        }
        .details-container {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .etudiant-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .etudiant-info img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .validations {
            margin-top: 10px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
        .btn-start { background: #28a745; }
        .btn-end { background: #dc3545; }
    </style>
</head>
<body>
   <div class="container">
       <div class="header">
           <h1>Gestion des Présences</h1>
           <div>
               <a href="dashboard.php" class="btn">Retour</a>
               <?php if ($seance['statut'] === 'en_cours'): ?>
                   <?php if (!$seance['validation_active']): ?>
                       <button onclick="demarrerValidation()" class="btn btn-success">
                           <i class="fas fa-check-circle"></i> Démarrer validation
                       </button>
                   <?php else: ?>
                       <span class="badge badge-success">Validation en cours</span>
                   <?php endif; ?>
                   <button onclick="terminerSeance()" class="btn btn-end">Terminer la séance</button>
               <?php endif; ?>
           </div>
       </div>

        <div class="seance-info">
            <h2><?php echo htmlspecialchars($seance['nom_groupe']); ?></h2>
            <p>
                <strong>Date:</strong> <?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?><br>
                <strong>Horaire:</strong> <?php echo substr($seance['heure_debut'], 0, 5); ?> - 
                                        <?php echo substr($seance['heure_fin'], 0, 5); ?><br>
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
                                    <div class="place <?php echo isset($presences[$place_num]) ? 'occupee' : ''; ?>"
                                         onclick="showDetails(<?php echo $place_num; ?>)">
                                        <?php if (isset($presences[$place_num])): ?>
                                            <img src="../<?php echo htmlspecialchars($presences[$place_num]['photo_url']); ?>"
                                                 alt="Photo <?php echo htmlspecialchars($presences[$place_num]['prenom']); ?>">
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
                                    <div class="place <?php echo isset($presences[$place_num]) ? 'occupee' : ''; ?>"
                                         onclick="showDetails(<?php echo $place_num; ?>)">
                                        <?php if (isset($presences[$place_num])): ?>
                                            <img src="../<?php echo htmlspecialchars($presences[$place_num]['photo_url']); ?>"
                                                 alt="Photo <?php echo htmlspecialchars($presences[$place_num]['prenom']); ?>">
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

        <div class="details-container" id="detailsContainer" style="display: none;">
            <h3>Détails de l'étudiant</h3>
            <div id="etudiantDetails"></div>
        </div>
    </div>

    <script>
    function demarrerValidation() {
        if(confirm('Voulez-vous démarrer la phase de validation des présences ?')) {
            fetch('ajax/demarrer_validation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    seance_id: <?php echo $seance_id; ?>
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
    function showDetails(placeNum) {
        const place = document.querySelector(`.place[onclick="showDetails(${placeNum})"]`);
        if (!place.classList.contains('occupee')) return;

        fetch('ajax/get_etudiant_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                seance_id: <?php echo $seance_id; ?>,
                numero_place: placeNum
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('etudiantDetails').innerHTML = data.html;
                document.getElementById('detailsContainer').style.display = 'block';
            }
        });
    }

    function terminerSeance() {
        if (confirm('Voulez-vous terminer la séance ? Les étudiants non présents seront marqués absents.')) {
            fetch('ajax/terminer_seance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    seance_id: <?php echo $seance_id; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    alert(data.message);
                }
            });
        }
    }

    function marquerAbsent(etudiantId) {
        if (confirm('Voulez-vous marquer cet étudiant comme absent ?')) {
            fetch('ajax/marquer_absent.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    seance_id: <?php echo $seance_id; ?>,
                    etudiant_id: etudiantId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    }
    </script>
</body>
</html>
