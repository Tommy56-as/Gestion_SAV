<?php
require_once __DIR__ . '/Database.php';

function soft_delete(PDO $pdo, string $entity, int $id, ?int $entrepriseId = null): bool
{
    $entities = [
        'user' => ['table' => 'utilisateur', 'id' => 'idUser'],
        'fournisseur' => ['table' => 'fournisseur', 'id' => 'idfour'],
    ];

    if (!isset($entities[$entity]) || $id < 1) {
        return false;
    }

    $config = $entities[$entity];
    $tenantClause = $entrepriseId !== null ? ' AND idEntreprise = ?' : '';
    $statement = $pdo->prepare(
        "UPDATE {$config['table']} SET supprime = 1 WHERE {$config['id']} = ? AND supprime = 0{$tenantClause}"
    );
    $parameters = [$id];
    if ($entrepriseId !== null) $parameters[] = $entrepriseId;
    $statement->execute($parameters);
    return $statement->rowCount() > 0;
}
