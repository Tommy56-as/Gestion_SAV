<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
require_admin();
require_csrf();
header('Content-Type: application/json');
// blacage et deblocage d'un utilisateur
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idUser'])) {
    $idUser = $_POST['idUser'];
    $new_status = $_POST['Statut']; // attendu: 0 (actif) ou 1 (bloqué)
    
    try {
        $userStatement = $pdo->prepare('SELECT Nom_Utilisateur FROM utilisateur WHERE idUser = ?');
        $userStatement->execute([$idUser]);
        $targetUser = $userStatement->fetchColumn() ?: 'Utilisateur #' . $idUser;
        $stmt = $pdo->prepare("UPDATE utilisateur SET Statut = ? WHERE idUser = ?");
        $stmt->execute([$new_status, $idUser]);
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