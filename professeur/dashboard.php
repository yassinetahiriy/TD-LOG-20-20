<?php
require_once '../config/session_config.php';
require_once '../config/database.php';
checkUserSession('professeur');

// Récupérer les séances avec les informations détaillées
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

// Compter les différents types de séances
$seances_programmees = array_filter($seances, function($s) { return $s['statut'] === 'programmee'; });
$seances_en_cours = array_filter($seances, function($s) { return $s['statut'] === 'en_cours'; });
$seances_terminees = array_filter($seances, function($s) { return $s['statut'] === 'terminee'; });
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Professeur</title>
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: var(--white);
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: bold;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
        }

        .seances-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .seance-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .seance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .section-title {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .salle-info {
            background-color: var(--light);
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .type-salle {
            background-color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            color: var(--primary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .status-programmee {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-en_cours {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-terminee {
            background-color: #fee2e2;
            color: #991b1b;
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
            color: var(--white);
        }

        .btn-primary {
            background-color: var(--primary);
        }

        .btn-danger {
            background-color: var(--danger);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
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
            <a href="#" class="sidebar-logo">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Espace Professeur</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="liste_absences.php" class="nav-link">
                <i class="fas fa-user-times"></i>
                <span>Liste des Absences</span>
            </a>
            
        </nav>
    
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['prenom'], 0, 1)); ?>
            </div>
            <div>
                <h3><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></h3>
                <p>Professeur</p>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <h1>Tableau de bord</h1>
            <a href="../logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--primary);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-value"><?php echo count($seances_programmees); ?></div>
                <div class="stat-label">Séances programmées</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--success);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo count($seances_en_cours); ?></div>
                <div class="stat-label">Séances en cours</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--warning);">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-value"><?php echo count($seances_terminees); ?></div>
                <div class="stat-label">Séances terminées</div>
            </div>
        </div>

        <!-- Séances -->
        <h2 class="section-title">
            <i class="fas fa-calendar-alt"></i>
            Vos prochaines séances
        </h2>
        
        <div class="seances-grid">
            <?php if (empty($seances)): ?>
                <div class="seance-card">
                    <p>Aucune séance programmée.</p>
                </div>
            <?php else: ?>
                <?php foreach ($seances as $seance): ?>
                    <div class="seance-card">
                        <h3><?php echo htmlspecialchars($seance['nom_groupe']); ?></h3>
                        <p>
                            <i class="fas fa-calendar"></i>
                            <?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?>
                        </p>
                        <p>
                            <i class="fas fa-clock"></i>
                            <?php echo substr($seance['heure_debut'], 0, 5); ?> - 
                            <?php echo substr($seance['heure_fin'], 0, 5); ?>
                        </p>
                        <div class="salle-info">
                            <i class="fas fa-door-open"></i>
                            <strong><?php echo htmlspecialchars($seance['nom_salle']); ?></strong>
                            <span class="type-salle">
                                <?php echo $seance['type_salle'] === 'binome' ? 'Tables binômes' : 'Tables de 4'; ?>
                            </span>
                        </div>
                        <span class="status-badge status-<?php echo $seance['statut']; ?>">
                            <?php 
                            switch($seance['statut']) {
                                case 'programmee':
                                    echo '<i class="fas fa-calendar"></i> Programmée';
                                    break;
                                case 'en_cours':
                                    echo '<i class="fas fa-play"></i> En cours';
                                    break;
                                case 'terminee':
                                    echo '<i class="fas fa-check"></i> Terminée';
                                    break;
                            }
                            ?>
                        </span>
                        
                        <?php if ($seance['statut'] === 'programmee'): ?>
                            <button onclick="startSession(<?php echo $seance['id']; ?>)" class="btn btn-primary">
                                <i class="fas fa-play"></i>
                                Démarrer la séance
                            </button>
                        <?php elseif ($seance['statut'] === 'en_cours'): ?>
                            <a href="gestion_presence.php?seance_id=<?php echo $seance['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-users"></i>
                                Gérer la présence
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'gestion_presence.php?seance_id=' + seanceId;
                } else {
                    showNotification(data.message || 'Erreur lors du démarrage de la séance', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur lors du démarrage de la séance', 'error');
            });
        }
    }

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

        // Styles pour les notifications
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

    // Fermer la sidebar en cliquant en dehors
    document.addEventListener('click', (e) => {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
    </script>
</body>
</html>
