<?php
require_once '../admin_auth.php';
require_once '../../inc/history.php';
require_permission('reparation.create');
require_csrf();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$iduser = filter_input(INPUT_POST, 'iduser', FILTER_VALIDATE_INT);
$entrepriseId = require_current_entreprise_id();
$pieces = json_decode($_POST['pieces'] ?? '[]', true);
$mainOeuvre = max(0, (float)($_POST['main_oeuvre'] ?? 0));
$nomClient = trim($_POST['nomClient'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$email = trim($_POST['email'] ?? '');
$appareil = trim($_POST['appareil'] ?? '');
$diagnostic = trim($_POST['diagnostic'] ?? '');
$solution = trim($_POST['solution'] ?? '');

if (!$iduser || !$nomClient || !$telephone || !$appareil || !is_array($pieces) || !$pieces) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Les champs obligatoires sont incomplets']);
    exit;
}

try {
    $pdo->beginTransaction();
    $technicianStatement = $pdo->prepare("SELECT idUser FROM utilisateur WHERE idEntreprise = ? AND idUser = ? AND TypeDeCompte = 'Technicien' AND Statut = 0 AND supprime = 0");
    $technicianStatement->execute([$entrepriseId, $iduser]);
    if (!$technicianStatement->fetch()) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Technicien ou équipement invalide']);
        exit;
    }
    $productStatement = $pdo->prepare("SELECT prixUnitaire, CAST(quantite AS UNSIGNED) AS stock FROM produit WHERE idEntreprise = ? AND idproduit = ? AND categorie = 'equipement' FOR UPDATE");
    $pieceRows = [];
    $totalPieces = 0;
    foreach ($pieces as $piece) {
        $pieceId = filter_var($piece['idproduit'] ?? null, FILTER_VALIDATE_INT);
        $pieceQuantity = filter_var($piece['quantite'] ?? null, FILTER_VALIDATE_INT);
        if (!$pieceId || !$pieceQuantity || $pieceQuantity < 1) throw new RuntimeException('Pièce invalide');
        $productStatement->execute([$entrepriseId, $pieceId]);
        $product = $productStatement->fetch(PDO::FETCH_ASSOC);
        if (!$product || (int)$product['stock'] < $pieceQuantity) throw new RuntimeException('Stock insuffisant pour une pièce');
        $unitPrice = (float)($product['prixUnitaire'] ?? 0);
        $amount = $unitPrice * $pieceQuantity;
        $pieceRows[] = [$pieceId, $pieceQuantity, $unitPrice, $amount];
        $totalPieces += $amount;
    }
    $prixTotal = $totalPieces + $mainOeuvre;
    $statement = $pdo->prepare("INSERT INTO reparation
        (idEntreprise, iduser, nomClient, telephone, email, appareil, diagnostic, solution, statut, quantite, prixUnitaire, prixTotal, main_oeuvre)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', 0, 0, ?, ?)");
    $statement->execute([$entrepriseId, $iduser, $nomClient, $telephone, $email ?: null, $appareil, $diagnostic ?: null, $solution ?: null, $prixTotal, $mainOeuvre]);
    $idrep = $pdo->lastInsertId();
    $pieceInsert = $pdo->prepare('INSERT INTO reparation_piece (idrep, idproduit, quantite, prix_unitaire, montant) VALUES (?, ?, ?, ?, ?)');
    $stockUpdate = $pdo->prepare('UPDATE produit SET quantite = quantite - ? WHERE idEntreprise = ? AND idproduit = ?');
    foreach ($pieceRows as [$pieceId, $pieceQuantity, $unitPrice, $amount]) {
        $pieceInsert->execute([$idrep, $pieceId, $pieceQuantity, $unitPrice, $amount]);
        $stockUpdate->execute([$pieceQuantity, $entrepriseId, $pieceId]);
    }
    $pdo->commit();
    log_history($pdo, "Création de la réparation de {$nomClient}");
    echo json_encode(['success' => true, 'message' => 'Réparation enregistrée']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    $message = in_array($e->getMessage(), ['Pièce invalide', 'Stock insuffisant pour une pièce'], true)
        ? $e->getMessage()
        : 'Erreur lors de la création de la réparation';
    echo json_encode(['success' => false, 'message' => $message]);
}