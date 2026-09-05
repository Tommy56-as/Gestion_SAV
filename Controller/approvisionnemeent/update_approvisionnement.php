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

// Seuls les deux états métier utilisés par l'interface sont acceptés.
$idApp = filter_input(INPUT_POST, 'idApp', FILTER_VALIDATE_INT);
$statut = $_POST['statut'] ?? '';
if (!$idApp || !in_array($statut, ['encours', 'terminee'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Commande ou statut invalide']);
    exit;
}

try {
    $entrepriseId = require_current_entreprise_id();
    // Verrouiller la commande pendant la modification pour éviter un double mouvement de stock.
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT idproduit, quantite_app, statut FROM approvisionnement WHERE idEntreprise = ? AND idApp = ? FOR UPDATE');
    $stmt->execute([$entrepriseId, $idApp]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$commande) {
        throw new RuntimeException('Commande introuvable');
    }
    // Une commande terminée est définitive : son stock ne peut pas être annulé.
    if ($commande['statut'] === 'terminee' && $statut === 'encours') {
        throw new RuntimeException('Une commande terminée ne peut plus être modifiée');
    }
    if ($commande['statut'] === $statut) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Statut inchangé']);
        exit;
    }

    // Réception : ajout au stock. Réouverture : annulation de cet ajout.
    $delta = $statut === 'terminee' ? (int) $commande['quantite_app'] : -(int) $commande['quantite_app'];
    $stockStmt = $pdo->prepare('UPDATE produit SET quantite = quantite + ? WHERE idEntreprise = ? AND idproduit = ?');
    $stockStmt->execute([$delta, $entrepriseId, $commande['idproduit']]);
    $updateStmt = $pdo->prepare('UPDATE approvisionnement SET statut = ? WHERE idEntreprise = ? AND idApp = ?');
    $updateStmt->execute([$statut, $entrepriseId, $idApp]);
    // Le stock et le statut doivent être validés ensemble.
    $pdo->commit();
    log_history($pdo, "Commande d'approvisionnement {$idApp} : {$statut}");
    echo json_encode(['success' => true, 'message' => 'Commande mise à jour']);
} catch (Throwable $e) {
    // Annuler les deux opérations si une seule étape échoue.
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code($e->getMessage() === 'Une commande terminée ne peut plus être modifiée' ? 409 : 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage() === 'Commande introuvable' || $e->getMessage() === 'Une commande terminée ne peut plus être modifiée' ? $e->getMessage() : 'Erreur lors de la mise à jour']);
}