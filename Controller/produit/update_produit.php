<?php
//// update_produit.php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
header('Content-Type: application/json');

// mise à jour d'un produit
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idproduit'])) { 
    // Validation des champs requis
    $required_fields = ['Nom_Utilisateur', 'Email', 'TypeDeCompte', 'MotDePasse', 'NomComplet', 'Telephone', 'Adresse'];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le champ ' . $field . ' est requis']);
            exit;
        }
    }
    $idproduit = $_POST['idproduit'];
    $designation = $_POST['designation'];
    $caracteristique = $_POST['caracteristique'];
    $quantite = $_POST['quantite'];
    
    try {
        $stmt = $pdo->prepare("UPDATE produit SET designation = ?, caracteristique = ?, quantite = ? WHERE idproduit = ?");
        $stmt->execute([$designation, $caracteristique, $quantite, $idproduit]);
        
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