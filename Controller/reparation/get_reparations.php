<?php
require_once '../admin_auth.php';
require_permission('reparation.read');
header('Content-Type: application/json');

try {
    $statement = $pdo->query("SELECT r.idrep, r.iduser, r.idproduit, r.nomClient, r.telephone, r.email,
        r.appareil, r.diagnostic, r.solution, r.statut, r.quantite, r.prixUnitaire, r.prixTotal, r.main_oeuvre,
        r.message_envoye, r.message_envoye_at,
        COALESCE(u.NomComplet, u.Nom_Utilisateur) AS technicien,
        CONCAT(p.designation, ' - ', p.caracteristique) AS equipement
        FROM reparation r
        INNER JOIN utilisateur u ON u.idUser = r.iduser
        LEFT JOIN produit p ON p.idproduit = r.idproduit
        ORDER BY r.idrep DESC");
    $reparations = $statement->fetchAll(PDO::FETCH_ASSOC);
    $piecesStatement = $pdo->query("SELECT rp.idrep, rp.idproduit, rp.quantite, rp.prix_unitaire, rp.montant,
        CONCAT(p.designation, ' - ', p.caracteristique) AS equipement
        FROM reparation_piece rp INNER JOIN produit p ON p.idproduit = rp.idproduit
        ORDER BY rp.id");
    $piecesParReparation = [];
    foreach ($piecesStatement->fetchAll(PDO::FETCH_ASSOC) as $piece) {
        $piecesParReparation[$piece['idrep']][] = $piece;
    }
    foreach ($reparations as &$reparation) {
        $reparation['pieces'] = $piecesParReparation[$reparation['idrep']] ?? [];
    }
    echo json_encode(['success' => true, 'data' => $reparations]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des réparations']);
}
