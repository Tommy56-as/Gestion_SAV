<?php
require_once __DIR__ . '/../../inc/super_auth.php';
require_super_admin();
require_csrf();
header('Content-Type: application/json');

$idEntreprise = filter_input(INPUT_POST, 'idEntreprise', FILTER_VALIDATE_INT);
$idPlan = filter_input(INPUT_POST, 'idPlan', FILTER_VALIDATE_INT);
$statut = trim((string) ($_POST['statut'] ?? ''));
$periode = trim((string) ($_POST['periode'] ?? 'mensuelle'));
$dateFin = trim((string) ($_POST['date_fin'] ?? ''));
$allowedStatuses = ['trialing', 'active', 'past_due', 'cancelled', 'expired', 'suspended'];

if (!$idEntreprise || !$idPlan || !in_array($statut, $allowedStatuses, true) || !in_array($periode, ['mensuelle', 'annuelle'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides.']);
    exit;
}
$date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateFin);
if (!$date || $date->format('Y-m-d') !== $dateFin) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La date d’expiration est invalide.']);
    exit;
}

try {
    $check = $pdo->prepare('SELECT 1 FROM entreprises WHERE idEntreprise = ?');
    $check->execute([$idEntreprise]);
    $planCheck = $pdo->prepare('SELECT 1 FROM plans WHERE idPlan = ? AND actif = 1');
    $planCheck->execute([$idPlan]);
    if (!$check->fetchColumn() || !$planCheck->fetchColumn()) {
        throw new RuntimeException('Entreprise ou plan introuvable.');
    }

    $statement = $pdo->prepare(
        'INSERT INTO abonnements (idEntreprise, idPlan, statut, periode, date_debut, date_fin)
         VALUES (?, ?, ?, ?, CURDATE(), ?)' 
    );
    // Une seule ligne active n'est pas imposée par le schéma historique : on clôt les anciennes avant insertion.
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE abonnements SET statut = 'cancelled' WHERE idEntreprise = ? AND statut IN ('trialing', 'active', 'past_due')")->execute([$idEntreprise]);
    $statement->execute([$idEntreprise, $idPlan, $statut, $periode, $dateFin]);
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Abonnement mis à jour.']);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $exception instanceof RuntimeException ? $exception->getMessage() : 'Impossible de modifier l’abonnement.']);
}
