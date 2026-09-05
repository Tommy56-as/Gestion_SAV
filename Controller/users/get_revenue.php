<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
header('Content-Type: application/json');

try {
    $entrepriseId = require_current_entreprise_id();
    $salesStatement = $pdo->prepare("SELECT u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte, COALESCE(SUM(v.totalHT), 0) AS revenu
        FROM utilisateur u LEFT JOIN vente v ON v.created_by = u.idUser AND v.idEntreprise = u.idEntreprise
        WHERE u.idEntreprise = ? AND u.TypeDeCompte = 'Caissier'  
        AND  DATE(v.date_vente) = CURDATE()   
        GROUP BY u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte
        ORDER BY revenu DESC");
    $salesStatement->execute([$entrepriseId]);
    $sales = $salesStatement->fetchAll(PDO::FETCH_ASSOC);
        
    $repairsStatement = $pdo->prepare("SELECT u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte, COALESCE(SUM(r.prixTotal), 0) AS revenu
                        FROM utilisateur u 
                        LEFT JOIN reparation r ON r.iduser = u.idUser AND r.idEntreprise = u.idEntreprise
                        WHERE u.idEntreprise = ? AND u.TypeDeCompte = 'Technicien' 
                        AND DATE(r.updated_at) = CURDATE()
                        AND r.statut = 'terminee'
                        GROUP BY u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte
                        ORDER BY revenu DESC");
    $repairsStatement->execute([$entrepriseId]);
    $repairs = $repairsStatement->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'sales' => $sales, 'repairs' => $repairs]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible de charger les revenus']);
}