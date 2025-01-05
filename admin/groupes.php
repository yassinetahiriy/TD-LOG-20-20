<?php
session_start();
require_once '../config/database.php';



try {
    // Récupérer tous les groupes TD
    $stmt = $conn->prepare("SELECT * FROM groupes_td ORDER BY nom_groupe");
    $stmt->execute();
    $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les étudiants
    $stmt = $conn->prepare("
        SELECT id, nom, prenom, email 
        FROM users 
        WHERE user_type = 'etudiant'
        ORDER BY nom, prenom
    ");
    $stmt->execute();
    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque groupe, récupérer ses étudiants
    foreach ($groupes as &$groupe) {
        $stmt = $conn->prepare("
            SELECT u.id, u.nom, u.prenom, u.email
            FROM users u
            JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
            WHERE gte.id_groupe_td = ?
            ORDER BY u.nom, u.prenom
        ");
        $stmt->execute([$groupe['id']]);
        $groupe['etudiants'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Groupes TD</title>
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
        .groupe-card {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .etudiant-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .etudiant-item {
            background: white;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
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
            margin: 2px;
        }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .form-group {
            margin-bottom: 15px;
        }
        select[multiple] {
            height: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestion des Groupes TD</h1>
            <div>
                <a href="dashboard.php" class="btn">Retour</a>
                <button onclick="showAddGroupeModal()" class="btn btn-success">Nouveau Groupe</button>
            </div>
        </div>

        <?php foreach ($groupes as $groupe): ?>
        <div class="groupe-card">
            <div class="header">
                <h3><?php echo htmlspecialchars($groupe['nom_groupe']); ?> 
                    (<?php echo htmlspecialchars($groupe['annee_scolaire']); ?>)
                </h3>
                <div>
                    <button onclick="editGroupe(<?php echo $groupe['id']; ?>)" class="btn">Modifier</button>
                    <button onclick="deleteGroupe(<?php echo $groupe['id']; ?>)" class="btn btn-danger">Supprimer</button>
                </div>
            </div>

            <div class="etudiant-list">
                <?php foreach ($groupe['etudiants'] as $etudiant): ?>
                    <div class="etudiant-item">
                        <?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?>
                        <button onclick="removeEtudiant(<?php echo $groupe['id']; ?>, <?php echo $etudiant['id']; ?>)" 
                                class="btn btn-danger btn-sm">
                            X
                        </button>
                    </div>
                <?php endforeach; ?>
                <button onclick="addEtudiantToGroupe(<?php echo $groupe['id']; ?>)" class="btn">
                    + Ajouter des étudiants
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal pour ajouter/modifier un groupe -->
    <div id="groupeModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Groupe TD</h2>
            <form id="groupeForm">
                <div class="form-group">
                    <label for="nom_groupe">Nom du groupe</label>
                    <input type="text" id="nom_groupe" name="nom_groupe" required>
                </div>
                <div class="form-group">
                    <label for="annee_scolaire">Année scolaire</label>
                    <input type="text" id="annee_scolaire" name="annee_scolaire" required>
                </div>
                <button type="submit" class="btn btn-success">Sauvegarder</button>
                <button type="button" onclick="closeModal()" class="btn">Annuler</button>
            </form>
        </div>
    </div>

    <!-- Modal pour ajouter des étudiants -->
    <div id="etudiantModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Ajouter des étudiants</h2>
            <form id="etudiantForm">
                <div class="form-group">
                    <label for="etudiants">Sélectionner les étudiants</label>
                    <select multiple id="etudiants" name="etudiants[]">
                        <?php foreach ($etudiants as $etudiant): ?>
                            <option value="<?php echo $etudiant['id']; ?>">
                                <?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Ajouter</button>
                <button type="button" onclick="closeModal()" class="btn">Annuler</button>
            </form>
        </div>
    </div>

    <script>
    let currentGroupeId = null;

    function showAddGroupeModal() {
        document.getElementById('groupeModal').style.display = 'block';
        document.getElementById('groupeForm').reset();
        currentGroupeId = null;
    }

    function editGroupe(groupeId) {
        currentGroupeId = groupeId;
        // Charger les données du groupe et afficher le modal
        fetch(`ajax/get_groupe.php?id=${groupeId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('nom_groupe').value = data.nom_groupe;
                document.getElementById('annee_scolaire').value = data.annee_scolaire;
                document.getElementById('groupeModal').style.display = 'block';
            });
    }

    function addEtudiantToGroupe(groupeId) {
        currentGroupeId = groupeId;
        document.getElementById('etudiantModal').style.display = 'block';
    }

    document.getElementById('groupeForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        if (currentGroupeId) formData.append('id', currentGroupeId);

        fetch('ajax/save_groupe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        });
    };

    document.getElementById('etudiantForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('groupe_id', currentGroupeId);

        fetch('ajax/add_etudiants_groupe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        });
    };

    function removeEtudiant(groupeId, etudiantId) {
        if (confirm('Voulez-vous vraiment retirer cet étudiant du groupe ?')) {
            fetch('ajax/remove_etudiant_groupe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    groupe_id: groupeId,
                    etudiant_id: etudiantId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                }
            });
        }
    }

    function closeModal() {
        document.getElementById('groupeModal').style.display = 'none';
        document.getElementById('etudiantModal').style.display = 'none';
    }
    </script>
</body>
</html>
