<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
require_csrf();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$userId = filter_input(INPUT_POST, 'idUser', FILTER_VALIDATE_INT);
$permissions = json_decode($_POST['permissions'] ?? '[]', true);
$allowedPermissions = ['vente.read', 'vente.create', 'vente.update', 'vente.delete', 'reparation.read', 'reparation.create', 'reparation.update', 'reparation.delete', 'produit.read', 'produit.create', 'produit.update', 'user.read', 'user.create', 'user.update'];

if (!$userId || !is_array($permissions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    $entrepriseId = require_current_entreprise_id();
    $check = $pdo->prepare("SELECT idUser FROM utilisateur
        WHERE idUser = ? AND idEntreprise = ? AND TypeDeCompte <> 'Administrateur' AND supprime = 0");
    $check->execute([$userId, $entrepriseId]);
    if (!$check->fetch()) throw new RuntimeException('Utilisateur introuvable');

    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM user_permissions WHERE idUser = ?')->execute([$userId]);
    $insert = $pdo->prepare('INSERT INTO user_permissions (idUser, permission, allowed) VALUES (?, ?, 1)');
    foreach (array_intersect($permissions, $allowedPermissions) as $permission) {
        $insert->execute([$userId, $permission]);
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Autorisations enregistrées']);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible d’enregistrer les autorisations']);
}
