<?php
session_start();
require_once '../config/database.php';

// Vérification de l'authentification
checkUserSession('professeur');

header('Content-Type: application/json');

// Vérifier si une séance est spécifiée
if (!isset($_GET['seance_id'])) {
    header('Location: dashboard.php');
    exit();
}

$seance_id = $_GET['seance_id'];

// Récupérer les informations de la séance
$stmt = $conn->prepare("
    SELECT s.*, sl.nom_salle, sl.type_salle, g.nom_groupe 
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

// Récupérer la liste des étudiants du groupe
$stmt = $conn->prepare("
    SELECT u.id, u.nom, u.prenom, u.photo_url,
           p.id as presence_id, p.status as presence_status,
           p.id_place, p.heure_marquage
    FROM users u
    LEFT JOIN presences p ON u.id = p.id_etudiant AND p.id_seance = ?
    WHERE u.user_type = 'etudiant'
    ORDER BY u.nom, u.prenom
");
$stmt->execute([$seance_id]);
$etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Présences - <?php echo htmlspecialchars($seance['nom_groupe']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
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

        .seance-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .classroom-layout {
            display: grid;
            gap: 10px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }

        .seat {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .seat.occupied {
            background: #e3f2fd;
            border-color: #2196f3;
        }

        .seat img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-bottom: 5px;
        }

        .student-list {
            margin-top: 20px;
        }

        .student-card {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .student-card img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
        }

        .btn-present { background-color: #28a745; }
        .btn-absent { background-color: #dc3545; }
        .btn-start { background-color: #007bff; }
        .btn-end { background-color: #6c757d; }

        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            margin-left: 10px;
        }

        .status-present { background: #d4edda; color: #155724; }
        .status-absent { background: #f8d7da; color: #721c24; }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            width: 80%;
            max-width: 500px;
            margin: 100px auto;
            padding: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestion des Présences</h1>
            <button onclick="location.href='dashboard.php'" class="btn">Retour</button>
        </div>

        <div class="seance-info">
            <h2><?php echo htmlspecialchars($seance['nom_groupe']); ?></h2>
            <p>
                <strong>Date:</strong> <?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?><br>
                <strong>Horaire:</strong> <?php echo substr($seance['heure_debut'], 0, 5); ?> - <?php echo substr($seance['heure_fin'], 0, 5); ?><br>
                <strong>Salle:</strong> <?php echo htmlspecialchars($seance['nom_salle']); ?>
            </p>
            <?php if ($seance['statut'] === 'programmee'): ?>
                <button onclick="startSession()" class="btn btn-start">Démarrer la séance</button>
            <?php elseif ($seance['statut'] === 'en_cours'): ?>
                <button onclick="endSession()" class="btn btn-end">Terminer la séance</button>
            <?php endif; ?>
        </div>

        <!-- Disposition de la salle -->
        <h3>Plan de la salle</h3>
        <div class="classroom-layout" style="grid-template-columns: repeat(<?php echo $seance['type_salle'] === 'individuel' ? '5' : '3'; ?>, 1fr);">
            <?php
            $places = array_column($etudiants, null, 'id_place');
            for ($i = 1; $i <= 30; $i++):
            ?>
                <div class="seat <?php echo isset($places[$i]) ? 'occupied' : ''; ?>" data-place="<?php echo $i; ?>">
                    <?php if (isset($places[$i])): ?>
                        <img src="../<?php echo htmlspecialchars($places[$i]['photo_url']); ?>" 
                             alt="<?php echo htmlspecialchars($places[$i]['nom'] . ' ' . $places[$i]['prenom']); ?>">
                        <div><?php echo htmlspecialchars($places[$i]['prenom']); ?></div>
                    <?php else: ?>
                        Place <?php echo $i; ?>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Liste des étudiants -->
        <div class="student-list">
            <h3>Liste des étudiants</h3>
            <?php foreach ($etudiants as $etudiant): ?>
                <div class="student-card">
                    <img src="../<?php echo htmlspecialchars($etudiant['photo_url']); ?>" 
                         alt="<?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?>">
                    <div class="student-info">
                        <strong><?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?></strong>
                        <?php if ($etudiant['presence_id']): ?>
                            <span class="status status-present">Présent - Place <?php echo $etudiant['id_place']; ?></span>
                        <?php else: ?>
                            <span class="status status-absent">Absent</span>
                            <button onclick="marquerAbsent(<?php echo $etudiant['id']; ?>)" class="btn btn-absent">Marquer Absent</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal pour la confirmation -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h3>Confirmation</h3>
            <p id="modalMessage"></p>
            <button onclick="confirmAction()" class="btn btn-present">Confirmer</button>
            <button onclick="closeModal()" class="btn">Annuler</button>
        </div>
    </div>

    <script>
        let currentAction = null;
        let currentStudentId = null;

        function startSession() {
            if (confirm('Voulez-vous démarrer la séance ?')) {
                fetch('ajax/start_session.php', {
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
                        alert('Erreur lors du démarrage de la séance');
                    }
                });
            }
        }

        function endSession() {
            if (confirm('Voulez-vous terminer la séance ? Cela marquera automatiquement absents tous les étudiants non présents.')) {
                fetch('ajax/end_session.php', {
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
                        location.href = 'dashboard.php';
                    } else {
                        alert('Erreur lors de la fermeture de la séance');
                    }
                });
            }
        }

        function marquerAbsent(studentId) {
            currentStudentId = studentId;
            currentAction = 'absent';
            document.getElementById('modalMessage').textContent = 'Voulez-vous marquer cet étudiant comme absent ?';
            document.getElementById('confirmModal').style.display = 'block';
        }

        function confirmAction() {
            if (currentAction === 'absent') {
                fetch('ajax/mark_absent.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        seance_id: <?php echo $seance_id; ?>,
                        student_id: currentStudentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur lors du marquage de l'absence');
                    }
                });
            }
            closeModal();
        }

        function closeModal() {
            document.getElementById('confirmModal').style.display = 'none';
            currentAction = null;
            currentStudentId = null;
        }
    </script>
</body>
</html>