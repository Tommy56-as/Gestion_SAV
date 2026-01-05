<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
header('Content-Type: application/json');

// Récupération d'un utilisateur spécifique
if(isset($_GET['idUser'])) {
    $idUser = intval($_GET['idUser']); 
    try {
        $stmt = $pdo->prepare("SELECT *
                              FROM utilisateur 
                              WHERE idUser = ?");
        $stmt->execute([$idUser]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user) {
            echo json_encode(['success' => true, 'data' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
}
?>