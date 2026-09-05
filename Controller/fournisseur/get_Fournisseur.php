<?php
    
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
require_once '../../inc/Database.php';

if(isset($_GET['idfour'])) {
    $idfour = intval($_GET['idfour']);

// affihage d'un fournisseurs
try {
    $entrepriseId = require_current_entreprise_id();
    $stmt = $pdo->prepare("SELECT 
    f.idfour,
    nom,
    prenom,
    telephone,
    adresse,
    f.produit_livre AS produitLivre,
    p.designation,
    p.caracteristique,
    CONCAT(p.designation, ' - ', p.caracteristique) AS produit_livres,
    statut
FROM fournisseur f
LEFT JOIN produit p ON p.idproduit = f.produit_livre
WHERE f.idEntreprise = ? AND f.idfour = ? AND f.supprime = 0 ");
    $stmt->execute([$entrepriseId, $idfour]);
    $fournisseur = $stmt->fetchAll(PDO::FETCH_ASSOC);

 if($fournisseur) {
            echo json_encode(['success' => true, 'data' => $fournisseur]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Fournisseur non trouvé']);
        }
    } catch(PDOException $e) {
        error_log('Erreur fournisseur: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement du fournisseur']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID fournisseur manquant']);
}