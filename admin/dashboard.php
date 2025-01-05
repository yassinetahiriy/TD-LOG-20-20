<?php
require_once '../config/session_config.php';
require_once '../config/database.php';
checkUserSession('admin');

// Récupérer quelques statistiques basiques
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'etudiant'");
    $totalEtudiants = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'professeur'");
    $totalProfesseurs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM seances");
    $totalSeances = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch(PDOException $e) {
    $totalEtudiants = 0;
    $totalProfesseurs = 0;
    $totalSeances = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur | Gestion des Présences</title>
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

        /* Sidebar Styles */
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
            transition: transform 0.3s ease-in-out;
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

        .nav-link:hover {
            background-color: var(--primary);
            color: var(--white);
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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

        .logout-button {
            padding: 0.5rem 1rem;
            background-color: var(--danger);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .logout-button:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }

        /* Stats Cards */
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

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .action-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.25rem;
        }

        .action-title {
            font-weight: 600;
            color: var(--dark);
        }

        .action-description {
            color: #64748b;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--light);
            color: var(--dark);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .action-button:hover {
            background-color: var(--primary);
            color: var(--white);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .menu-toggle {
                display: block;
            }
        }

        .menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 101;
            padding: 0.5rem;
            border-radius: 0.5rem;
            background-color: var(--white);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
        }

    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-logo">
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
            <a href="creer_groupe.php" class="nav-link">
                <i class="fas fa-user-friends"></i>
                <span>Groupes TD</span>
            </a>
        </nav>
    </aside>

    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="user-welcome">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['prenom'], 0, 1)); ?>
                </div>
                <div>
                    <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom']); ?></h2>
                    <p>Administrateur</p>
                </div>
            </div>
            <a href="../logout.php" class="logout-button">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--secondary);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-value"><?php echo $totalEtudiants; ?></div>
                <div class="stat-label">Étudiants inscrits</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--success);">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-value"><?php echo $totalProfesseurs; ?></div>
                <div class="stat-label">Professeurs actifs</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--warning);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-value"><?php echo $totalSeances; ?></div>
                <div class="stat-label">Séances programmées</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-card">
                <div class="action-header">
                    <div class="action-icon" style="background-color: var(--primary);">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h3 class="action-title">Gestion des Séances</h3>
                </div>
                <p class="action-description">
                    Planifiez et gérez les séances de cours, suivez les présences en temps réel.
                </p>
                <a href="seances.php" class="action-button">
                    <i class="fas fa-arrow-right"></i>
                    <span>Gérer les séances</span>
                </a>
            </div>

            <div class="action-card">
                <div class="action-header">
                    <div class="action-icon" style="background-color: var(--secondary);">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="action-title">Gestion des Utilisateurs</h3>
                </div>
                <p class="action-description">
                    Administrez les comptes des étudiants et des professeurs.
                </p>
                <a href="users.php" class="action-button">
                    <i class="fas fa-arrow-right"></i>
                    <span>Gérer les utilisateurs</span>
                </a>
            </div>

            <div class="action-card">
                <div class="action-header">
                    <div class="action-icon" style="background-color: var(--success);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="action-title">Rapports & Statistiques</h3>
                </div>
                <p class="action-description">
                    Consultez les statistiques de présence et générez des rapports.
                </p>
                <a href="reports.php" class="action-button">
                    <i class="fas fa-arrow-right"></i>
                    <span>Voir les rapports</span>
                </a>
            </div>
        </div>
    </main>

    <script>
        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
