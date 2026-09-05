<?php
require_once __DIR__ . '/../admin_auth.php';
require_once '../../inc/authorization.php';
require_once '../../inc/saas.php';
require_admin();
header('Content-Type: application/json');

$entrepriseId = require_current_entreprise_id();
$action = $_SERVER['REQUEST_METHOD'] === 'GET' ? 'list' : ($_POST['action'] ?? '');

try {
    if ($action === 'list') {
        $statement = $pdo->prepare(
            'SELECT c.idCategorie, c.libelle, c.actif, COUNT(p.idproduit) AS produits
             FROM categorie c
             LEFT JOIN produit p ON p.idCategorie = c.idCategorie
             WHERE c.idEntreprise = ?
             GROUP BY c.idCategorie, c.libelle, c.actif
             ORDER BY c.libelle'
        );
        $statement->execute([$entrepriseId]);
        echo json_encode(['success' => true, 'categories' => $statement->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    require_csrf();
    $categoryId = filter_input(INPUT_POST, 'idCategorie', FILTER_VALIDATE_INT);

    if ($action === 'create' || $action === 'update') {
        $label = trim((string) ($_POST['libelle'] ?? ''));
        if ($label === '' || mb_strlen($label) > 100) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le libellé est obligatoire et limité à 100 caractères.']);
            exit;
        }

        if ($action === 'create') {
            $statement = $pdo->prepare('INSERT INTO categorie (idEntreprise, libelle) VALUES (?, ?)');
            $statement->execute([$entrepriseId, $label]);
            echo json_encode(['success' => true, 'message' => 'Catégorie créée avec succès.']);
            exit;
        }

        if (!$categoryId) {
            throw new InvalidArgumentException('Catégorie invalide.');
        }
        $statement = $pdo->prepare(
            'UPDATE categorie SET libelle = ? WHERE idCategorie = ? AND idEntreprise = ?'
        );
        $statement->execute([$label, $categoryId, $entrepriseId]);
        echo json_encode(['success' => true, 'message' => 'Catégorie modifiée avec succès.']);
        exit;
    }

    if ($action === 'delete') {
        if (!$categoryId) {
            throw new InvalidArgumentException('Catégorie invalide.');
        }
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM produit WHERE idCategorie = ? AND idEntreprise = ?'
        );
        $statement->execute([$categoryId, $entrepriseId]);
        if ((int) $statement->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cette catégorie est utilisée par un produit.']);
            exit;
        }

        $statement = $pdo->prepare('DELETE FROM categorie WHERE idCategorie = ? AND idEntreprise = ?');
        $statement->execute([$categoryId, $entrepriseId]);
        echo json_encode(['success' => true, 'message' => 'Catégorie supprimée avec succès.']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
} catch (PDOException $exception) {
    $message = $exception->errorInfo[1] === 1062
        ? 'Cette catégorie existe déjà dans votre entreprise.'
        : 'Erreur lors de la gestion de la catégorie.';
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => $message]);
} catch (Throwable $exception) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
