<?php
require_once '../config/session_config.php';
require_once '../config/database.php';

checkUserSession('admin');

header('Content-Type: application/json');


try {
    $salle_id = $_GET['id'];

    // Récupérer les informations de la salle
    $stmt = $conn->prepare("
        SELECT s.*, GROUP_CONCAT(p.numero_place) as places
        FROM salles s
        LEFT JOIN places p ON s.id = p.id_salle
        WHERE s.id = ?
        GROUP BY s.id
    ");
    $stmt->execute([$salle_id]);
    $salle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$salle) {
        throw new Exception('Salle non trouvée');
    }

    // Récupérer les places avec leurs positions
    $stmt = $conn->prepare("
        SELECT numero_place, position_x, position_y
        FROM places
        WHERE id_salle = ?
        ORDER BY numero_place
    ");
    $stmt->execute([$salle_id]);
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disposition de la Salle</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .salle-grid {
            display: grid;
            gap: 10px;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .place {
            background: white;
            border: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 4px;
        }
        .place-groupe {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 5px;
            padding: 10px;
            background: #e9ecef;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Disposition de la Salle: <?php echo htmlspecialchars($salle['nom_salle']); ?></h1>
            <a href="classes.php" class="btn">Retour</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="info">
                <p>
                    Type: <?php echo $salle['type_salle'] === 'individuel' ? 'Places Individuelles' : 'Places Groupées'; ?><br>
                    Capacité: <?php echo $salle['capacite']; ?> places
                </p>
            </div>

            <div class="salle-grid" style="grid-template-columns: repeat(<?php echo $salle['type_salle'] === 'individuel' ? '5' : '3'; ?>, 1fr);">
                <?php if ($salle['type_salle'] === 'individuel'): ?>
                    <?php foreach ($places as $place): ?>
                        <div class="place">
                            Place <?php echo $place['numero_place']; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php for ($i = 0; $i < $salle['capacite']/4; $i++): ?>
                        <div class="place place-groupe">
                            <div class="mini-place">Place <?php echo ($i*4)+1; ?></div>
                            <div class="mini-place">Place <?php echo ($i*4)+2; ?></div>
                            <div class="mini-place">Place <?php echo ($i*4)+3; ?></div>
                            <div class="mini-place">Place <?php echo ($i*4)+4; ?></div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>