<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
header('Content-Type: application/json');

try {
    $entrepriseId = require_current_entreprise_id();
    $cleanup = $pdo->prepare("DELETE s FROM user_sessions s
        INNER JOIN utilisateur u ON u.idUser = s.idUser
        WHERE u.idEntreprise = ? AND s.last_activity < DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $cleanup->execute([$entrepriseId]);
    $statement = $pdo->prepare("SELECT s.session_id, s.last_activity, s.ip_address, s.created_at,
        u.idUser, u.Nom_Utilisateur, u.NomComplet, u.TypeDeCompte,
        COALESCE(v.revenu_vente, 0) AS revenu_vente,
        COALESCE(r.revenu_reparation, 0) AS revenu_reparation
        FROM user_sessions s
        INNER JOIN utilisateur u ON u.idUser = s.idUser AND u.idEntreprise = ?
        LEFT JOIN (SELECT created_by, idEntreprise, SUM(totalHT) AS revenu_vente FROM vente GROUP BY created_by, idEntreprise) v ON v.created_by = u.idUser AND v.idEntreprise = u.idEntreprise
        LEFT JOIN (SELECT iduser, idEntreprise, SUM(prixTotal) AS revenu_reparation FROM reparation GROUP BY iduser, idEntreprise) r ON r.iduser = u.idUser AND r.idEntreprise = u.idEntreprise
        ORDER BY s.last_activity DESC");
    $statement->execute([$entrepriseId]);
    echo json_encode(['success' => true, 'sessions' => $statement->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible de charger les sessions']);
}