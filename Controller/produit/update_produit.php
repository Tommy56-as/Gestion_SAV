<?php
//// update_produit.php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
header('Content-Type: application/json');

// mise à jour d'un produit
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idproduit'])) { 
    // Validation des champs requis
    $required_fields = ['designation', 'caracteristique', 'quantite', 'quantite_min', 'prixUnitaire', 'categorie'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception('Le champ ' . $field . ' est requis');
            }
        }
    $idproduit = $_POST['idproduit'];
    $designation = $_POST['designation'];
    $caracteristique = $_POST['caracteristique'];
    $quantite = $_POST['quantite'];
    
    try {
        $stmt = $pdo->prepare("UPDATE produit SET designation = ?, caracteristique = ?, quantite = ? WHERE idproduit = ?");
        $stmt->execute([$designation, $caracteristique, $quantite, $idproduit]);
        log_history($pdo, "Modification du produit {$designation} ({$caracteristique})");
        
        // Gestion de l'image si fournie
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = '../../img/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
            
            $stmt = $pdo->prepare("UPDATE produit SET image = ? WHERE idproduit = ?");
            $stmt->execute([$imagePath, $idproduit]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Produit mis à jour avec succès']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}