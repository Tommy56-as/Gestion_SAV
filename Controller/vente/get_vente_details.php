<?php
require_once '../admin_auth.php';
require_once '../../inc/DataBase.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $idvente = $_GET['idvente'] ?? null;

    if (empty($idvente)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'idvente manquant']);
        exit;
    }

    try {
         $stmt = $pdo->prepare("SELECT v.*,
                           (SELECT COALESCE(SUM(d.montant), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS montant,
                           (SELECT COALESCE(SUM(d.montantReduction), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS montantReduction,
                           (SELECT COALESCE(MAX(d.prixRecu), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS prixRecu,
                           (SELECT COALESCE(MAX(d.remboursement), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS remboursement
                       FROM vente v
                       WHERE v.idvente = ?");
        $stmt->execute([$idvente]);
        $vente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vente) {
            echo json_encode(['success' => false, 'message' => 'Vente introuvable']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT d.*, COALESCE(p.designation) AS designation, COALESCE( p.caracteristique) AS caracteristique
                               FROM details_vente d
                               LEFT JOIN produit p ON d.idproduit = p.idproduit
                               WHERE d.idvente = ?");
        $stmt->execute([$idvente]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'vente' => $vente, 'details' => $details]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
