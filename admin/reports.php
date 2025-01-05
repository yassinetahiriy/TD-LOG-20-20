<?php
require_once '../config/session_config.php';
require_once '../config/database.php';
checkUserSession('admin');

$search = $_GET['search'] ?? '';

try {
   // Stats étudiant
   $stmt = $conn->prepare("
       SELECT 
           u.id,
           u.nom,
           u.prenom,
           u.photo_url,
           g.nom_groupe,
           COUNT(DISTINCT s.id) as total_seances,
           COUNT(DISTINCT CASE WHEN p.status = 'present' THEN s.id END) as total_presences,
           COUNT(DISTINCT CASE WHEN p.status = 'absent' THEN s.id END) as total_absences,
           GROUP_CONCAT(DISTINCT g.nom_groupe) as groupes
       FROM users u
       LEFT JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
       LEFT JOIN groupes_td g ON gte.id_groupe_td = g.id
       LEFT JOIN seances s ON g.id = s.id_groupe_td
       LEFT JOIN presences p ON s.id = p.id_seance AND u.id = p.id_etudiant
       WHERE u.user_type = 'etudiant'
       AND (u.nom LIKE ? OR u.prenom LIKE ?)
       GROUP BY u.id
   ");
   $stmt->execute(["%$search%", "%$search%"]);
   $etudiants = $stmt->fetchAll();

   // Détails des séances pour chaque étudiant
   $stmt_seances = $conn->prepare("
       SELECT 
           s.*,
           sl.nom_salle,
           sl.type_salle,
           g.nom_groupe,
           prof.nom as prof_nom,
           prof.prenom as prof_prenom,
           p.status,
           p.numero_place,
           COUNT(v.id) as validations_count
       FROM seances s
       JOIN salles sl ON s.id_salle = sl.id
       JOIN groupes_td g ON s.id_groupe_td = g.id
       JOIN users prof ON s.id_professeur = prof.id
       LEFT JOIN presences p ON s.id = p.id_seance AND p.id_etudiant = ?
       LEFT JOIN validations_presence v ON p.id = v.id_presence
       WHERE s.id_groupe_td IN (
           SELECT id_groupe_td 
           FROM groupes_td_etudiants 
           WHERE id_etudiant = ?
       )
       GROUP BY s.id
       ORDER BY s.date_seance DESC, s.heure_debut DESC
   ");
} catch(PDOException $e) {
   $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Détails des Séances - Étudiants</title>
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
   <style>
       :root {
           --primary: #4f46e5;
           --secondary: #0ea5e9;
           --success: #22c55e;
           --danger: #ef4444;
           --warning: #f59e0b;
           --white: #ffffff;
           --light: #f3f4f6;
           --dark: #1f2937;
           --gray: #9ca3af;
           --sidebar-width: 280px;
       }

       body {
           background: var(--light);
           font-family: 'Inter', sans-serif;
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

        .nav-link:hover, .nav-link.active {
            background-color: var(--primary);
            color: var(--white);
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }

       .student-card {
           background: var(--white);
           border-radius: 1rem;
           padding: 2rem;
           margin-bottom: 2rem;
           box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
       }

       .student-header {
           display: flex;
           align-items: center;
           gap: 2rem;
           margin-bottom: 2rem;
           padding-bottom: 1rem;
           border-bottom: 2px solid var(--light);
       }

       .student-avatar {
           width: 100px;
           height: 100px;
           border-radius: 50%;
           object-fit: cover;
           border: 3px solid var(--primary);
           padding: 3px;
       }

       .student-info h2 {
           color: var(--dark);
           margin: 0;
       }

       .stats-grid {
           display: grid;
           grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
           gap: 1.5rem;
           margin-bottom: 2rem;
       }

       .stat-card {
           background: var(--light);
           padding: 1.5rem;
           border-radius: 1rem;
           text-align: center;
           transition: transform 0.3s ease;
       }

       .stat-card:hover {
           transform: translateY(-5px);
       }

       .stat-value {
           font-size: 2rem;
           font-weight: bold;
           color: var(--primary);
           margin-bottom: 0.5rem;
       }

       .stat-label {
           color: var(--gray);
           font-size: 0.875rem;
       }

       .seances-list {
           display: grid;
           grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
           gap: 1rem;
       }

       .seance-card {
           background: var(--light);
           padding: 1.5rem;
           border-radius: 1rem;
           border-left: 4px solid var(--primary);
       }

       .seance-header {
           display: flex;
           justify-content: space-between;
           margin-bottom: 1rem;
       }

       .seance-date {
           font-weight: bold;
           color: var(--dark);
       }

       .status-badge {
           padding: 0.25rem 0.75rem;
           border-radius: 9999px;
           font-size: 0.875rem;
           font-weight: 500;
       }

       .status-present { 
           background: #dcfce7; 
           color: #166534;
       }

       .status-absent { 
           background: #fee2e2; 
           color: #991b1b;
       }

       .seance-details {
           display: grid;
           gap: 0.5rem;
           font-size: 0.875rem;
       }

       .detail-item {
           display: flex;
           align-items: center;
           gap: 0.5rem;
       }

       .detail-item i {
           width: 20px;
           color: var(--primary);
       }

       .navbar {
           background: var(--white);
           padding: 1rem;
           margin-bottom: 2rem;
           border-radius: 0.5rem;
           box-shadow: 0 2px 4px rgba(0,0,0,0.1);
       }

       .search-form {
           display: flex;
           gap: 1rem;
       }

       .search-input {
           flex: 1;
           padding: 0.75rem;
           border: 1px solid var(--light);
           border-radius: 0.5rem;
           font-size: 0.875rem;
       }

       .search-btn {
           padding: 0.75rem 1.5rem;
           background: var(--primary);
           color: var(--white);
           border: none;
           border-radius: 0.5rem;
           cursor: pointer;
           transition: all 0.3s ease;
       }

       .search-btn:hover {
           background: #4338ca;
       }

       .validation-count {
           display: inline-flex;
           align-items: center;
           gap: 0.25rem;
           padding: 0.25rem 0.5rem;
           background: #e0e7ff;
           color: #3730a3;
           border-radius: 9999px;
           font-size: 0.75rem;
       }

       @media (max-width: 768px) {
           .student-header {
               flex-direction: column;
               text-align: center;
           }
           
           .stats-grid {
               grid-template-columns: 1fr;
           }

           .seances-list {
               grid-template-columns: 1fr;
           }
       }
   </style>
</head>
<body>
    <main class="main-content">
        <div class="top-bar">
            <h1 class="section-title">Gestion des Utilisateurs</h1>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Retour au tableau de bord
            </a>
        </div>

   <div class="container">
       <div class="navbar">
           <form class="search-form">
               <input type="text" name="search" 
                      value="<?php echo htmlspecialchars($search); ?>" 
                      class="search-input"
                      placeholder="Rechercher un étudiant...">
               <button type="submit" class="search-btn">
                   <i class="fas fa-search"></i> Rechercher
               </button>
           </form>
       </div>

       <?php foreach($etudiants as $etudiant): ?>
           <div class="student-card">
               
                   <div class="student-info">
                       <h2><?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?></h2>
                       <p>Groupes: <?php echo htmlspecialchars($etudiant['groupes']); ?></p>
                   </div>
               

               <div class="stats-grid">
                   <div class="stat-card">
                       <div class="stat-value">
                           <?php echo $etudiant['total_seances']; ?>
                       </div>
                       <div class="stat-label">Total séances</div>
                   </div>
                   <div class="stat-card">
                       <div class="stat-value" style="color: var(--success)">
                           <?php echo $etudiant['total_presences']; ?>
                       </div>
                       <div class="stat-label">Présences</div>
                   </div>
                   <div class="stat-card">
                       <div class="stat-value" style="color: var(--danger)">
                           <?php echo $etudiant['total_absences']; ?>
                       </div>
                       <div class="stat-label">Absences</div>
                   </div>
                   <div class="stat-card">
                       <div class="stat-value" style="color: var(--warning)">
                           <?php 
                               echo $etudiant['total_seances'] > 0 ? 
                                   round(($etudiant['total_presences'] / $etudiant['total_seances']) * 100) : 0;
                           ?>%
                       </div>
                       <div class="stat-label">Taux de présence</div>
                   </div>
               </div>

               <?php 
               $stmt_seances->execute([$etudiant['id'], $etudiant['id']]);
               $seances = $stmt_seances->fetchAll();
               ?>
               <h3>Détail des séances</h3>
               <div class="seances-list">
                   <?php foreach($seances as $seance): ?>
                       <div class="seance-card">
                           <div class="seance-header">
                               <span class="seance-date">
                                   <?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?>
                               </span>
                               <span class="status-badge status-<?php echo $seance['status'] ?? 'absent'; ?>">
                                   <?php echo ucfirst($seance['status'] ?? 'Absent'); ?>
                                   <?php if($seance['status'] == 'present' && $seance['validations_count'] > 0): ?>
                                       <span class="validation-count">
                                           <i class="fas fa-check"></i> 
                                           <?php echo $seance['validations_count']; ?> validation(s)
                                       </span>
                                   <?php endif; ?>
                               </span>
                           </div>
                           <div class="seance-details">
                               <div class="detail-item">
                                   <i class="fas fa-clock"></i>
                                   <?php echo substr($seance['heure_debut'], 0, 5) . ' - ' . 
                                       substr($seance['heure_fin'], 0, 5); ?>
                               </div>
                               <div class="detail-item">
                                   <i class="fas fa-door-open"></i>
                                   <?php echo htmlspecialchars($seance['nom_salle']); ?>
                                   (<?php echo $seance['type_salle'] === 'binome' ? 'Binôme' : 'Groupe'; ?>)
                               </div>
                               <div class="detail-item">
                                   <i class="fas fa-user-tie"></i>
                                   <?php echo htmlspecialchars($seance['prof_nom'] . ' ' . $seance['prof_prenom']); ?>
                               </div>
                               <div class="detail-item">
                                   <i class="fas fa-users"></i>
                                   <?php echo htmlspecialchars($seance['nom_groupe']); ?>
                               </div>
                               <?php if($seance['status'] == 'present'): ?>
                                   <div class="detail-item">
                                       <i class="fas fa-chair"></i>
                                       Place n°<?php echo $seance['numero_place']; ?>
                                   </div>
                               <?php endif; ?>
                           </div>
                       </div>
                   <?php endforeach; ?>
               </div>
           </div>
       <?php endforeach; ?>

       <?php if(empty($etudiants)): ?>
           <div class="student-card">
               <p>Aucun étudiant trouvé.</p>
           </div>
       <?php endif; ?>
   </div>
</body>
</html>