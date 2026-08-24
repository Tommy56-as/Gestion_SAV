<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
require_admin();
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
        error_log('Erreur utilisateur: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement de l’utilisateur']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
}
?>