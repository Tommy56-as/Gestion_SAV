<?php
//// update_produit.php
require_once '../admin_auth.php';
require_permission('produit.update');
require_csrf();
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
header('Content-Type: application/json');

// mise à jour d'un produit
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idproduit'])) { 
    $entrepriseId = require_current_entreprise_id();
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
    $categorie = trim($_POST['categorie']);
    
    try {
        $categorieId = get_or_create_category($pdo, $entrepriseId, $categorie);
        $stmt = $pdo->prepare("UPDATE produit SET idCategorie = ?, categorie = ?, designation = ?, caracteristique = ?, quantite = ? WHERE idEntreprise = ? AND idproduit = ?");
        $stmt->execute([$categorieId, $categorie, $designation, $caracteristique, $quantite, $entrepriseId, $idproduit]);
        log_history($pdo, "Modification du produit {$designation} ({$caracteristique})");
        
        // Gestion de l'image si fournie
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageTmpPath = $_FILES['image']['tmp_name'];
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($imageTmpPath);
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            if (!isset($allowedTypes[$mime]) || @getimagesize($imageTmpPath) === false || $_FILES['image']['size'] > 3 * 1024 * 1024) {
                throw new Exception('Type ou taille d\'image non autorisé');
            }
            $imageName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
            $imagePath = realpath(__DIR__ . '/../../img') . DIRECTORY_SEPARATOR . $imageName;
            if (!move_uploaded_file($imageTmpPath, $imagePath)) {
                throw new Exception('Erreur lors du téléchargement de l\'image');
            }
            
            $stmt = $pdo->prepare("UPDATE produit SET image = ? WHERE idEntreprise = ? AND idproduit = ?");
            $stmt->execute([$imageName, $entrepriseId, $idproduit]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Produit mis à jour avec succès']);
    } catch(PDOException $e) {
        error_log('Erreur mise à jour produit: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du produit']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}