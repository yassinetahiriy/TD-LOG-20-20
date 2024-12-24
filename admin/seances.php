<?php
session_start();
require_once '../config/database.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Récupération des données nécessaires pour le formulaire
try {
    // Récupérer les professeurs
    $stmt = $conn->prepare("SELECT id, nom, prenom FROM users WHERE user_type = 'professeur'");
    $stmt->execute();
    $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les salles
    $stmt = $conn->prepare("SELECT id, nom_salle, type_salle, capacite FROM salles");
    $stmt->execute();
    $salles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les groupes TD
    $stmt = $conn->prepare("SELECT id, nom_groupe, annee_scolaire FROM groupes_td");
    $stmt->execute();
    $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les séances existantes avec leurs détails
    $stmt = $conn->prepare("
        SELECT s.*, 
               p.nom as prof_nom, p.prenom as prof_prenom,
               sl.nom_salle, g.nom_groupe
        FROM seances s
        JOIN users p ON s.id_professeur = p.id
        JOIN salles sl ON s.id_salle = sl.id
        JOIN groupes_td g ON s.id_groupe_td = g.id
        ORDER BY s.date_seance DESC, s.heure_debut DESC
    ");
    $stmt->execute();
    $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Séances</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-success {
            background: #28a745;
        }
        .seance-card {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestion des Séances</h1>
            <a href="dashboard.php" class="btn">Retour au tableau de bord</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form id="seanceForm" action="ajax/add_seance.php" method="POST">
            <div class="form-group">
                <label for="date_seance">Date de la séance:</label>
                <input type="date" id="date_seance" name="date_seance" required>
            </div>

            <div class="form-group">
                <label for="heure_debut">Heure de début:</label>
                <input type="time" id="heure_debut" name="heure_debut" required>
            </div>

            <div class="form-group">
                <label for="heure_fin">Heure de fin:</label>
                <input type="time" id="heure_fin" name="heure_fin" required>
            </div>

            <div class="form-group">
                <label for="id_professeur">Professeur:</label>
                <select id="id_professeur" name="id_professeur" required>
                    <option value="">Sélectionner un professeur</option>
                    <?php foreach ($professeurs as $prof): ?>
                        <option value="<?php echo $prof['id']; ?>">
                            <?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['prenom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_salle">Salle:</label>
                <select id="id_salle" name="id_salle" required>
                    <option value="">Sélectionner une salle</option>
                    <?php foreach ($salles as $salle): ?>
                        <option value="<?php echo $salle['id']; ?>">
                            <?php echo htmlspecialchars($salle['nom_salle'] . ' (' . $salle['type_salle'] . ' - ' . $salle['capacite'] . ' places)'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_groupe_td">Groupe TD:</label>
                <select id="id_groupe_td" name="id_groupe_td" required>
                    <option value="">Sélectionner un groupe</option>
                    <?php foreach ($groupes as $groupe): ?>
                        <option value="<?php echo $groupe['id']; ?>">
                            <?php echo htmlspecialchars($groupe['nom_groupe'] . ' (' . $groupe['annee_scolaire'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Ajouter la séance</button>
        </form>

        <h2>Séances programmées</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Horaires</th>
                    <th>Professeur</th>
                    <th>Salle</th>
                    <th>Groupe</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($seances as $seance): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?></td>
                        <td><?php echo substr($seance['heure_debut'], 0, 5) . ' - ' . substr($seance['heure_fin'], 0, 5); ?></td>
                        <td><?php echo htmlspecialchars($seance['prof_nom'] . ' ' . $seance['prof_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($seance['nom_salle']); ?></td>
                        <td><?php echo htmlspecialchars($seance['nom_groupe']); ?></td>
                        <td><?php echo htmlspecialchars($seance['statut']); ?></td>
                        <td>
                            <?php if ($seance['statut'] === 'programmee'): ?>
                                <button onclick="modifierSeance(<?php echo $seance['id']; ?>)" class="btn">Modifier</button>
                                <button onclick="supprimerSeance(<?php echo $seance['id']; ?>)" class="btn btn-danger">Supprimer</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function supprimerSeance(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette séance ?')) {
            fetch('ajax/delete_seance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression : ' + data.message);
                }
            })
            .catch(error => {
                alert('Erreur : ' + error);
            });
        }
    }

    function modifierSeance(id) {
        // Rediriger vers la page de modification
        window.location.href = 'modifier_seance.php?id=' + id;
    }

    // Validation du formulaire
    document.getElementById('seanceForm').onsubmit = function(e) {
        e.preventDefault();
        
        const heureDebut = document.getElementById('heure_debut').value;
        const heureFin = document.getElementById('heure_fin').value;
        
        if (heureDebut >= heureFin) {
            alert("L'heure de fin doit être postérieure à l'heure de début");
            return false;
        }

        // Envoyer le formulaire
        fetch('ajax/add_seance.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            alert('Erreur : ' + error);
        });
    };
    </script>
</body>
</html>