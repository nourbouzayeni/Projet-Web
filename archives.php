<?php
session_start();
require '../../config/db.php';

// Vérifier admin
if (!isset($_SESSION['client_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../connexion.php');
    exit;
}

// Récupérer les produits archivés
$stmt = $pdo->query("SELECT * FROM produits WHERE archive = TRUE ORDER BY id DESC");
$produits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Produits archivés - Admin</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700&family=Lato:wght@300;400;700&display=swap');
        
        body { font-family: 'Lato', sans-serif; background: #fdf8f5; }
        
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background: #3a2a30;
            color: white;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
        }
        .sidebar h2 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            line-height: 1.3;
        }
        .sidebar h2 small {
            display: block;
            font-size: 11px;
            color: #c8748a;
            font-family: 'Lato', sans-serif;
            margin-top: 5px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 8px;
        }
        .sidebar nav a:hover, .sidebar nav a.active {
            background: #c8748a;
            color: white;
        }
        .content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #f0e0e8;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #f0e0e8;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0e0e8;
        }
        th {
            background: #fef8f5;
            color: #8a6a74;
        }
        .btn-restaurer {
            background: #4caf50;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-supprimer {
            background: #e53935;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn {
            background:#8a6a74;
            color:white;
            padding:8px 15px;
            border-radius:8px;
            text-decoration:none;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <h2>Floraison Éternelle<br><small>Fleurs fraîches</small></h2>
        <nav>
            <a href="../dashboard.php">📊 Tableau de bord</a>
            <a href="index.php">🌸 Gestion des produits</a>
            <a href="archives.php" class="active">📦 Produits archivés</a>
            <a href="../commandes.php">📋 Commandes clients</a>
            <a href="../../index.php">🛍️ Voir le site</a>
            <a href="../../logout.php">🚪 Se déconnecter</a>
        </nav>
    </div>
    
    <div class="content">
        <div class="header">
            <h1>📦 Produits archivés</h1>
            <a href="index.php" class="btn">← Retour</a>
        </div>
        
        <table>
            <thead>
                <tr><th>Nom</th><th>Prix</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nom']) ?></td>
                    <td><?= number_format($p['prix'], 2) ?> DT</td>
                    <td>
                        <a href="archiver.php?id=<?= $p['id'] ?>" class="btn-restaurer">🔄 Restaurer</a>
                        <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn-supprimer" onclick="return confirm('Supprimer définitivement ?')">🗑️ Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($produits) === 0): ?>
                <tr><td colspan="3" style="text-align:center; padding:50px;">Aucun produit archivé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>