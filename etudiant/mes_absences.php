<?php
require_once '../config/session_config.php';
require_once '../config/database.php';
checkUserSession('etudiant');

try {
    // Récupérer toutes les absences de l'étudiant
    $stmt = $conn->prepare("
        SELECT 
            s.date_seance,
            s.heure_debut,
            s.heure_fin,
            sl.nom_salle,
            sl.type_salle,
            g.nom_groupe,
            u.nom as prof_nom,
            u.prenom as prof_prenom,
            p.status,
            p.heure_marquage
        FROM seances s
        JOIN salles sl ON s.id_salle = sl.id
        JOIN groupes_td g ON s.id_groupe_td = g.id
        JOIN users u ON s.id_professeur = u.id
        JOIN groupes_td_etudiants gte ON g.id = gte.id_groupe_td
        LEFT JOIN presences p ON s.id = p.id_seance AND p.id_etudiant = ?
        WHERE gte.id_etudiant = ?
        AND (p.status = 'absent' OR p.status IS NULL)
        AND s.statut = 'terminee'
        ORDER BY s.date_seance DESC, s.heure_debut DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compter le total des absences
    $total_absences = count($absences);

} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Absences</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --danger: #ef4444;
            --light: #f1f5f9;
            --white: #ffffff;
            --dark: #1e293b;
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        /* Sidebar styles comme dans dashboard.php */
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
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .absences-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .absence-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid var(--danger);
            transition: transform 0.3s ease;
        }

        .absence-card:hover {
            transform: translateY(-5px);
        }

        .absence-date {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .absence-info {
            display: grid;
            gap: 0.5rem;
            color: var(--dark);
        }

        .absence-info div {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .absence-info i {
            width: 20px;
            color: var(--primary);
        }

        /* Styles existants du dashboard */
        .sidebar-header, .nav-link {
            /* Copier les styles du dashboard.php */
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

            .absences-grid {
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
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="mes_absences.php" class="nav-link active">
                <i class="fas fa-user-times"></i>
                <span>Mes Absences</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div>
                <h1 class="section-title">Mes Absences</h1>
                <p>Total : <?php echo $total_absences; ?> absence<?php echo $total_absences > 1 ? 's' : ''; ?></p>
            </div>
        </div>

        <div class="absences-grid">
            <?php if (empty($absences)): ?>
                <div class="absence-card">
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                        <p>Aucune absence enregistrée.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($absences as $absence): ?>
                    <div class="absence-card">
                        <div class="absence-date">
                            <?php echo date('d/m/Y', strtotime($absence['date_seance'])); ?>
                        </div>
                        <div class="absence-info">
                            <div>
                                <i class="fas fa-clock"></i>
                                <?php echo substr($absence['heure_debut'], 0, 5); ?> - 
                                <?php echo substr($absence['heure_fin'], 0, 5); ?>
                            </div>
                            <div>
                                <i class="fas fa-user-tie"></i>
                                <?php echo htmlspecialchars($absence['prof_nom'] . ' ' . $absence['prof_prenom']); ?>
                            </div>
                            <div>
                                <i class="fas fa-door-open"></i>
                                <?php echo htmlspecialchars($absence['nom_salle']); ?>
                            </div>
                            <div>
                                <i class="fas fa-users"></i>
                                <?php echo htmlspecialchars($absence['nom_groupe']); ?>
                            </div>
                        </div>
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
