<?php
require_once '../../inc/Database.php';
header('Content-Type: application/json');

if (!isset($_GET['designation']) || trim($_GET['designation']) === '') {
    echo json_encode([]);
    exit;
}

$designation = $_GET['designation'];

try {
    $stmt = $pdo->prepare("
        SELECT idproduit, caracteristique
        FROM produit
        WHERE designation = ?
        ORDER BY caracteristique
    ");
    $stmt->execute([$designation]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
