<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
header('Content-Type: application/json');
// blacage et deblocage d'un utilisateur
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idUser'])) {
    $idUser = $_POST['idUser'];
    $new_status = $_POST['Statut']; // attendu: 0 (actif) ou 1 (bloqué)
    
    try {
        $stmt = $pdo->prepare("UPDATE utilisateur SET Statut = ? WHERE idUser = ?");
        $stmt->execute([$new_status, $idUser]);
        
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}
?>