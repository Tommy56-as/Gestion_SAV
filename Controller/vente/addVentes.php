<?php
require_once '../admin_auth.php';
require_once '../../inc/DataBase.php';
require_once '../../inc/history.php';
require_permission('vente.create');
require_csrf();
header('Content-Type: application/json');

// ajout d'une vente
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs requis
    $client = $_POST['client'] ?? null;
    $date_vente = $_POST['date_vente'] ?? null;
    $telephone = $_POST['telephone'] ?? null;
    $totalHT = $_POST['totalHT'] ?? 0;
    $tauxReduction = $_POST['tauxReduction'] ?? 0;
    $montantReduction = $_POST['montantReduction'] ?? 0;
    $totalApresReduction = $_POST['totalApresReduction'] ?? 0;
    $prixRecu = $_POST['prixRecu'] ?? 0;
    $remboursement = $_POST['remboursement'] ?? 0;
    $moyenPaiement = $_POST['moyenPaiement'] ?? 'especes';
    $produits = isset($_POST['produits']) ? json_decode($_POST['produits'], true) : [];

    // Validation de base
    if(empty($client)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le nom du client est requis']);
        exit;
    }

    if(empty($date_vente)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La date de vente est requise']);
        exit;
    }

    if(empty($produits)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Au moins un produit doit être ajouté']);
        exit;
    }

    if(empty($moyenPaiement)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le moyen de paiement est requis']);
        exit;
    }

    try {
        // Insérer la vente (entête)
        $stmt = $pdo->prepare("INSERT INTO vente (created_by, client, telephone, date_vente, totalHT) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([current_user_id(), $client, $telephone, $date_vente, $totalHT]);
        $idvente = $pdo->lastInsertId();

        // Insérer les détails des produits dans details_vente
        $stmt = $pdo->prepare("INSERT INTO details_vente (idvente, idproduit, quantite, prixUnitaire, montant, prixRecu, remboursement, tauxReduction, 
                                    montantReduction, totalApresReduction, moyenPaiement,finGarantie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Préparer la requête pour réduire le stock
        $updateStockStmt = $pdo->prepare("UPDATE produit SET quantite = quantite - ? WHERE idproduit = ?");
        
        foreach($produits as $produit) {
            $montant = ($produit['prixUnitaire'] ?? 0) * ($produit['quantite'] ?? 0);
            $idproduit = $produit['idproduit'] ?? null;
            $quantite = $produit['quantite'] ?? 0;
            
            // Insérer le détail de la vente
            $stmt->execute([
                $idvente,
                $idproduit,
                $quantite,
                $produit['prixUnitaire'] ?? 0,
                $montant,
                $prixRecu,
                $remboursement,
                $tauxReduction,
                $montantReduction,
                $totalApresReduction,
                $moyenPaiement,
                $produit['finGarantie'] ?? null
            ]);
            
            // Réduire la quantité en stock dans la table produit
            if ($idproduit && $quantite > 0) {
                $updateStockStmt->execute([$quantite, $idproduit]);
            }
        }

        // Log historique
        $nbProduits = count($produits);
        $paiementInfo = ucfirst($moyenPaiement === 'om' ? 'Orange Money' : ($moyenPaiement === 'mobile_money' ? 'Mobile Money' : 'Espèces'));
        $reductionInfo = $tauxReduction > 0 ? " - Réduction: {$tauxReduction}% ({$montantReduction} FCFA)" : '';
        log_history($pdo, "Vente de {$nbProduits} produit(s) au client {$client} - Total: {$totalHT} FCFA - Reçu: {$prixRecu} FCFA - Paiement: {$paiementInfo}{$reductionInfo}");

        echo json_encode([
            'success' => true,
            'message' => 'Vente enregistrée avec succès',
            'idvente' => $idvente,
            'totalProduits' => $nbProduits,
            'totalHT' => $totalHT,
            'tauxReduction' => $tauxReduction,
            'montantReduction' => $montantReduction,
            'totalApresReduction' => $totalApresReduction,
            'prixRecu' => $prixRecu,
            'remboursement' => $remboursement,
            'moyenPaiement' => $moyenPaiement
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        error_log('Erreur ajout vente: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l’enregistrement de la vente']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}