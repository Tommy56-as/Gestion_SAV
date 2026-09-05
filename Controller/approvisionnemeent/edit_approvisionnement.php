<?php
require_once '../admin_auth.php';
require_csrf();
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$idApp = filter_input(INPUT_POST, 'idApp', FILTER_VALIDATE_INT);
$idProduit = filter_input(INPUT_POST, 'idproduit', FILTER_VALIDATE_INT);
$quantite = filter_input(INPUT_POST, 'quantite_app', FILTER_VALIDATE_INT);
$idFour = filter_input(INPUT_POST, 'idfour', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
$prixTotal = filter_input(INPUT_POST, 'prix_total', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
$dateApp = $_POST['date_app'] ?? '';

if (!$idApp || !$idProduit || !$quantite || $quantite < 1 || !$dateApp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Les informations de la commande sont invalides']);
    exit;
}

try {
    $entrepriseId = require_current_entreprise_id();
    // Le verrou empêche l'édition pendant une réception concurrente.
    $pdo->beginTransaction();
    $commandeStmt = $pdo->prepare('SELECT statut FROM approvisionnement WHERE idEntreprise = ? AND idApp = ? FOR UPDATE');
    $commandeStmt->execute([$entrepriseId, $idApp]);
    $commande = $commandeStmt->fetch(PDO::FETCH_ASSOC);
    if (!$commande) {
        throw new RuntimeException('Commande introuvable');
    }
    if ($commande['statut'] === 'terminee') {
        throw new RuntimeException('Une commande terminée ne peut plus être modifiée');
    }

    $produitStmt = $pdo->prepare('SELECT quantite FROM produit WHERE idEntreprise = ? AND idproduit = ?');
    $produitStmt->execute([$entrepriseId, $idProduit]);
    $produit = $produitStmt->fetch(PDO::FETCH_ASSOC);
    if (!$produit) {
        throw new RuntimeException('Produit introuvable');
    }

    $updateStmt = $pdo->prepare('UPDATE approvisionnement
        SET idproduit = ?, quantite_stock = ?, quantite_app = ?, prix_total = ?, idfour = ?, date_app = ?
        WHERE idEntreprise = ? AND idApp = ?');
    $updateStmt->execute([$idProduit, (int) $produit['quantite'], $quantite, $prixTotal, $idFour ?: null, $dateApp, $entrepriseId, $idApp]);
    $pdo->commit();
    log_history($pdo, "Modification de la commande d'approvisionnement {$idApp}");
    echo json_encode(['success' => true, 'message' => 'Commande modifiée']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code($e->getMessage() === 'Une commande terminée ne peut plus être modifiée' ? 409 : 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage() === 'Commande introuvable' || $e->getMessage() === 'Produit introuvable' || $e->getMessage() === 'Une commande terminée ne peut plus être modifiée' ? $e->getMessage() : 'Erreur lors de la modification']);
}