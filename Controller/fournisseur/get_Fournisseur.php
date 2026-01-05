<?php
    
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../../inc/Database.php';

if(isset($_GET['idfour'])) {
    $idfour = intval($_GET['idfour']);

// affihage d'un fournisseurs
try {
    $stmt = $pdo->prepare("SELECT 
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
FROM fournisseur f WHERE idfour = ? ");
    $stmt->execute([$idfour]);
    $fournisseur = $stmt->fetchAll(PDO::FETCH_ASSOC);

 if($fournisseur) {
            echo json_encode(['success' => true, 'data' => $fournisseur]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Fournisseur non trouvé']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID fournisseur manquant']);
}