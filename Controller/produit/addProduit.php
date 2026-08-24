<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Désactive l'affichage d'erreurs HTML
require_once '../admin_auth.php';
require_permission('produit.create');
require_csrf();
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
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
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_size = $_FILES['image']['size'];
        $image_folder = realpath(__DIR__ . '/../../img');
        if ($image_folder === false) {
            throw new Exception('Dossier image indisponible');
        }

        // Vérifications sur l'image
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($image_tmp_name);
        $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        if (!isset($allowed_types[$mime]) || @getimagesize($image_tmp_name) === false) {
            throw new Exception('Type d\'image non autorisé (JPEG, PNG, GIF seulement)');
        }
        if ($image_size > 3 * 1024 * 1024) {
            throw new Exception('Image trop grande (max 3 Mo)');
        }
        $image = bin2hex(random_bytes(16)) . '.' . $allowed_types[$mime];
        $image_path = $image_folder . DIRECTORY_SEPARATOR . $image;

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
        if (!move_uploaded_file($image_tmp_name, $image_path)) {
            // Si échec, supprimer l'entrée en base
            $pdo->prepare("DELETE FROM produit WHERE idproduit = ?")->execute([$product_id]);
            throw new Exception('Erreur lors du téléchargement de l\'image');
        }

        log_history($pdo, "Ajout du produit {$designation} ({$caracteristique})");

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
    $safeMessages = [
        'Une image est requise et doit être valide',
        'Type d\'image non autorisé (JPEG, PNG, GIF seulement)',
        'Image trop grande (max 3 Mo)',
        'Ce produit existe déjà',
        'Dossier image indisponible',
        'Erreur lors du téléchargement de l\'image'
    ];
    error_log('Erreur ajout produit: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => in_array($e->getMessage(), $safeMessages, true) ? $e->getMessage() : 'Erreur lors de la création du produit']);
}
?>