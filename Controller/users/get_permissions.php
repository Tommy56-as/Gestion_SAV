<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_admin();
header('Content-Type: application/json');

try {
    $entrepriseId = require_current_entreprise_id();
    $usersStatement = $pdo->prepare("SELECT idUser, Nom_Utilisateur, NomComplet, TypeDeCompte, Statut
        FROM utilisateur
        WHERE idEntreprise = ? AND TypeDeCompte <> 'Administrateur' AND supprime = 0
        ORDER BY Nom_Utilisateur");
    $usersStatement->execute([$entrepriseId]);
    $users = $usersStatement->fetchAll(PDO::FETCH_ASSOC);

    $permissionsStatement = $pdo->prepare("SELECT up.idUser, up.permission, up.allowed
        FROM user_permissions up
        INNER JOIN utilisateur u ON u.idUser = up.idUser
        WHERE u.idEntreprise = ? AND u.supprime = 0 AND u.TypeDeCompte <> 'Administrateur'");
    $permissionsStatement->execute([$entrepriseId]);
    $permissions = $permissionsStatement->fetchAll(PDO::FETCH_ASSOC);
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