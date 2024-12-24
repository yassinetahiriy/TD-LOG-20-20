<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Types de Classe</title>
    <style>
        .classe-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .classe-type {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .grille-places {
            display: grid;
            gap: 10px;
            margin-top: 15px;
        }
        .place {
            background: white;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
        }
        .place-individuelle {
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .place-groupe {
            height: 80px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 5px;
            padding: 5px;
        }
        .etudiant {
            background: #e3f2fd;
            border-radius: 4px;
            padding: 5px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Configuration des Types de Classe</h1>
            <a href="dashboard.php" class="btn">Retour</a>
        </div>

        <div class="classe-container">
            <div class="classe-type">
                <h2>Type 1: Places Individuelles</h2>
                <p>30 places individuelles</p>
                <div class="grille-places" style="grid-template-columns: repeat(5, 1fr);">
                    <?php for ($i = 1; $i <= 30; $i++): ?>
                        <div class="place place-individuelle">
                            Place <?php echo $i; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="classe-type">
                <h2>Type 2: Places Groupées</h2>
                <p>30 places (tables de 4)</p>
                <div class="grille-places" style="grid-template-columns: repeat(3, 1fr);">
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <div class="place place-groupe">
                            <div class="etudiant">Place <?php echo ($i*4)-3; ?></div>
                            <div class="etudiant">Place <?php echo ($i*4)-2; ?></div>
                            <div class="etudiant">Place <?php echo ($i*4)-1; ?></div>
                            <div class="etudiant">Place <?php echo ($i*4); ?></div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>