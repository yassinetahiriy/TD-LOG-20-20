<?php
require_once '../config/session_config.php';
require_once '../config/database.php';
checkUserSession('admin');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administrateur</title>
    <style>
        /* Styles de base */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .menu-item {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .menu-item h3 {
            margin: 0 0 10px 0;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>Tableau de bord Administrateur</h1>
            <div>
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></span>
                <a href="../logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </div>

        <div class="menu">
            <div class="menu-item">
                <h3>Gestion des Séances</h3>
                <p>Créer et gérer les séances de cours</p>
                <a href="seances.php" class="btn">Gérer</a>
            </div>
            
            <div class="menu-item">
                <h3>Gestion des Utilisateurs</h3>
                <p>Gérer les comptes étudiants et professeurs</p>
                <a href="users.php" class="btn">Gérer</a>
            </div>
            
            <div class="menu-item">
                <h3>Gestion des Classes</h3>
                <p>Configurer les salles et leur disposition</p>
                <a href="classes.php" class="btn">Gérer</a>
            </div>
            
            <div class="menu-item">
                <h3>Rapports</h3>
                <p>Voir les statistiques de présence</p>
                <a href="reports.php" class="btn">Voir</a>
            </div>

            <div class="menu-item">
                <h3>Creation de groupe TD</h3>
                <p>Gestion des groupes TD</p>
                <a href="creer_groupe.php" class="btn">Gérer</a>
            </div>
        </div>
    </div>
</body>
</html>