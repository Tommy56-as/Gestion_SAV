<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Désactive l'affichage d'erreurs HTML
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation des champs requis (texte)
        $required_fields = ['designation', 'caracteristique', 'quantite', 'quantite_min', 'prixUnitaire', 'categorie'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception('Le champ ' . $field . ' est requis');
            }
        }

        // Validation de l'image (obligatoire)
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Une image est requise et doit être valide');
        }

        $designation = trim($_POST['designation']);
        $caracteristique = trim($_POST['caracteristique']);
        $quantite = (int) trim($_POST['quantite']);
        $quantite_min = (int) trim($_POST['quantite_min']);
        $prixUnitaire = (double) trim($_POST['prixUnitaire']);
        $categorie = trim($_POST['categorie']);

        // Gestion de l'image
        $image = $_FILES['image']['name'];
        $image = filter_var($image, FILTER_SANITIZE_STRING);
        $image_size = $_FILES['image']['size'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = '../../img/' . $image;

        // Vérifications sur l'image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            throw new Exception('Type d\'image non autorisé (JPEG, PNG, GIF seulement)');
        }
        if ($image_size > 3 * 1024 * 1024) {
            throw new Exception('Image trop grande (max 3 Mo)');
        }

        // Vérifier si le produit existe déjà
        $check_stmt = $pdo->prepare("SELECT * FROM produit WHERE designation = ? AND caracteristique = ?");
        $check_stmt->execute([$designation, $caracteristique]);
        if ($check_stmt->fetch()) {
            throw new Exception('Ce produit existe déjà');
        }

        // Insérer en base
        $stmt = $pdo->prepare("INSERT INTO produit (designation, caracteristique, quantite, quantite_min, prixUnitaire, categorie, image) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$designation, $caracteristique, $quantite, $quantite_min, $prixUnitaire, $categorie, $image]);

        $product_id = $pdo->lastInsertId();

        // Déplacer le fichier
        if (!move_uploaded_file($image_tmp_name, $image_folder)) {
            // Si échec, supprimer l'entrée en base
            $pdo->prepare("DELETE FROM produit WHERE idproduit = ?")->execute([$product_id]);
            throw new Exception('Erreur lors du téléchargement de l\'image');
        }

        // Succès
        echo json_encode([
            'success' => true, 
            'message' => 'Produit ajouté avec succès',
            'id' => $product_id
        ]);
    } else {
        throw new Exception('Méthode non autorisée');
    }
} catch (Exception $e) {
    http_response_code(400); // Ou 500 selon l'erreur
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>