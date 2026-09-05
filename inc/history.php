<?php
/**
 * Enregistre une action dans l'historique applicatif.
 */
function log_history(PDO $pdo, string $operation, ?string $userName = null): void
{
    $userName = $userName ?: ($_SESSION['user_nom'] ?? 'Système');
    $entrepriseId = (int) ($_SESSION['entreprise_id'] ?? 0);
    if ($entrepriseId > 0) {
        $statement = $pdo->prepare(
            'INSERT INTO historiques (idEntreprise, utilisateur, `operation_effectuée`)
             VALUES (:entreprise, :utilisateur, :operation)'
        );
        $statement->execute([
            ':entreprise' => $entrepriseId,
            ':utilisateur' => $userName,
            ':operation' => $operation,
        ]);
        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO historiques (utilisateur, `operation_effectuée`) VALUES (:utilisateur, :operation)'
    );
    $statement->execute([':utilisateur' => $userName, ':operation' => $operation]);
}
