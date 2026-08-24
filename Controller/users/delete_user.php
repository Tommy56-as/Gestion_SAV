<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_once '../../inc/soft_delete.php';
require_once '../../inc/history.php';
require_admin();
require_csrf();
header('Content-Type: application/json');

$userId = filter_input(INPUT_POST, 'idUser', FILTER_VALIDATE_INT);
if (!$userId || $userId === current_user_id()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Utilisateur invalide ou session actuelle']);
    exit;
}

try {
    $statement = $pdo->prepare("SELECT Nom_Utilisateur FROM utilisateur WHERE idUser = ? AND supprime = 0");
    $statement->execute([$userId]);
    $name = $statement->fetchColumn();
    if (!$name || !soft_delete($pdo, 'user', $userId)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
        exit;
    }
    log_history($pdo, "Suppression logique de l'utilisateur {$name}");
    echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
} catch (Throwable $exception) {
    error_log('Erreur suppression utilisateur: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
}
