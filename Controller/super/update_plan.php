<?php
require_once __DIR__ . '/../../inc/super_auth.php';
require_super_admin();
require_csrf();
header('Content-Type: application/json');

$idPlan = filter_input(INPUT_POST, 'idPlan', FILTER_VALIDATE_INT);
$nom = trim((string) ($_POST['nom'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$monthly = filter_input(INPUT_POST, 'prix_mensuel', FILTER_VALIDATE_FLOAT);
$annual = filter_input(INPUT_POST, 'prix_annuel', FILTER_VALIDATE_FLOAT);

if (!$idPlan || $nom === '' || $monthly === false || $annual === false || $monthly < 0 || $annual < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Informations de plan invalides.']);
    exit;
}

try {
    $statement = $pdo->prepare('UPDATE plans SET nom = ?, description = ?, prix_mensuel = ?, prix_annuel = ? WHERE idPlan = ?');
    $statement->execute([$nom, $description ?: null, $monthly, $annual, $idPlan]);
    echo json_encode(['success' => true, 'message' => 'Plan mis à jour.']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible de modifier le plan.']);
}
