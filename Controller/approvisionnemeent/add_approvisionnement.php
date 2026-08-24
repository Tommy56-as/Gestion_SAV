<?php
require_once '../admin_auth.php';
require_csrf();
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
header('Content-Type: application/json');

// La création d'une commande se fait uniquement via une requête POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Convertir les valeurs reçues et refuser les données invalides avant l'accès à la base.
$idProduit = filter_input(INPUT_POST, 'idproduit', FILTER_VALIDATE_INT);
$quantite = filter_input(INPUT_POST, 'quantite_app', FILTER_VALIDATE_INT);
$idFour = filter_input(INPUT_POST, 'idfour', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
$prixTotal = filter_input(INPUT_POST, 'prix_total', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
$dateApp = $_POST['date_app'] ?? '';

if (!$idProduit || !$quantite || $quantite < 1 || !$dateApp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Produit, quantité et date sont obligatoires']);
    exit;
}

try {
    // Mémoriser le stock au moment de la commande pour le suivi historique.
    $productStmt = $pdo->prepare('SELECT quantite FROM produit WHERE idproduit = ?');
    $productStmt->execute([$idProduit]);
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produit introuvable']);
        exit;
    }

    // Une commande reste en cours : le stock ne sera augmenté qu'à sa réception.
    $stmt = $pdo->prepare("INSERT INTO approvisionnement
        (idproduit, quantite_stock, quantite_app, prix_total, idfour, statut, date_app)
        VALUES (?, ?, ?, ?, ?, 'encours', ?)");
    $stmt->execute([$idProduit, (int) $product['quantite'], $quantite, $prixTotal, $idFour ?: null, $dateApp]);
    log_history($pdo, "Nouvelle commande d'approvisionnement pour le produit {$idProduit} avec quantité {$quantite}");
    echo json_encode(['success' => true, 'message' => 'Commande enregistrée']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l’enregistrement']);
}