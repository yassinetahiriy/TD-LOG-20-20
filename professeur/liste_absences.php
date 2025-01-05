<?php
require_once '../config/session_config.php';
require_once '../config/database.php';
checkUserSession('professeur');

try {
   // Récupérer toutes les séances du professeur
   $stmt = $conn->prepare("
       SELECT 
           s.id,
           s.date_seance,
           s.heure_debut,
           s.heure_fin,
           s.statut,
           sl.nom_salle,
           sl.type_salle,
           g.nom_groupe,
           (
               SELECT COUNT(*) 
               FROM groupes_td_etudiants gte2 
               WHERE gte2.id_groupe_td = g.id
           ) as total_etudiants,
           (
               SELECT COUNT(*) 
               FROM presences p2 
               WHERE p2.id_seance = s.id AND p2.status = 'absent'
           ) as nombre_absents
       FROM seances s
       JOIN salles sl ON s.id_salle = sl.id
       JOIN groupes_td g ON s.id_groupe_td = g.id
       WHERE s.id_professeur = ?
       AND s.statut = 'terminee'
       ORDER BY s.date_seance DESC, s.heure_debut DESC
   ");
   $stmt->execute([$_SESSION['user_id']]);
   $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

   // Préparer la requête pour les étudiants absents par séance
   $stmt_absents = $conn->prepare("
       SELECT 
           u.nom,
           u.prenom,
           u.email
       FROM users u
       JOIN groupes_td_etudiants gte ON u.id = gte.id_etudiant
       JOIN seances s ON gte.id_groupe_td = s.id_groupe_td
       LEFT JOIN presences p ON s.id = p.id_seance AND u.id = p.id_etudiant
       WHERE s.id = ? 
       AND (p.status = 'absent' OR p.status IS NULL)
       ORDER BY u.nom, u.prenom
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
   <title>Liste des Absences</title>
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
   <style>
       :root {
           --primary: #4f46e5;
           --white: #ffffff;
           --light: #f1f5f9;
           --dark: #1e293b;
           --danger: #ef4444;
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

       .top-bar {
           background: var(--white);
           padding: 1.5rem;
           border-radius: 1rem;
           margin-bottom: 2rem;
           box-shadow: 0 2px 4px rgba(0,0,0,0.05);
       }

       .seance-card {
           background: var(--white);
           border-radius: 1rem;
           padding: 1.5rem;
           margin-bottom: 1.5rem;
           box-shadow: 0 2px 4px rgba(0,0,0,0.05);
       }

       .seance-header {
           display: flex;
           justify-content: space-between;
           align-items: center;
           margin-bottom: 1rem;
           padding-bottom: 1rem;
           border-bottom: 1px solid var(--light);
       }

       .absent-list {
           background: var(--light);
           border-radius: 0.5rem;
           padding: 1rem;
           margin-top: 1rem;
       }

       .absent-item {
           background: var(--white);
           padding: 0.75rem;
           border-radius: 0.5rem;
           margin-bottom: 0.5rem;
           display: flex;
           align-items: center;
           gap: 1rem;
       }

       .badge {
           padding: 0.5rem 1rem;
           border-radius: 9999px;
           font-size: 0.875rem;
           font-weight: 500;
       }

       .badge-danger {
           background-color: #fee2e2;
           color: #991b1b;
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

       .nav-link.active, .nav-link:hover {
           background-color: var(--primary);
           color: var(--white);
       }

       .nav-link i {
           width: 20px;
           margin-right: 0.75rem;
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
   <aside class="sidebar">
       <div class="sidebar-header">
           <a href="dashboard.php" class="nav-link">
               <i class="fas fa-chalkboard-teacher"></i>
               <span>Espace Professeur</span>
           </a>
       </div>
       <nav>
           <a href="dashboard.php" class="nav-link">
               <i class="fas fa-home"></i>
               <span>Tableau de bord</span>
           </a>
           <a href="liste_absences.php" class="nav-link active">
               <i class="fas fa-user-times"></i>
               <span>Liste des Absences</span>
           </a>
       </nav>
   </aside>

   <main class="main-content">
       <div class="top-bar">
           <h1>Liste des Absences par Séance</h1>
       </div>

       <?php foreach($seances as $seance): ?>
           <div class="seance-card">
               <div class="seance-header">
                   <div>
                       <h2><?php echo htmlspecialchars($seance['nom_groupe']); ?></h2>
                       <p>
                           <i class="fas fa-calendar"></i>
                           <?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?>
                           <i class="fas fa-clock"></i>
                           <?php echo substr($seance['heure_debut'], 0, 5) . ' - ' . substr($seance['heure_fin'], 0, 5); ?>
                       </p>
                       <p>
                           <i class="fas fa-door-open"></i>
                           <?php echo htmlspecialchars($seance['nom_salle']); ?>
                       </p>
                   </div>
                   <div class="badge badge-danger">
                       <?php echo $seance['nombre_absents']; ?> absent(s) / <?php echo $seance['total_etudiants']; ?> étudiants
                   </div>
               </div>

               <?php
               $stmt_absents->execute([$seance['id']]);
               $absents = $stmt_absents->fetchAll();
               ?>

               <div class="absent-list">
                   <h3>Liste des absents :</h3>
                   <?php if(empty($absents)): ?>
                       <p>Aucun absent pour cette séance.</p>
                   <?php else: ?>
                       <?php foreach($absents as $absent): ?>
                           <div class="absent-item">
                               <i class="fas fa-user"></i>
                               <div>
                                   <strong><?php echo htmlspecialchars($absent['nom'] . ' ' . $absent['prenom']); ?></strong>
                                   <br>
                                   <small><?php echo htmlspecialchars($absent['email']); ?></small>
                               </div>
                           </div>
                       <?php endforeach; ?>
                   <?php endif; ?>
               </div>
           </div>
       <?php endforeach; ?>

       <?php if(empty($seances)): ?>
           <div class="seance-card">
               <p>Aucune séance terminée n'a été trouvée.</p>
           </div>
       <?php endif; ?>
   </main>
</body>
</html>