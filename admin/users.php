<?php
require_once '../config/session_config.php';
checkUserSession('admin'); // Pour s'assurer que seul l'admin a accès
require_once '../config/database.php';

try {
    // Récupérer tous les utilisateurs sauf l'admin connecté
    $stmt = $conn->prepare("
        SELECT u.*, 
               GROUP_CONCAT(DISTINCT g.nom_groupe) as groupes_td
        FROM users u
        LEFT JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
        LEFT JOIN groupes_td g ON gte.id_groupe_td = g.id
        WHERE u.id != :current_user_id
        GROUP BY u.id, u.nom, u.prenom, u.email, u.user_type, u.photo_url
        ORDER BY u.user_type, u.nom, u.prenom
    ");
    $stmt->bindValue(':current_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les groupes TD
    $stmt = $conn->prepare("SELECT id, nom_groupe FROM groupes_td ORDER BY nom_groupe");
    $stmt->execute();
    $groupes_td = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo '<div class="error-message">Erreur lors de la récupération des utilisateurs : ' . htmlspecialchars($e->getMessage()) . '</div>';
    $users = [];
    $groupes_td = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
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

        .filter-card {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .select-container {
            position: relative;
            flex: 1;
        }

        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            appearance: none;
            background-color: var(--white);
            font-size: 0.875rem;
            cursor: pointer;
        }

        .select-container::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .table-container {
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: var(--dark);
        }

        .user-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light);
        }

        .groupe-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #e3f2fd;
            color: #1e40af;
            border-radius: 9999px;
            font-size: 0.75rem;
            margin: 0.25rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
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

        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark);
        }

        select[multiple] {
            height: 200px;
            padding: 0.5rem;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
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
    <!-- Sidebar comme dans les autres pages -->
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
            <a href="users.php" class="nav-link active">
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
            <a href="creer_groupe.php" class="nav-link">
                <i class="fas fa-user-friends"></i>
                <span>Groupes TD</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <h1 class="section-title">Gestion des Utilisateurs</h1>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Retour au tableau de bord
            </a>
        </div>

        <div class="filter-card">
            <div class="select-container">
                <select id="typeFilter" onchange="filterUsers()">
                    <option value="">Tous les utilisateurs</option>
                    <option value="professeur">Professeurs</option>
                    <option value="etudiant">Étudiants</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        
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
                            
                            <td><?php echo htmlspecialchars($user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="groupe-badge">
                                    <?php
                                    switch($user['user_type']) {
                                        case 'professeur':
                                            echo 'Professeur';
                                            break;
                                        case 'etudiant':
                                            echo 'Étudiant';
                                            break;
                                        default:
                                            echo 'Type inconnu';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['user_type'] === 'etudiant'): ?>
                                    <?php if (!empty($user['groupes_td'])): ?>
                                        <?php foreach(explode(',', $user['groupes_td']) as $groupe): ?>
                                            <span class="groupe-badge">
                                                <?php echo htmlspecialchars(trim($groupe)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <button onclick="assignerGroupe(<?php echo $user['id']; ?>)" class="btn btn-primary">
                                        <i class="fas fa-users"></i> Assigner Groupe
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
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
                        <label for="groupes">Sélectionner les groupes</label>
                        <select multiple name="groupes[]" id="groupes">
                            <?php foreach ($groupes_td as $groupe): ?>
                                <option value="<?php echo $groupe['id']; ?>">
                                    <?php echo htmlspecialchars($groupe['nom_groupe']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
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

   // Fermer le sidebar en cliquant en dehors
   document.addEventListener('click', (e) => {
       const sidebar = document.querySelector('.sidebar');
       if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
           sidebar.classList.remove('active');
       }
   });

   // Fonctions pour la gestion des utilisateurs
   function assignerGroupe(userId) {
       document.getElementById('etudiant_id').value = userId;
       document.getElementById('groupeModal').style.display = 'block';

       // Charger les groupes actuels de l'étudiant
       fetch('ajax/get_groupes_etudiant.php?id=' + userId)
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   const select = document.getElementById('groupes');
                   const options = select.options;
                   for (let i = 0; i < options.length; i++) {
                       options[i].selected = data.groupes.includes(parseInt(options[i].value));
                   }
               } else {
                   showNotification('Erreur lors du chargement des groupes', 'error');
               }
           })
           .catch(error => {
               console.error('Erreur:', error);
               showNotification('Erreur lors du chargement des groupes', 'error');
           });
   }

   function closeGroupeModal() {
       document.getElementById('groupeModal').style.display = 'none';
   }

   // Gestion de la modal
   window.onclick = function(event) {
       const modal = document.getElementById('groupeModal');
       if (event.target === modal) {
           modal.style.display = 'none';
       }
   }

   // Soumission du formulaire d'assignation de groupe
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
               showNotification('Groupes assignés avec succès', 'success');
               location.reload();
           } else {
               showNotification(data.message || 'Erreur inconnue', 'error');
           }
       })
       .catch(error => {
           console.error('Erreur:', error);
           showNotification('Erreur lors de l\'assignation des groupes', 'error');
       });
   };

   // Filtrage des utilisateurs
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

   // Suppression d'un utilisateur
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
                   showNotification('Utilisateur supprimé avec succès', 'success');
                   location.reload();
               } else {
                   showNotification(data.message || 'Erreur inconnue', 'error');
               }
           })
           .catch(error => {
               console.error('Erreur:', error);
               showNotification('Erreur lors de la suppression', 'error');
           });
       }
   }

   // Fonction de notification
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

       // Ajouter le style de notification
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

   // Style pour le menu toggle
   const style = document.createElement('style');
   style.textContent = `
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
   `;
   document.head.appendChild(style);
</script>
</body>
</html>
