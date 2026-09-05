<?php
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
require_admin();
// affihage de tous les utilisateurs
try {
    $entrepriseId = require_current_entreprise_id();
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE idEntreprise = ? AND supprime = 0");
    $stmt->execute([$entrepriseId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
} catch(PDOException $e) {
    error_log('Erreur utilisateurs: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des utilisateurs']);
}