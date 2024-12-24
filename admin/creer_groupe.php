<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Récupérer la liste des étudiants non assignés
try {
    $stmt = $conn->prepare("
        SELECT u.* 
        FROM users u
        LEFT JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
        WHERE u.user_type = 'etudiant'
        AND gte.id IS NULL
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute();
    $etudiants_non_assignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les étudiants
    $stmt = $conn->prepare("
        SELECT id, nom, prenom
        FROM users
        WHERE user_type = 'etudiant'
        ORDER BY nom, prenom
    ");
    $stmt->execute();
    $tous_etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Groupe TD</title>
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
            margin-bottom: 20px;
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
        .btn-success {
            background: #28a745;
        }
        .btn-danger {
            background: #dc3545;
        }
        .student-selection {
            display: grid;
            grid-template-columns: 1fr 100px 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .student-list {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            height: 400px;
            overflow-y: auto;
        }
        .student-item {
            padding: 8px;
            margin: 4px 0;
            background: #f8f9fa;
            border-radius: 4px;
            cursor: pointer;
        }
        .student-item:hover {
            background: #e9ecef;
        }
        .student-item.selected {
            background: #cce5ff;
            border: 1px solid #b8daff;
        }
        .transfer-buttons {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
        }
        .success-message {
            color: green;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .error-message {
            color: red;
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .buttons-container {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Créer un Groupe TD</h1>
            <div>
                <a href="groupes.php" class="btn">Voir les groupes</a>
                <a href="dashboard.php" class="btn">Retour</a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form id="createGroupForm">
            <div class="form-group">
                <label for="nom_groupe">Nom du groupe</label>
                <input type="text" id="nom_groupe" name="nom_groupe" required>
            </div>

            <div class="form-group">
                <label for="annee_scolaire">Année scolaire</label>
                <input type="text" id="annee_scolaire" name="annee_scolaire" 
                       placeholder="2023-2024" required>
            </div>

            <div class="student-selection">
                <div>
                    <h3>Étudiants disponibles</h3>
                    <div id="availableStudents" class="student-list">
                        <!-- Liste des étudiants disponibles -->
                    </div>
                </div>

                <div class="transfer-buttons">
                    <button type="button" class="btn" onclick="addSelectedStudents()">&gt;&gt;</button>
                    <button type="button" class="btn" onclick="removeSelectedStudents()">&lt;&lt;</button>
                </div>

                <div>
                    <h3>Étudiants sélectionnés</h3>
                    <div id="selectedStudents" class="student-list">
                        <!-- Liste des étudiants sélectionnés -->
                    </div>
                </div>
            </div>

            <div class="buttons-container">
                <button type="submit" class="btn btn-success">Créer le groupe</button>
            </div>
        </form>

        <div id="groupeSuccess" style="display: none; margin-top: 20px;">
            <div class="alert alert-success">
                Groupe créé avec succès !
            </div>
            <div class="buttons-container">
                <button onclick="document.getElementById('createGroupForm').reset(); location.reload();" class="btn">
                    Créer un autre groupe
                </button>
                <a href="groupes.php" class="btn btn-success">Voir tous les groupes</a>
            </div>
        </div>
    </div>

    <script>
    let availableStudents = <?php echo json_encode($etudiants_non_assignes); ?>;
    let selectedStudents = [];

    function renderStudentLists() {
        const availableList = document.getElementById('availableStudents');
        const selectedList = document.getElementById('selectedStudents');

        availableList.innerHTML = availableStudents.map(student => `
            <div class="student-item" onclick="toggleStudentSelection(this, ${student.id})">
                ${student.nom} ${student.prenom}
            </div>
        `).join('');

        selectedList.innerHTML = selectedStudents.map(student => `
            <div class="student-item" onclick="toggleStudentSelection(this, ${student.id})">
                ${student.nom} ${student.prenom}
            </div>
        `).join('');
    }

    function toggleStudentSelection(element, studentId) {
        element.classList.toggle('selected');
    }

    function addSelectedStudents() {
        const availableElements = document.querySelectorAll('#availableStudents .student-item.selected');
        availableElements.forEach(element => {
            const studentId = parseInt(element.getAttribute('onclick').match(/\d+/)[0]);
            const studentIndex = availableStudents.findIndex(s => s.id === studentId);
            if (studentIndex !== -1) {
                selectedStudents.push(availableStudents[studentIndex]);
                availableStudents.splice(studentIndex, 1);
            }
        });
        renderStudentLists();
    }

    function removeSelectedStudents() {
        const selectedElements = document.querySelectorAll('#selectedStudents .student-item.selected');
        selectedElements.forEach(element => {
            const studentId = parseInt(element.getAttribute('onclick').match(/\d+/)[0]);
            const studentIndex = selectedStudents.findIndex(s => s.id === studentId);
            if (studentIndex !== -1) {
                availableStudents.push(selectedStudents[studentIndex]);
                selectedStudents.splice(studentIndex, 1);
            }
        });
        renderStudentLists();
    }

    document.getElementById('createGroupForm').onsubmit = function(e) {
        e.preventDefault();

        if (selectedStudents.length === 0) {
            alert('Veuillez sélectionner au moins un étudiant');
            return;
        }

        const formData = new FormData();
        formData.append('nom_groupe', document.getElementById('nom_groupe').value);
        formData.append('annee_scolaire', document.getElementById('annee_scolaire').value);
        formData.append('etudiants', JSON.stringify(selectedStudents.map(s => s.id)));

        fetch('ajax/create_groupe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('createGroupForm').style.display = 'none';
                document.getElementById('groupeSuccess').style.display = 'block';
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            alert('Erreur : ' + error);
        });
    };

    renderStudentLists();
    </script>
</body>
</html>