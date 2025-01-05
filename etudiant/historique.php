<?php
session_start();
require_once '../config/database.php';

checkUserSession('etudiant');

header('Content-Type: application/json');


try {
    // Récupérer l'historique des présences
    $stmt = $conn->prepare("
        SELECT p.*, s.date_seance, s.heure_debut, s.heure_fin,
               sl.nom_salle, g.nom_groupe,
               u.nom as prof_nom, u.prenom as prof_prenom
        FROM presences p
        JOIN seances s ON p.id_seance = s.id
        JOIN salles sl ON s.id_salle = sl.id
        JOIN groupes_td g ON s.id_groupe_td = g.id
        JOIN users u ON s.id_professeur = u.id
        WHERE p.id_etudiant = ?
        ORDER BY s.date_seance DESC, s.heure_debut DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $presences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer les statistiques
    $total_seances = count($presences);
    $present = 0;
    $absent = 0;

    foreach ($presences as $presence) {
        if ($presence['status'] === 'present') {
            $present++;
        } else {
            $absent++;
        }
    }

    $taux_presence = $total_seances > 0 ? ($present / $total_seances) * 100 : 0;

} catch(PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des présences</title>
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
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .status-present {
            background: #d4edda;
            color: #155724;
        }
        .status-absent {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Historique des présences</h1>
            <a href="dashboard.php" class="btn">Retour</a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total des séances</h3>
                <div class="stat-number"><?php echo $total_seances; ?></div>
            </div>
            <div class="stat-card">
                <h3>Présences</h3>
                <div class="stat-number"><?php echo $present; ?></div>
            </div>
            <div class="stat-card">
                <h3>Absences</h3>
                <div class="stat-number"><?php echo $absent; ?></div>
            </div>
            <div class="stat-card">
                <h3>Taux de présence</h3>
                <div class="stat-number"><?php echo number_format($taux_presence, 1); ?>%</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Horaire</th>
                    <th>Groupe</th>
                    <th>Salle</th>
                    <th>Professeur</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presences as $presence): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($presence['date_seance'])); ?></td>
                        <td><?php echo substr($presence['heure_debut'], 0, 5) . ' - ' . substr($presence['heure_fin'], 0, 5); ?></td>
                        <td><?php echo htmlspecialchars($presence['nom_groupe']); ?></td>
                        <td><?php echo htmlspecialchars($presence['nom_salle']); ?></td>
                        <td><?php echo htmlspecialchars($presence['prof_nom'] . ' ' . $presence['prof_prenom']); ?></td>
                        <td>
                            <span class="status status-<?php echo $presence['status']; ?>">
                                <?php echo $presence['status'] === 'present' ? 'Présent' : 'Absent'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>