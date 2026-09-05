<?php
    
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
require_once '../../inc/Database.php';
// affihage de tous les fournisseurs
try {
    $entrepriseId = require_current_entreprise_id();
    $statusFilter = isset($_GET['actifs']) && $_GET['actifs'] === '1'
        ? ' AND f.statut = 0'
        : '';
    $stmt = $pdo->prepare("SELECT
    idfour,
    nom,
    prenom,
    telephone,
    adresse,
    (
        SELECT CONCAT(p.designation, ' - ', p.caracteristique)
        FROM produit p
        WHERE p.idproduit = f.produit_livre
    ) AS produit_livres,
    statut
FROM fournisseur f
WHERE f.idEntreprise = ? AND f.supprime = 0{$statusFilter};
");
    $stmt->execute([$entrepriseId]);
    $fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'fournisseurs' => $fournisseurs]);
} catch(PDOException $e) {
    error_log('Erreur fournisseurs: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des fournisseurs']);
}