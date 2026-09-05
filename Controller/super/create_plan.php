<?php
require_once __DIR__ . '/../../inc/super_auth.php';
require_super_admin();
require_csrf();
header('Content-Type: application/json');

$code = strtolower(trim((string) ($_POST['code'] ?? '')));
$nom = trim((string) ($_POST['nom'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$monthly = filter_input(INPUT_POST, 'prix_mensuel', FILTER_VALIDATE_FLOAT);
$annual = filter_input(INPUT_POST, 'prix_annuel', FILTER_VALIDATE_FLOAT);

if (!preg_match('/^[a-z0-9_\-]{2,40}$/', $code) || $nom === '' || mb_strlen($nom) > 100 || $monthly === false || $annual === false || $monthly < 0 || $annual < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vérifiez le code, le nom et les tarifs du plan.']);
    exit;
}

try {
    $statement = $pdo->prepare('INSERT INTO plans (code, nom, description, prix_mensuel, prix_annuel) VALUES (?, ?, ?, ?, ?)');
    $statement->execute([$code, $nom, $description ?: null, $monthly, $annual]);
    echo json_encode(['success' => true, 'message' => 'Nouveau plan créé.']);
} catch (PDOException $exception) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Ce code de plan existe déjà.']);
}
