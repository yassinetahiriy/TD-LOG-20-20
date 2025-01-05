<?php
session_start();
require_once '../config/database.php';



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

        /* Sidebar styles comme avant */
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

        .card {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 2rem;
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

        .student-selection {
            display: grid;
            grid-template-columns: 1fr 80px 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }

        .student-list {
            background-color: var(--white);
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            height: 400px;
            overflow-y: auto;
            padding: 1rem;
        }

        .student-item {
            padding: 0.75rem;
            margin: 0.5rem 0;
            background: var(--light);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .student-item:hover {
            background: #e2e8f0;
        }

        .student-item.selected {
            background: var(--primary);
            color: var(--white);
        }

        .transfer-buttons {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1rem;
        }

        .transfer-btn {
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            background: var(--primary);
            color: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .transfer-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
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
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .button-group {
            display: flex;
            gap: 1rem;
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

            .student-selection {
                grid-template-columns: 1fr;
            }

            .transfer-buttons {
                flex-direction: row;
                justify-content: center;
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
            <a href="classes.php" class="nav-link">
                <i class="fas fa-school"></i>
                <span>Classes</span>
            </a>
            <a href="reports.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span>Rapports</span>
            </a>
            <a href="creer_groupe.php" class="nav-link active">
                <i class="fas fa-user-friends"></i>
                <span>Groupes TD</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>Créer un Groupe TD</h1>
            <div class="button-group">
                <a href="groupes.php" class="btn btn-primary">
                    <i class="fas fa-list"></i>
                    <span>Voir les groupes</span>
                </a>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour</span>
                </a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form id="createGroupForm">
                <div class="form-group">
                    <label for="nom_groupe">
                        <i class="fas fa-users"></i> Nom du groupe
                    </label>
                    <input type="text" id="nom_groupe" name="nom_groupe" 
                           class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="annee_scolaire">
                        <i class="fas fa-calendar"></i> Année scolaire
                    </label>
                    <input type="text" id="annee_scolaire" name="annee_scolaire" 
                           class="form-control" placeholder="2023-2024" required>
                </div>

                <div class="student-selection">
                    <div>
                        <h3>
                            <i class="fas fa-user"></i> 
                            Étudiants disponibles
                        </h3>
                        <div id="availableStudents" class="student-list">
                            <!-- Rempli par JavaScript -->
                        </div>
                    </div>

                    <div class="transfer-buttons">
                        <button type="button" class="transfer-btn" onclick="addSelectedStudents()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button type="button" class="transfer-btn" onclick="removeSelectedStudents()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>

                    <div>
                        <h3>
                            <i class="fas fa-user-check"></i> 
                            Étudiants sélectionnés
                        </h3>
                        <div id="selectedStudents" class="student-list">
                            <!-- Rempli par JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        <span>Créer le groupe</span>
                    </button>
                </div>
            </form>
        </div>

        <div id="groupeSuccess" class="card" style="display: none;">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Groupe créé avec succès !
            </div>
            <div class="button-group">
                <button onclick="location.reload()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Créer un autre groupe</span>
                </button>
                <a href="groupes.php" class="btn btn-success">
                    <i class="fas fa-list"></i>
                    <span>Voir tous les groupes</span>
                </a>
            </div>
        </div>
    </main>
    <script>
    // Initialisation des données
    let availableStudents = <?php echo json_encode($etudiants_non_assignes); ?>;
    let selectedStudents = [];

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

    // Rendu des listes d'étudiants
    function renderStudentLists() {
        const availableList = document.getElementById('availableStudents');
        const selectedList = document.getElementById('selectedStudents');

        availableList.innerHTML = availableStudents.map(student => `
            <div class="student-item" onclick="toggleStudentSelection(this, ${student.id})">
                <i class="fas fa-user"></i>
                ${student.nom} ${student.prenom}
            </div>
        `).join('');

        selectedList.innerHTML = selectedStudents.map(student => `
            <div class="student-item" onclick="toggleStudentSelection(this, ${student.id})">
                <i class="fas fa-user-check"></i>
                ${student.nom} ${student.prenom}
            </div>
        `).join('');
    }

    // Sélection des étudiants
    function toggleStudentSelection(element, studentId) {
        element.classList.toggle('selected');
    }

    // Transfert des étudiants
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
        showNotification('Étudiants ajoutés au groupe', 'success');
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
        showNotification('Étudiants retirés du groupe', 'info');
    }

    // Soumission du formulaire
    document.getElementById('createGroupForm').onsubmit = function(e) {
        e.preventDefault();

        if (selectedStudents.length === 0) {
            showNotification('Veuillez sélectionner au moins un étudiant', 'error');
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
                showNotification('Groupe créé avec succès', 'success');
            } else {
                showNotification(data.message || 'Erreur lors de la création du groupe', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Une erreur est survenue', 'error');
        });
    };

    // Système de notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                              type === 'error' ? 'fa-exclamation-circle' : 
                              'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);

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

            .notification.info {
                background-color: #e0f2fe;
                color: #075985;
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

    // Initialisation
    renderStudentLists();
    </script>
</body>
</html>    
