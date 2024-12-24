<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

try {
    // Récupérer tous les utilisateurs sauf l'admin connecté
    $stmt = $conn->prepare("
        SELECT u.*, 
               GROUP_CONCAT(g.nom_groupe) as groupes_td
        FROM users u
        LEFT JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
        LEFT JOIN groupes_td g ON gte.id_groupe_td = g.id
        WHERE u.id != ?
        GROUP BY u.id
        ORDER BY u.user_type, u.nom, u.prenom
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les groupes TD
    $stmt = $conn->prepare("SELECT id, nom_groupe FROM groupes_td ORDER BY nom_groupe");
    $stmt->execute();
    $groupes_td = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <style>
        /* ... Styles précédents ... */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            width: 70%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
        }
        .close {
            position: absolute;
            right: 10px;
            top: 5px;
            font-size: 20px;
            cursor: pointer;
        }
        .groupe-badge {
            display: inline-block;
            background: #e3f2fd;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin: 2px;
        }
        select[multiple] {
            width: 100%;
            height: 200px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ... Header et autres éléments ... -->

        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Groupes TD</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr data-user-type="<?php echo htmlspecialchars($user['user_type']); ?>">
                        <td>
                            <img src="<?php echo htmlspecialchars($user['photo_url']); ?>" 
                                 alt="Photo de profil" 
                                 class="user-img">
                        </td>
                        <td><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php
                            switch($user['user_type']) {
                                case 'admin':
                                    echo 'Administrateur';
                                    break;
                                case 'professeur':
                                    echo 'Professeur';
                                    break;
                                case 'etudiant':
                                    echo 'Étudiant';
                                    break;
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($user['user_type'] === 'etudiant'): ?>
                                <?php if ($user['groupes_td']): ?>
                                    <?php foreach(explode(',', $user['groupes_td']) as $groupe): ?>
                                        <span class="groupe-badge"><?php echo htmlspecialchars($groupe); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <button onclick="assignerGroupe(<?php echo $user['id']; ?>)" class="btn">
                                    Assigner Groupe
                                </button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="editUser(<?php echo $user['id']; ?>)" class="btn">Modifier</button>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-danger">Supprimer</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour assigner des groupes -->
    <div id="groupeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeGroupeModal()">&times;</span>
            <h2>Assigner aux Groupes TD</h2>
            <form id="assignGroupeForm">
                <input type="hidden" id="etudiant_id" name="etudiant_id">
                <div class="form-group">
                    <label>Sélectionner les groupes</label>
                    <select multiple name="groupes[]" id="groupes">
                        <?php foreach ($groupes_td as $groupe): ?>
                            <option value="<?php echo $groupe['id']; ?>">
                                <?php echo htmlspecialchars($groupe['nom_groupe']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Enregistrer</button>
            </form>
        </div>
    </div>

    <script>
    function assignerGroupe(userId) {
        document.getElementById('etudiant_id').value = userId;
        document.getElementById('groupeModal').style.display = 'block';

        // Charger les groupes actuels de l'étudiant
        fetch('ajax/get_groupes_etudiant.php?id=' + userId)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('groupes');
                const options = select.options;
                for (let i = 0; i < options.length; i++) {
                    options[i].selected = data.groupes.includes(parseInt(options[i].value));
                }
            });
    }

    function closeGroupeModal() {
        document.getElementById('groupeModal').style.display = 'none';
    }

    document.getElementById('assignGroupeForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('ajax/assign_groupes.php', {
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
    function filterUsers() {
        const type = document.getElementById('typeFilter').value;
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            if (!type || row.dataset.userType === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function deleteUser(userId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
            fetch('ajax/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: userId })
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

    function editUser(userId) {
        window.location.href = 'edit_user.php?id=' + userId;
    }
    </script>
</body>
</html>