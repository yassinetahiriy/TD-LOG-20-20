<?php
session_start();
require_once '../config/database.php';

// Vérification de l'authentification

// Récupération des données nécessaires pour le formulaire
try {
    // Récupérer les professeurs
    $stmt = $conn->prepare("SELECT id, nom, prenom FROM users WHERE user_type = 'professeur'");
    $stmt->execute();
    $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les salles
    $stmt = $conn->prepare("SELECT id, nom_salle, type_salle, capacite FROM salles");
    $stmt->execute();
    $salles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les groupes TD
    $stmt = $conn->prepare("SELECT id, nom_groupe, annee_scolaire FROM groupes_td");
    $stmt->execute();
    $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les séances existantes avec leurs détails
    $stmt = $conn->prepare("
        SELECT s.*, 
               p.nom as prof_nom, p.prenom as prof_prenom,
               sl.nom_salle, g.nom_groupe
        FROM seances s
        JOIN users p ON s.id_professeur = p.id
        JOIN salles sl ON s.id_salle = sl.id
        JOIN groupes_td g ON s.id_groupe_td = g.id
        ORDER BY s.date_seance DESC, s.heure_debut DESC
    ");
    $stmt->execute();
    $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Séances</title>
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

        .nav-link:hover, .nav-link.active {
            background-color: var(--primary);
            color: var(--white);
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
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

        .back-button {
            padding: 0.5rem 1rem;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: var(--primary-dark);
        }

        .form-container {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #dc2626;
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

        .table tr:hover {
            background-color: #f8fafc;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-programmee {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-en-cours {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-terminee {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
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
            <a href="seances.php" class="nav-link active">
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <h1 class="section-title">Gestion des Séances</h1>
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Retour au tableau de bord</span>
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulaire d'ajout de séance -->
        <div class="form-container">
            <h2 class="section-title">Ajouter une nouvelle séance</h2>
            <form id="seanceForm" action="ajax/add_seance.php" method="POST">
                <div class="form-group">
                    <label for="date_seance">Date de la séance</label>
                    <input type="date" id="date_seance" name="date_seance" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="heure_debut">Heure de début</label>
                    <input type="time" id="heure_debut" name="heure_debut" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="heure_fin">Heure de fin</label>
                    <input type="time" id="heure_fin" name="heure_fin" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="id_professeur">Professeur</label>
                    <select id="id_professeur" name="id_professeur" class="form-control" required>
                        <option value="">Sélectionner un professeur</option>
                        <?php foreach ($professeurs as $prof): ?>
                            <option value="<?php echo $prof['id']; ?>">
                                <?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['prenom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_salle">Salle</label>
                    <select id="id_salle" name="id_salle" class="form-control" required>
                        <option value="">Sélectionner une salle</option>
                        <?php foreach ($salles as $salle): ?>
                            <option value="<?php echo $salle['id']; ?>">
                                <?php echo htmlspecialchars($salle['nom_salle'] . ' (' . $salle['type_salle'] . ' - ' . $salle['capacite'] . ' places)'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_groupe_td">Groupe TD</label>
                    <select id="id_groupe_td" name="id_groupe_td" class="form-control" required>
                        <option value="">Sélectionner un groupe</option>
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?php echo $groupe['id']; ?>">
                                <?php echo htmlspecialchars($groupe['nom_groupe'] . ' (' . $groupe['annee_scolaire'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter la séance
                </button>
            </form>
        </div>

        <!-- Liste des séances -->
        <div class="table-container">
            <h2 class="section-title">Séances programmées</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Horaires</th>
                        <th>Professeur</th>
                        <th>Salle</th>
                        <th>Groupe</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seances as $seance): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($seance['date_seance'])); ?></td>
                            <td><?php echo substr($seance['heure_debut'], 0, 5) . ' - ' . substr($seance['heure_fin'], 0, 5); ?></td>
                            <td><?php echo htmlspecialchars($seance['prof_nom'] . ' ' . $seance['prof_prenom']); ?></td>
                            <td><?php echo htmlspecialchars($seance['nom_salle']); ?></td>
                            <td><?php echo htmlspecialchars($seance['nom_groupe']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($seance['statut']); ?>">
                                    <?php echo htmlspecialchars($seance['statut']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <?php if ($seance['statut'] === 'programmee'): ?>
                                    <button onclick="modifierSeance(<?php echo $seance['id']; ?>)" class="btn btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="supprimerSeance(<?php echo $seance['id']; ?>)" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
    function supprimerSeance(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette séance ?')) {
            fetch('ajax/delete_seance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la suppression');
            });
        }
    }

    function modifierSeance(id) {
        window.location.href = 'modifier_seance.php?id=' + id;
    }

    // Validation du formulaire
    document.getElementById('seanceForm').onsubmit = function(e) {
        e.preventDefault();
        
        const heureDebut = document.getElementById('heure_debut').value;
        const heureFin = document.getElementById('heure_fin').value;
        
        if (heureDebut >= heureFin) {
            alert("L'heure de fin doit être postérieure à l'heure de début");
            return false;
        }

        const formData = new FormData(this);

        fetch('ajax/add_seance.php', {
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
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de l\'ajout de la séance');
        });
    };

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

    // Style supplémentaire pour le menu toggle
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
