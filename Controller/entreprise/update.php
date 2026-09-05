<?php
require_once __DIR__ . '/../admin_auth.php';
require_once __DIR__ . '/../../inc/authorization.php';
require_once __DIR__ . '/../../inc/saas.php';
require_admin();
require_csrf();
header('Content-Type: application/json');

$entrepriseId = require_current_entreprise_id();
$nom = trim((string) ($_POST['nom'] ?? ''));
$adresse = trim((string) ($_POST['adresse'] ?? ''));
$telephone = trim((string) ($_POST['telephone'] ?? ''));
$boitePostale = trim((string) ($_POST['boite_postale'] ?? ''));

if ($nom === '' || mb_strlen($nom) > 150) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le nom de l’entreprise est obligatoire.']);
    exit;
}

$statement = $pdo->prepare('UPDATE entreprises SET nom = ?, adresse = ?, telephone = ?, boite_postale = ? WHERE idEntreprise = ?');
$statement->execute([$nom, $adresse ?: null, $telephone ?: null, $boitePostale ?: null, $entrepriseId]);
echo json_encode(['success' => true, 'message' => 'Informations de l’entreprise mises à jour.']);