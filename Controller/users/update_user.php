<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
require_once '../../inc/history.php';
require_once '../../inc/saas.php';
require_admin();
require_csrf();
header('Content-Type: application/json');

// Vérification des champs obligatoires
$required_fields = ['Nom_Utilisateur', 'Email', 'TypeDeCompte','NomComplet', 'Telephone', 'Adresse'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        echo json_encode([
            'success' => false,
            'message' => "Paramètre manquant : $field"
        ]);
        exit;
    }
}

$id = (int) $_POST['idUser'];
$entrepriseId = require_current_entreprise_id();
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Utilisateur invalide']);
    exit;
}

$targetStatement = $pdo->prepare('SELECT Nom_Utilisateur FROM utilisateur WHERE idUser = ? AND idEntreprise = ? AND supprime = 0');
$targetStatement->execute([$id, $entrepriseId]);
$targetUser = $targetStatement->fetchColumn() ?: 'Utilisateur #' . $id;

$sql = "
UPDATE utilisateur SET
    Nom_Utilisateur = :nom,
    Email = :email,
    TypeDeCompte = :type,
    NomComplet = :nomComplet,
    Telephone = :telephone,
    Adresse = :adresse
";

$params = [
    ':nom' => $_POST['Nom_Utilisateur'],
    ':email' => $_POST['Email'],
    ':type' => $_POST['TypeDeCompte'],
    ':nomComplet' => $_POST['NomComplet'],
    ':telephone' => $_POST['Telephone'],
    ':adresse' => $_POST['Adresse'],
];

if (!empty($_POST['MotDePasse'])) {
    $sql .= ", MotDePasse = :pwd";
    $params[':pwd'] = password_hash($_POST['MotDePasse'], PASSWORD_DEFAULT);
}

$sql .= " WHERE idUser = :idUser AND idEntreprise = :entrepriseId AND supprime = 0";
$params[':idUser'] = $id;
$params[':entrepriseId'] = $entrepriseId;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    exit;
}
log_history($pdo, "Modification de l'utilisateur {$targetUser}");

echo json_encode([
    'success' => true,
    'message' => 'Utilisateur modifié avec succès'
]);
