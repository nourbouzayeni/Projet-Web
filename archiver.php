<?php
session_start();
require '../../config/db.php';

// Vérifier admin
if (!isset($_SESSION['client_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../connexion.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Récupérer l'état actuel
$stmt = $pdo->prepare("SELECT archive FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch();

if ($produit) {
    // PostgreSQL : passer 'true' / 'false' en string explicite
    $est_archive    = ($produit['archive'] === true || $produit['archive'] === 't' || $produit['archive'] === '1');
    $nouvel_etat    = $est_archive ? 'false' : 'true';
    $message        = $est_archive ? 'Produit restauré' : 'Produit archivé';

    $stmt = $pdo->prepare("UPDATE produits SET archive = ? WHERE id = ?");
    $stmt->execute([$nouvel_etat, $id]);
} else {
    $message = 'Produit introuvable';
}

header("Location: index.php?message=" . urlencode($message));
exit;
?>