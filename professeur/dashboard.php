<?php
require_once '../config/session_config.php';
require_once '../config/database.php';
checkUserSession('professeur');


// Récupérer les séances avec les informations détaillées de la salle
$stmt = $conn->prepare("
    SELECT s.*, sl.nom_salle, sl.type_salle, g.nom_groupe 
    FROM seances s
    JOIN salles sl ON s.id_salle = sl.id
    JOIN groupes_td g ON s.id_groupe_td = g.id
    WHERE s.id_professeur = ? 
    AND s.date_seance >= CURDATE()
    ORDER BY s.date_seance ASC, s.heure_debut ASC
");
$stmt->execute([$_SESSION['user_id']]);
$seances = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Professeur</title>
    <style>
        /* Styles précédents inchangés */
        .salle-info {
            margin-top: 10px;
            padding: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .type-salle {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            margin-left: 10px;
            font-size: 0.9em;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Tableau de bord - Professeur</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></p>
            </div>
            <a href="../logout.php" class="btn btn-danger">Déconnexion</a>
        </div>

        <h2>Vos séances</h2>
        <div class="seance-grid">
            <?php foreach ($seances as $seance): ?>
                <div class="seance-card">
                    <h3><?php echo htmlspecialchars($seance['nom_groupe']); ?></h3>
                    <p>
                        <strong>Date:</strong> <?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?><br>
                        <strong>Horaire:</strong> <?php echo substr($seance['heure_debut'], 0, 5); ?> - 
                                                <?php echo substr($seance['heure_fin'], 0, 5); ?>
                    </p>
                    <div class="salle-info">
                        <strong>Salle:</strong> <?php echo htmlspecialchars($seance['nom_salle']); ?>
                        <span class="type-salle">
                            <?php echo $seance['type_salle'] === 'binome' ? 'Tables binômes' : 'Tables de 4'; ?>
                        </span>
                    </div>
                    <div style="margin-top: 10px;">
                        <span class="status-badge status-<?php echo $seance['statut']; ?>">
                            <?php 
                            switch($seance['statut']) {
                                case 'programmee':
                                    echo 'Programmée';
                                    break;
                                case 'en_cours':
                                    echo 'En cours';
                                    break;
                                case 'terminee':
                                    echo 'Terminée';
                                    break;
                            }
                            ?>
                        </span>
                    </div>
                    
                    <?php if ($seance['statut'] === 'programmee'): ?>
                        <button onclick="startSession(<?php echo $seance['id']; ?>)" class="btn">
                            Démarrer la séance
                        </button>
                    <?php elseif ($seance['statut'] === 'en_cours'): ?>
                        <a href="gestion_presence.php?seance_id=<?php echo $seance['id']; ?>" class="btn">
                            Gérer la présence
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($seances)): ?>
                <p>Aucune séance programmée.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function startSession(seanceId) {
        if (confirm('Voulez-vous démarrer cette séance ?')) {
            fetch('ajax/start_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    seance_id: seanceId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = 'gestion_presence.php?seance_id=' + seanceId;
                } else {
                    alert(data.message || 'Erreur lors du démarrage de la séance');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors du démarrage de la séance');
            });
        }
    }
    </script>
</body>
</html>