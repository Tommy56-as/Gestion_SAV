<?php
require_once '../admin_auth.php';
require_once '../../inc/DataBase.php';
header('Content-Type: application/json');

// Récupérer les ventes
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT v.*,
                   (SELECT COALESCE(SUM(d.montant), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS prixTotal,
                   (SELECT COALESCE(MAX(d.prixRecu), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS prixRecu,
                   (SELECT COALESCE(MAX(d.remboursement), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS remboursement
            FROM vente v
            ORDER BY v.date_vente DESC
            LIMIT 100
        ");
        $stmt->execute();
        $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $ventes]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
