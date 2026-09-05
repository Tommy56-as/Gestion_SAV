<?php
require_once __DIR__ . '/../../inc/super_auth.php';
require_super_admin();
require_csrf();
header('Content-Type: application/json');

$idEntreprise = filter_input(INPUT_POST, 'idEntreprise', FILTER_VALIDATE_INT);
$montant = filter_input(INPUT_POST, 'montant', FILTER_VALIDATE_FLOAT);
$fournisseur = trim((string) ($_POST['fournisseur'] ?? 'Paiement manuel'));
$reference = trim((string) ($_POST['reference'] ?? ''));

if (!$idEntreprise || $montant === false || $montant <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Saisissez un montant valide.']);
    exit;
}

try {
    $subscriptionStatement = $pdo->prepare('SELECT idAbonnement FROM abonnements WHERE idEntreprise = ? ORDER BY idAbonnement DESC LIMIT 1');
    $subscriptionStatement->execute([$idEntreprise]);
    $idAbonnement = $subscriptionStatement->fetchColumn() ?: null;
    $statement = $pdo->prepare("INSERT INTO paiements (idEntreprise, idAbonnement, montant, devise, reference, statut, fournisseur, paid_at) VALUES (?, ?, ?, 'XAF', ?, 'paid', ?, NOW())");
    $statement->execute([$idEntreprise, $idAbonnement, $montant, $reference ?: null, $fournisseur ?: 'Paiement manuel']);
    echo json_encode(['success' => true, 'message' => 'Paiement enregistré.']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible d’enregistrer le paiement.']);
}
