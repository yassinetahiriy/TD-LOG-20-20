<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

try {
    // Récupérer toutes les salles
    $stmt = $conn->prepare("SELECT * FROM salles ORDER BY nom_salle");
    $stmt->execute();
    $salles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Classes</title>
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
        }
        .classe-types {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .classe-type {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .salle-preview {
            margin-top: 20px;
            padding: 10px;
            background: white;
            border-radius: 4px;
        }
        .rangee {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
        }
        .table {
            border: 2px solid #333;
            border-radius: 4px;
            background: #fff;
            padding: 2px;
        }
        .table-binome {
            width: 100px;
            height: 30px;
            display: flex;
            justify-content: space-between;
        }
        .table-groupe {
            width: 160px;
            height: 30px;
            display: flex;
            justify-content: space-between;
        }
        .place {
            width: 25px;
            height: 25px;
            background: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
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
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .grid-salles {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
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
            margin: 15% auto;
            padding: 20px;
            width: 70%;
            max-width: 500px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestion des Classes</h1>
            <div>
                <a href="dashboard.php" class="btn">Retour</a>
                <button onclick="showAddSalleModal()" class="btn btn-success">Ajouter une salle</button>
            </div>
        </div>

        <!-- Types de Configuration -->
        <h2>Types de Configuration</h2>
        <div class="classe-types">
            <!-- Type 1: Tables binômes -->
            <div class="classe-type">
                <h3>Type 1: Tables binômes</h3>
                <p>30 places (15 tables de 2)</p>
                <div class="salle-preview">
                    <?php for($rangee = 0; $rangee < 5; $rangee++): ?>
                        <div class="rangee">
                            <?php for($table = 0; $table < 3; $table++): 
                                $place_num = $rangee * 6 + $table * 2 + 1;
                            ?>
                                <div class="table table-binome">
                                    <div class="place"><?php echo $place_num; ?></div>
                                    <div class="place"><?php echo $place_num + 1; ?></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Type 2: Tables de 4 en ligne -->
            <div class="classe-type">
                <h3>Type 2: Tables de 4</h3>
                <p>32 places (8 tables de 4 en ligne)</p>
                <div class="salle-preview">
                    <?php for($rangee = 0; $rangee < 4; $rangee++): ?>
                        <div class="rangee">
                            <?php for($table = 0; $table < 2; $table++): 
                                $place_num = $rangee * 8 + $table * 4 + 1;
                            ?>
                                <div class="table table-groupe">
                                    <div class="place"><?php echo $place_num; ?></div>
                                    <div class="place"><?php echo $place_num + 1; ?></div>
                                    <div class="place"><?php echo $place_num + 2; ?></div>
                                    <div class="place"><?php echo $place_num + 3; ?></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Liste des salles existantes -->
        <h2>Salles Existantes</h2>
        <div class="grid-salles">
            <?php foreach($salles as $salle): ?>
                <div class="classe-type">
                    <h3><?php echo htmlspecialchars($salle['nom_salle']); ?></h3>
                    <p>
                        Type: <?php 
                            echo $salle['type_salle'] === 'binome' ? 'Tables binômes (2 places)' : 'Tables groupées (4 places)'; 
                        ?><br>
                        Capacité: <?php echo $salle['capacite']; ?> places
                    </p>
                    <div class="actions">
                        <button onclick="editSalle(<?php echo $salle['id']; ?>)" class="btn">Modifier</button>
                        <button onclick="deleteSalle(<?php echo $salle['id']; ?>)" class="btn btn-danger">Supprimer</button>
                        <button onclick="voirDisposition(<?php echo $salle['id']; ?>)" class="btn">Voir disposition</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal d'ajout/modification de salle -->
    <div id="salleModal" class="modal">
        <div class="modal-content">
            <h2>Ajouter/Modifier une Salle</h2>
            <form id="salleForm">
                <input type="hidden" id="salle_id" name="salle_id">
                
                <div class="form-group">
                    <label for="nom_salle">Nom de la salle</label>
                    <input type="text" id="nom_salle" name="nom_salle" required>
                </div>

                <div class="form-group">
                    <label for="type_salle">Type de salle</label>
                    <select id="type_salle" name="type_salle" required>
                        <option value="binome">Tables binômes (2 places)</option>
                        <option value="groupe">Tables groupées (4 places)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="capacite">Capacité</label>
                    <select id="capacite" name="capacite" required>
                        <option value="30">30 places (Type 1)</option>
                        <option value="32">32 places (Type 2)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Enregistrer</button>
                <button type="button" onclick="closeModal()" class="btn">Annuler</button>
            </form>
        </div>
    </div>

    <script>
    function showAddSalleModal() {
        document.getElementById('salleForm').reset();
        document.getElementById('salle_id').value = '';
        document.getElementById('salleModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('salleModal').style.display = 'none';
    }

    function editSalle(salleId) {
        fetch('ajax/get_salle.php?id=' + salleId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('salle_id').value = data.data.id;
                    document.getElementById('nom_salle').value = data.data.nom_salle;
                    document.getElementById('type_salle').value = data.data.type_salle;
                    document.getElementById('capacite').value = data.data.capacite;
                    document.getElementById('salleModal').style.display = 'block';
                } else {
                    alert('Erreur : ' + data.message);
                }
            });
    }

    function deleteSalle(salleId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette salle ?')) {
            fetch('ajax/delete_salle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: salleId })
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

    function voirDisposition(salleId) {
        window.location.href = 'disposition_salle.php?id=' + salleId;
    }

    document.getElementById('salleForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('ajax/save_salle.php', {
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

    // Mise à jour automatique de la capacité en fonction du type de salle
    document.getElementById('type_salle').onchange = function() {
        const capaciteSelect = document.getElementById('capacite');
        capaciteSelect.value = this.value === 'binome' ? '30' : '32';
    };
    </script>
</body>
</html>