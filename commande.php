<?php
session_start();
require 'config/db.php';

if (empty($_SESSION['panier'])) {
    header('Location: index.php');
    exit;
}

// Récupérer les infos du formulaire
$prenom      = htmlspecialchars($_POST['prenom'] ?? '');
$nom_client  = htmlspecialchars($_POST['nom'] ?? '');
$email       = htmlspecialchars($_POST['email'] ?? '');
$telephone   = htmlspecialchars($_POST['telephone'] ?? '');
$adresse     = htmlspecialchars($_POST['adresse'] ?? '');
$code_postal = htmlspecialchars($_POST['code_postal'] ?? '');
$ville       = htmlspecialchars($_POST['ville'] ?? '');
$paiement    = htmlspecialchars($_POST['paiement'] ?? 'carte');

// Calculer le total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}

// Récupérer client_id si connecté
$client_id_val = $_SESSION['client_id'] ?? null;

// Si client connecté, récupérer ses infos depuis la session
if ($client_id_val && empty($prenom)) {
    $prenom      = $_SESSION['prenom'] ?? $prenom;
    $nom_client  = $_SESSION['nom']    ?? $nom_client;
    $email       = $_SESSION['email']  ?? $email;
}

// Insérer la commande avec toutes les infos client
$stmt = $pdo->prepare("
    INSERT INTO commandes (total, client_id, prenom, nom, email, telephone, adresse, code_postal, ville, paiement)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id
");
$stmt->execute([$total, $client_id_val, $prenom, $nom_client, $email, $telephone, $adresse, $code_postal, $ville, $paiement]);
$commande_id = $stmt->fetchColumn();

// Insérer les détails
$stmt2 = $pdo->prepare("
    INSERT INTO details_commande (commande_id, produit_id, quantite, prix_unitaire)
    VALUES (?, ?, ?, ?)
");
foreach ($_SESSION['panier'] as $produit_id => $item) {
    $stmt2->execute([$commande_id, $produit_id, $item['quantite'], $item['prix']]);
}

// Sauvegarder le panier pour affichage puis vider
$panier_sauvegarde = $_SESSION['panier'];
unset($_SESSION['panier']);

// Icône paiement
$icones = ['carte' => '💳', 'paypal' => '🅿️', 'virement' => '🏦'];
$icone_paiement = $icones[$paiement] ?? '💳';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande confirmée — Floraison Éternelle</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Lato', sans-serif; background: #fdf8f5; }

        .confirm-wrap {
            max-width: 680px;
            margin: 50px auto;
            padding: 0 20px 60px;
        }

        .confirm-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            border: 1px solid #f0e0e8;
            box-shadow: 0 8px 30px rgba(200,116,138,.1);
            text-align: center;
            margin-bottom: 24px;
        }
        .confirm-icon { font-size: 56px; margin-bottom: 14px; }
        .confirm-card h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 28px;
            color: #3a2a30;
            margin-bottom: 10px;
        }
        .confirm-card p { color: #8a6a74; font-size: 15px; margin-bottom: 6px; }
        .confirm-num {
            display: inline-block;
            background: #fde8f0;
            color: #c8748a;
            padding: 6px 18px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            margin: 10px 0 16px;
        }

        /* Info client */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .info-box {
            background: white;
            border: 1px solid #f0e0e8;
            border-radius: 12px;
            padding: 20px 24px;
        }
        .info-box h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #c8748a;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .info-box p {
            font-size: 14px;
            color: #3a2a30;
            margin-bottom: 4px;
            line-height: 1.6;
        }
        .info-box p span { color: #8a6a74; font-size: 12px; }

        /* Tableau commande */
        .commande-detail {
            background: white;
            border: 1px solid #f0e0e8;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .commande-detail h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #c8748a;
            font-weight: 700;
            padding: 16px 24px;
            border-bottom: 1px solid #f0e0e8;
        }
        .commande-detail table {
            width: 100%;
            border-collapse: collapse;
        }
        .commande-detail th {
            background: #fdf0f4;
            padding: 10px 18px;
            text-align: left;
            font-size: 12px;
            color: #8a6a74;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .commande-detail td {
            padding: 12px 18px;
            border-top: 1px solid #f8eef2;
            font-size: 14px;
            color: #3a2a30;
        }
        .total-row td {
            background: #fdf0f4;
            font-weight: 700;
            color: #c8748a;
            font-size: 16px;
        }

        .btn-retour {
            display: inline-block;
            background: #c8748a;
            color: white;
            padding: 14px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: .5px;
            transition: background .2s;
        }
        .btn-retour:hover { background: #a05570; }
    </style>
</head>
<body>

<header>
    <div class="nav-container">
        <a href="index.php" class="logo">
            <svg class="logo-icon" viewBox="0 0 40 40" fill="none">
                <circle cx="20" cy="20" r="6" fill="#c8748a" opacity="0.3"/>
                <circle cx="20" cy="10" r="5" fill="#c8748a" opacity="0.7"/>
                <circle cx="20" cy="30" r="5" fill="#c8748a" opacity="0.7"/>
                <circle cx="10" cy="20" r="5" fill="#c8748a" opacity="0.7"/>
                <circle cx="30" cy="20" r="5" fill="#c8748a" opacity="0.7"/>
                <circle cx="20" cy="20" r="4" fill="#c8748a"/>
            </svg>
            <div class="logo-texte">Floraison Éternelle<span>Fleurs Fraîches</span></div>
        </a>
        <nav><ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="boutique.php">Boutique</a></li>
        </ul></nav>
        <div class="nav-actions">
            <a href="panier.php" class="btn-panier">
                🛒 Mon Panier <span class="badge">0</span>
            </a>
        </div>
    </div>
</header>

<div class="confirm-wrap">

    <!-- Succès -->
    <div class="confirm-card">
        <div class="confirm-icon">🌸</div>
        <h1>Commande confirmée !</h1>
        <div class="confirm-num">Commande n° <?= $commande_id ?></div>
        <p>Merci <strong><?= $prenom ?> <?= $nom_client ?></strong> pour votre commande !</p>
        <p>Un email de confirmation a été envoyé à <strong><?= $email ?></strong></p>
        <p>Vos fleurs seront livrées à <strong><?= $adresse ?>, <?= $code_postal ?> <?= $ville ?></strong> dans les 24h 🚚</p>
    </div>

    <!-- Infos client & paiement -->
    <div class="info-grid">
        <div class="info-box">
            <h3>📦 Livraison</h3>
            <p><?= $prenom ?> <?= $nom_client ?></p>
            <p><?= $adresse ?></p>
            <p><?= $code_postal ?> <?= $ville ?></p>
            <p><span>Tél :</span> <?= $telephone ?></p>
        </div>
        <div class="info-box">
            <h3><?= $icone_paiement ?> Paiement</h3>
            <p>Méthode : <strong><?= ucfirst($paiement) ?></strong></p>
            <p>Statut : <strong style="color:#4caf50;">✓ Confirmé</strong></p>
            <p><span>Total débité :</span> <?= number_format($total, 2) ?> DT</p>
        </div>
    </div>

    <!-- Détail commande -->
    <div class="commande-detail">
        <h3>🌺 Détail de votre commande</h3>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($panier_sauvegarde as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nom']) ?></td>
                    <td><?= $item['quantite'] ?></td>
                    <td><?= number_format($item["prix"], 2) ?> DT</td>
                    <td><?= number_format($item['prix'] * $item['quantite'], 2) ?> DT</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">Total</td>
                    <td><?= number_format($total, 2) ?> DT</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="text-align:center;">
        <a href="index.php" class="btn-retour">← Retour à la boutique</a>
    </div>

</div>

<footer>
    <div class="footer-logo">Floraison Éternelle</div>
    <p>© 2025 Floraison Éternelle</p>
</footer>

</body>
</html>