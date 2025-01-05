<?php
require_once '../config/session_config.php';
require_once '../config/database.php';
checkUserSession('etudiant');

try {
    // Récupérer les séances de l'étudiant
    $stmt = $conn->prepare("
        SELECT s.*, sl.nom_salle, sl.type_salle, g.nom_groupe,
               u.nom as prof_nom, u.prenom as prof_prenom,
               p.id as presence_id, p.status as presence_status
        FROM seances s
        JOIN salles sl ON s.id_salle = sl.id
        JOIN groupes_td g ON s.id_groupe_td = g.id
        JOIN users u ON s.id_professeur = u.id
        JOIN groupes_td_etudiants gte ON g.id = gte.id_groupe_td
        LEFT JOIN presences p ON s.id = p.id_seance AND p.id_etudiant = ?
        WHERE gte.id_etudiant = ?
        AND s.date_seance = CURDATE()
        ORDER BY s.heure_debut ASC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $seances_aujourdhui = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les statistiques
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_seances,
            SUM(CASE WHEN p.status = 'present' THEN 1 ELSE 0 END) as seances_presentes
        FROM seances s
        JOIN groupes_td_etudiants gte ON s.id_groupe_td = gte.id_groupe_td
        LEFT JOIN presences p ON s.id = p.id_seance AND p.id_etudiant = ?
        WHERE gte.id_etudiant = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Étudiant</title>
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
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

        .section-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .seance-card {
            background-color: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .seance-card:hover {
            transform: translateY(-5px);
        }

        .seance-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .seance-info p {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-present {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-absent {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
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

            .seance-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-logo">
                <i class="fas fa-user-graduate"></i>
                <span>Espace Étudiant</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-link active">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="mes_absences.php" class="nav-link">
                <i class="fas fa-user-times"></i>
                <span>Mes Absences</span>
            </a>
            
            
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="user-welcome">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['prenom'], 0, 1)); ?>
                </div>
                <div>
                    <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom']); ?></h2>
                    <p>Étudiant</p>
                </div>
            </div>
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
                <div class="stat-value"><?php echo $stats['total_seances']; ?></div>
                <div class="stat-label">Total des séances</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--success);">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?php echo $stats['seances_presentes']; ?></div>
                <div class="stat-label">Séances présentes</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--warning);">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">
                    <?php echo $stats['total_seances'] > 0 ? 
                        round(($stats['seances_presentes'] / $stats['total_seances']) * 100) : 0; ?>%
                </div>
                <div class="stat-label">Taux de présence</div>
            </div>
        </div>

        <!-- Séances d'aujourd'hui -->
        <div class="section-title">
            <i class="fas fa-calendar-day"></i>
            <span>Séances d'aujourd'hui</span>
        </div>

        <?php if (!empty($seances_aujourdhui)): ?>
            <?php foreach ($seances_aujourdhui as $seance): ?>
                <div class="seance-card">
                    <h3><?php echo htmlspecialchars($seance['nom_groupe']); ?></h3>
                    <div class="seance-info">
                        <p>
                            <i class="fas fa-clock"></i>
                            <?php echo substr($seance['heure_debut'], 0, 5); ?> - 
                            <?php echo substr($seance['heure_fin'], 0, 5); ?>
                        </p>
                        <p>
                            <i class="fas fa-door-open"></i>
                            <?php echo htmlspecialchars($seance['nom_salle']); ?>
                            (<?php echo $seance['type_salle'] === 'binome' ? 'Tables binômes' : 'Tables de 4'; ?>)
                        </p>
                        <p>
                            <i class="fas fa-chalkboard-teacher"></i>
                            <?php echo htmlspecialchars($seance['prof_nom'] . ' ' . $seance['prof_prenom']); ?>
                        </p>
                    </div>
                    
                    <?php if ($seance['statut'] === 'en_cours'): ?>
                        <?php if ($seance['presence_id']): ?>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <span class="status-badge status-present">
                                    <i class="fas fa-check"></i>
                                    Présence marquée
                                </span>
                                <!-- Ajout du lien pour retourner à la vérification -->
                                <a href="marquer_presence.php?seance_id=<?php echo $seance['id']; ?>" 
                                class="btn btn-primary">
                                    <i class="fas fa-user-check"></i>
                                    Retourner à la validation
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="marquer_presence.php?seance_id=<?php echo $seance['id']; ?>" 
                            class="btn btn-success">
                                <i class="fas fa-check"></i>
                                Marquer ma présence
                            </a>
                        <?php endif; ?>
                    <?php elseif ($seance['statut'] === 'programmee'): ?>
                        <span class="status-badge status-pending">
                        <i class="fas fa-clock"></i>
                            À venir
                        </span>
                    <?php else: ?>
                        <?php if ($seance['presence_id']): ?>
                            <span class="status-badge status-present">
                                <i class="fas fa-check"></i>
                                Présent
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-absent">
                                <i class="fas fa-times"></i>
                                Absent
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="seance-card">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--dark); margin-bottom: 1rem;"></i>
                    <p>Aucune séance programmée aujourd'hui.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Actions rapides -->
        
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
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>
