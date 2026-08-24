<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_once '../../inc/soft_delete.php';
require_once '../../inc/history.php';
require_admin();
require_csrf();
header('Content-Type: application/json');

$fournisseurId = filter_input(INPUT_POST, 'idfour', FILTER_VALIDATE_INT);
if (!$fournisseurId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Fournisseur invalide']);
    exit;
}

try {
    $statement = $pdo->prepare("SELECT nom, prenom FROM fournisseur WHERE idfour = ? AND supprime = 0");
    $statement->execute([$fournisseurId]);
    $supplier = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$supplier || !soft_delete($pdo, 'fournisseur', $fournisseurId)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Fournisseur introuvable']);
        exit;
    }
    log_history($pdo, "Suppression logique du fournisseur {$supplier['nom']} {$supplier['prenom']}");
    echo json_encode(['success' => true, 'message' => 'Fournisseur supprimé']);
} catch (Throwable $exception) {
    error_log('Erreur suppression fournisseur: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
}