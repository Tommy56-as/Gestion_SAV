<?php
require_once '../admin_auth.php';
require_once '../../inc/authorization.php';
require_once '../../inc/history.php';
require_admin();
require_csrf();
header('Content-Type: application/json');

$fournisseurId = filter_input(INPUT_POST, 'idfour', FILTER_VALIDATE_INT);
$entrepriseId = require_current_entreprise_id();
$statut = filter_input(INPUT_POST, 'statut', FILTER_VALIDATE_INT);

if (!$fournisseurId || ($statut !== 0 && $statut !== 1)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    $statement = $pdo->prepare(
        'SELECT nom, prenom FROM fournisseur WHERE idEntreprise = ? AND idfour = ? AND supprime = 0'
    );
    $statement->execute([$entrepriseId, $fournisseurId]);
    $fournisseur = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$fournisseur) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Fournisseur introuvable']);
        exit;
    }

    $statement = $pdo->prepare('UPDATE fournisseur SET statut = ? WHERE idEntreprise = ? AND idfour = ?');
    $statement->execute([$statut, $entrepriseId, $fournisseurId]);

    $action = $statut === 1 ? 'Archivage' : 'Restauration';
    log_history(
        $pdo,
        "{$action} du fournisseur {$fournisseur['nom']} {$fournisseur['prenom']}"
    );

    echo json_encode([
        'success' => true,
        'message' => $statut === 1
            ? 'Fournisseur archivé avec succès'
            : 'Fournisseur restauré avec succès',
    ]);
} catch (Throwable $exception) {
    error_log('Erreur statut fournisseur: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour du statut',
    ]);
}