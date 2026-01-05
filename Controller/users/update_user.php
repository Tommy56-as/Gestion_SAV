<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
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

$sql .= " WHERE idUser = :idUser";
$params[':idUser'] = $id;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode([
    'success' => true,
    'message' => 'Utilisateur modifié avec succès'
]);
