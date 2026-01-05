<?php
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
// affihage de tous les utilisateurs
try {
    $stmt = $pdo->query("SELECT * FROM utilisateur");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}

