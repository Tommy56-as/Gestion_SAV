<?php
//afficher un produit spécifique
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
header('Content-Type: application/json');
if(isset($_GET['idproduit'])) {
    $idProduit = intval($_GET['idproduit']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM produit WHERE idproduit = ?");
        $stmt->execute([$idProduit]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($produit) {
            echo json_encode(['success' => true, 'data' => $produit]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
        }
    } catch(PDOException $e) {
        error_log('Erreur produit: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement du produit']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID produit manquant']);
}