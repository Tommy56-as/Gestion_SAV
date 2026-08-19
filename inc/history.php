<?php
/**
 * Enregistre une action dans l'historique applicatif.
 */
function log_history(PDO $pdo, string $operation, ?string $userName = null): void
{
    $userName = $userName ?: ($_SESSION['user_nom'] ?? 'Système');
    $statement = $pdo->prepare(
        'INSERT INTO historiques (utilisateur, `operation_effectuée`) VALUES (:utilisateur, :operation)'
    );
    $statement->execute([
        ':utilisateur' => $userName,
        ':operation' => $operation,
    ]);
}
