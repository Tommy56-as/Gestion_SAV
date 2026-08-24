<?php
require_once '../admin_auth.php';
header('Content-Type: application/json');

try {
    $techniciens = $pdo->query("SELECT idUser, NomComplet, Nom_Utilisateur
        FROM utilisateur WHERE TypeDeCompte = 'Technicien' AND Statut = 0
        ORDER BY NomComplet, Nom_Utilisateur")->fetchAll(PDO::FETCH_ASSOC);
        $equipements = $pdo->query("SELECT idproduit, designation, caracteristique, prixUnitaire
        FROM produit WHERE categorie = 'equipement'
        ORDER BY designation, caracteristique")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'techniciens' => $techniciens, 'equipements' => $equipements]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement du formulaire']);
}