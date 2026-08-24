<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
header('Content-Type: application/json');

try {
    $users = $pdo->query("SELECT idUser, Nom_Utilisateur, NomComplet, TypeDeCompte, Statut FROM utilisateur WHERE TypeDeCompte <> 'Administrateur' ORDER BY Nom_Utilisateur")->fetchAll(PDO::FETCH_ASSOC);
    $permissions = $pdo->query('SELECT idUser, permission, allowed FROM user_permissions')->fetchAll(PDO::FETCH_ASSOC);
    $byUser = [];
    foreach ($permissions as $permission) {
        $byUser[$permission['idUser']][$permission['permission']] = (int)$permission['allowed'];
    }
    foreach ($users as &$user) {
        $user['permissions'] = $byUser[$user['idUser']] ?? [];
    }
    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible de charger les autorisations']);
}