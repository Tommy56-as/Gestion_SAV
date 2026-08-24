<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
header('Content-Type: application/json');

try {
    $sales = $pdo->query("SELECT u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte, COALESCE(SUM(v.totalHT), 0) AS revenu
        FROM utilisateur u LEFT JOIN vente v ON v.created_by = u.idUser
        WHERE u.TypeDeCompte = 'Caissier'  
        AND  DATE(v.date_vente) = CURDATE()   
        GROUP BY u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte
        ORDER BY revenu DESC")->fetchAll(PDO::FETCH_ASSOC);
        
    $repairs = $pdo->query("SELECT u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte, COALESCE(SUM(r.prixTotal), 0) AS revenu
                        FROM utilisateur u 
                        LEFT JOIN reparation r ON r.iduser = u.idUser
                        WHERE u.TypeDeCompte = 'Technicien' 
                        AND DATE(r.updated_at) = CURDATE()
                        AND r.statut = 'terminee'
                        GROUP BY u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte
                        ORDER BY revenu DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'sales' => $sales, 'repairs' => $repairs]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible de charger les revenus']);
}