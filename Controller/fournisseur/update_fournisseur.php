<?php

header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
require_csrf();
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idfour'])) {
    $entrepriseId = require_current_entreprise_id();
    // Validation des champs requis
    $required_fields = [ 'nom',  'prenom', 'telephone','adresse', 'produitLivre'];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le champ ' . $field . ' est vide']);
            exit;
        }
    }
    $idfour = ($_POST['idfour']);
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $produitLivre = ($_POST['produitLivre'] ?? 0);
    $statut = $_POST['statut'] ?? 0; // actif par défaut
    try {
        $stmt = $pdo->prepare("
            UPDATE fournisseur SET 
            nom = :nom,
            prenom = :prenom,
            telephone = :telephone,
            adresse = :adresse,
            produit_livre = :produit,
            statut = :statut
            WHERE idEntreprise = :entreprise AND idfour = :idfour
        ");

        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':telephone' => $telephone,
            ':adresse' => $adresse,
            ':produit' => $produitLivre,
            ':statut' => $statut,
            ':entreprise' => $entrepriseId,
            ':idfour' => $idfour
        ]);
        log_history($pdo, "Modification du fournisseur {$nom} {$prenom}");

        echo json_encode([
            'success' => true,
            'message' => 'Fournisseur mis à jour avec succès'
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