<?php
require_once __DIR__ . '/Database.php';

function soft_delete(PDO $pdo, string $entity, int $id): bool
{
    $entities = [
        'user' => ['table' => 'utilisateur', 'id' => 'idUser'],
        'fournisseur' => ['table' => 'fournisseur', 'id' => 'idfour'],
    ];

    if (!isset($entities[$entity]) || $id < 1) {
        return false;
    }

    $config = $entities[$entity];
    $statement = $pdo->prepare(
        "UPDATE {$config['table']} SET supprime = 1 WHERE {$config['id']} = ? AND supprime = 0"
    );
    $statement->execute([$id]);
    return $statement->rowCount() > 0;
}
