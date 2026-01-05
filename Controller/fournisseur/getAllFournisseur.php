<?php
    
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
// affihage de tous les fournisseurs
try {
    $stmt = $pdo->query("SELECT 
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
FROM fournisseur f;
");
    $fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'fournisseurs' => $fournisseurs]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
