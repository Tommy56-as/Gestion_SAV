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
        $entrepriseId = require_current_entreprise_id();
         $stmt = $pdo->prepare("SELECT v.*,
                           (SELECT COALESCE(SUM(d.montant), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS montant,
                           (SELECT COALESCE(SUM(d.montantReduction), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS montantReduction,
                           (SELECT COALESCE(MAX(d.prixRecu), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS prixRecu,
                           (SELECT COALESCE(MAX(d.remboursement), 0) FROM details_vente d WHERE d.idvente = v.idvente) AS remboursement
                       FROM vente v
                       WHERE v.idEntreprise = ? AND v.idvente = ?");
        $stmt->execute([$entrepriseId, $idvente]);
        $vente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vente) {
            echo json_encode(['success' => false, 'message' => 'Vente introuvable']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT d.*, COALESCE(p.designation) AS designation, COALESCE( p.caracteristique) AS caracteristique
                               FROM details_vente d
                               LEFT JOIN produit p ON d.idproduit = p.idproduit
                               WHERE d.idvente = ? AND p.idEntreprise = ?");
        $stmt->execute([$idvente, $entrepriseId]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'vente' => $vente, 'details' => $details]);
    } catch(PDOException $e) {
        error_log('Erreur détails vente: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des détails']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}