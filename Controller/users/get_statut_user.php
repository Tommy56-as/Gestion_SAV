<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
require_admin();
require_csrf();
header('Content-Type: application/json');
// blacage et deblocage d'un utilisateur
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idUser'])) {
    $entrepriseId = require_current_entreprise_id();
    $idUser = filter_input(INPUT_POST, 'idUser', FILTER_VALIDATE_INT);
    $new_status = filter_input(INPUT_POST, 'Statut', FILTER_VALIDATE_INT);
    if (!$idUser || !in_array($new_status, [0, 1], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
        exit;
    }
    if ($idUser === current_user_id()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas bloquer votre propre compte']);
        exit;
    }
    
    try {
        $userStatement = $pdo->prepare('SELECT Nom_Utilisateur FROM utilisateur WHERE idEntreprise = ? AND idUser = ?');
        $userStatement->execute([$entrepriseId, $idUser]);
        $targetUser = $userStatement->fetchColumn() ?: 'Utilisateur #' . $idUser;
        $stmt = $pdo->prepare("UPDATE utilisateur SET Statut = ? WHERE idEntreprise = ? AND idUser = ?");
        $stmt->execute([$new_status, $entrepriseId, $idUser]);
        $action = ((int) $new_status === 1) ? 'Blocage' : 'Déblocage';
        log_history($pdo, "{$action} de l'utilisateur {$targetUser}");
        
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès']);
    } catch(PDOException $e) {
        error_log('Erreur statut utilisateur: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}
?>