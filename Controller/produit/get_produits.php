<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
header('Content-Type: application/json');

//affichage de tous les produits
try {   
    $entrepriseId = require_current_entreprise_id();
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE idEntreprise = ?");
    $stmt->execute([$entrepriseId]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $produits]);
} catch(PDOException $e) {
    error_log('Erreur produits: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des produits']);
}