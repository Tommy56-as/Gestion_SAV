<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs requis
    $required_fields = ['nom',  'prenom', 'telephone','adresse', 'produitLivre'];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le champ ' . $field . ' est vide']);
            exit;
        }
    }

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$produitLivre = ($_POST['produitLivre'] ?? 0);
$statut = $_POST['statut'] ?? 0; // actif par défaut

// verifier si le fournisseur existe deja en fonction du nom, prenom et produit livre
$check_stmt = $pdo->prepare("SELECT * FROM fournisseur WHERE nom = ? AND prenom = ? AND produit_livre = ?");
$check_stmt->execute([$nom, $prenom, $produitLivre]);
if ($check_stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Ce fournisseur existe déjà']);
    exit;
} else {

try {
    $stmt = $pdo->prepare("
        INSERT INTO fournisseur 
        (nom, prenom, telephone, adresse, produit_livre, statut)
        VALUES 
        (:nom, :prenom, :telephone, :adresse, :produit, :statut)
    ");

    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':telephone' => $telephone,
        ':adresse' => $adresse,
        ':produit' => $produitLivre,
        ':statut' => $statut
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Fournisseur ajouté avec succès'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
}
