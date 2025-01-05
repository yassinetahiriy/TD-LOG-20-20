<?php
session_start();
require_once '../config/database.php';



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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #0ea5e9;
            --success: #22c55e;
            --warning: #eab308;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f1f5f9;
            --white: #ffffff;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--light);
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--white);
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .sidebar-header {
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--light);
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: var(--dark);
            text-decoration: none;
            border-radius: 0.5rem;
            margin: 0.5rem 0;
            transition: all 0.3s ease;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }

        .nav-link:hover, 
        .nav-link.active {
            background-color: var(--primary);
            color: var(--white);
        }

        .sidebar-nav {
            margin-top: 1rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .card {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .classe-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .classe-type {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .salle-preview {
            margin-top: 1rem;
            padding: 1rem;
            background-color: var(--light);
            border-radius: 0.5rem;
        }

        .rangee {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .table {
            border: 2px solid var(--dark);
            border-radius: 0.5rem;
            background: var(--white);
            padding: 0.25rem;
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
            background: var(--secondary);
            border: 1px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: var(--white);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            color: var(--white);
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
        }

        .btn-success {
            background-color: var(--success);
        }

        .btn-danger {
            background-color: var(--danger);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .grid-salles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .salle-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .salle-card h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .salle-info {
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .salle-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            position: relative;
            margin: 10% auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-logo">
                <i class="fas fa-user-graduate"></i>
                <span>AdminPanel</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="seances.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i>
                <span>Séances</span>
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="classes.php" class="nav-link active">
                <i class="fas fa-school"></i>
                <span>Classes</span>
            </a>
            <a href="reports.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span>Rapports</span>
            </a>
            <a href="creer_groupe.php" class="nav-link">
                <i class="fas fa-user-friends"></i>
                <span>Groupes TD</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <h1 class="section-title">Gestion des Classes</h1>
            <div class="button-group">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour</span>
                </a>
                <button onclick="showAddSalleModal()" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    <span>Ajouter une salle</span>
                </button>
            </div>
        </div>

        <!-- Types de Configuration -->
        <h2 class="section-title">Types de Configuration</h2>
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

            <!-- Type 2: Tables de 4 -->
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
        <h2 class="section-title">Salles Existantes</h2>
        <div class="grid-salles">
            <?php foreach($salles as $salle): ?>
                <div class="salle-card">
                    <h3><?php echo htmlspecialchars($salle['nom_salle']); ?></h3>
                    <div class="salle-info">
                        <p>
                            <i class="fas fa-chairs"></i>
                            Type: <?php echo $salle['type_salle'] === 'binome' ? 'Tables binômes (2 places)' : 'Tables groupées (4 places)'; ?>
                        </p>
                        <p>
                            <i class="fas fa-users"></i>
                            Capacité: <?php echo $salle['capacite']; ?> places
                        </p>
                    </div>
                    <div class="salle-actions">
                        <button onclick="editSalle(<?php echo $salle['id']; ?>)" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <button onclick="deleteSalle(<?php echo $salle['id']; ?>)" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modal d'ajout/modification de salle -->
        <div id="salleModal" class="modal">
            <div class="modal-content">
                <h2>Ajouter/Modifier une Salle</h2>
                <form id="salleForm">
                    <input type="hidden" id="salle_id" name="salle_id">
                    
                    <div class="form-group">
                        <label for="nom_salle">Nom de la salle</label>
                        <input type="text" id="nom_salle" name="nom_salle" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="type_salle">Type de salle</label>
                        <select id="type_salle" name="type_salle" class="form-control" required>
                            <option value="binome">Tables binômes (2 places)</option>
                            <option value="groupe">Tables groupées (4 places)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="capacite">Capacité</label>
                        <select id="capacite" name="capacite" class="form-control" required>
                            <option value="30">
                            <option value="30">30 places (Type 1)</option>
                            <option value="32">32 places (Type 2)</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <button type="button" onclick="closeModal()" class="btn btn-primary">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
    // Menu toggle pour mobile
    const menuToggle = document.createElement('button');
    menuToggle.className = 'menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.body.appendChild(menuToggle);

    menuToggle.addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    // Fermer la sidebar en cliquant en dehors
    document.addEventListener('click', (e) => {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });

    // Gestion des salles
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
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Une erreur est survenue', 'error');
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
                    showNotification('Salle supprimée avec succès', 'success');
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la suppression', 'error');
            });
        }
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
                showNotification('Salle enregistrée avec succès', 'success');
                location.reload();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de l\'enregistrement', 'error');
        });
    };

    // Mise à jour automatique de la capacité en fonction du type de salle
    document.getElementById('type_salle').onchange = function() {
        const capaciteSelect = document.getElementById('capacite');
        capaciteSelect.value = this.value === 'binome' ? '30' : '32';
    };

    // Système de notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);

        // Style pour les notifications
        const style = document.createElement('style');
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem;
                border-radius: 0.5rem;
                background: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
            }

            .notification-content {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .notification.success {
                background-color: #dcfce7;
                color: #166534;
            }

            .notification.error {
                background-color: #fee2e2;
                color: #991b1b;
            }

            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            .menu-toggle {
                display: none;
                position: fixed;
                top: 1rem;
                right: 1rem;
                z-index: 1000;
                padding: 0.75rem;
                border-radius: 0.5rem;
                background-color: var(--white);
                border: none;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                cursor: pointer;
            }

            @media (max-width: 1024px) {
                .menu-toggle {
                    display: block;
                }
            }
        `;
        document.head.appendChild(style);

        // Supprimer la notification après 3 secondes
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Fermer le modal en cliquant en dehors
    window.onclick = function(event) {
        const modal = document.getElementById('salleModal');
        if (event.target === modal) {
            closeModal();
        }
    };
    </script>
</body>
</html>                                
