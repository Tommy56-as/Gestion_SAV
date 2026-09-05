<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
header('Content-Type: application/json');

try {
    $entrepriseId = require_current_entreprise_id();
    // Les libellés produit et fournisseur sont construits depuis leurs tables liées.
    // Cela évite de dupliquer la désignation dans approvisionnement.
    $stmt = $pdo->query("SELECT a.idApp, a.idproduit, a.quantite_stock, a.quantite_app, a.prix_total, a.idfour, a.statut, a.date_app,
        CONCAT(p.designation, ' - ', p.caracteristique) AS produit,
        CONCAT(f.nom, ' ', f.prenom) AS fournisseur
        FROM approvisionnement a
        INNER JOIN produit p ON p.idproduit = a.idproduit AND p.idEntreprise = a.idEntreprise
        LEFT JOIN fournisseur f ON f.idfour = a.idfour AND f.idEntreprise = a.idEntreprise
        WHERE a.idEntreprise = ?
        ORDER BY a.date_app DESC, a.idApp DESC");
    $stmt->execute([$entrepriseId]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des commandes']);
}
