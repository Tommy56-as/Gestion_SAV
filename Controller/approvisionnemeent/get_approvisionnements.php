<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
require_admin();

try {
    $entrepriseId = require_current_entreprise_id();
    
    // CORRECTION : On utilise prepare() au lieu de query()
    $stmt = $pdo->prepare("SELECT a.idApp, a.idproduit, a.quantite_stock, a.quantite_app, a.prix_total, a.idfour, a.statut, a.date_app,
        CONCAT(p.designation, ' - ', p.caracteristique) AS produit,
        CONCAT(f.nom, ' ', f.prenom) AS fournisseur
        FROM approvisionnement a
        INNER JOIN produit p ON p.idproduit = a.idproduit AND p.idEntreprise = a.idEntreprise
        LEFT JOIN fournisseur f ON f.idfour = a.idfour AND f.idEntreprise = a.idEntreprise
        WHERE a.idEntreprise = ?
        ORDER BY a.date_app DESC, a.idApp DESC");
        
    // La variable $entrepriseId est injectée ici en toute sécurité
    $stmt->execute([$entrepriseId]);
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    http_response_code(500);
    // Optionnel pour le développement : vous pouvez afficher le vrai message SQL en décommentant la ligne ci-dessous
    //echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des commandes']);
}