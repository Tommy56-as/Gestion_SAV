<?php
/** Regles SaaS partagees entre les pages et les controles d'acces. */

function current_entreprise_id(): int
{
    return (int) ($_SESSION['entreprise_id'] ?? 0);
}

function current_entreprise(PDO $pdo): array
{
    $entrepriseId = require_current_entreprise_id();
    $statement = $pdo->prepare('SELECT idEntreprise, nom, adresse, telephone, boite_postale FROM entreprises WHERE idEntreprise = ? AND actif = 1');
    $statement->execute([$entrepriseId]);
    return $statement->fetch(PDO::FETCH_ASSOC) ?: [
        'idEntreprise' => $entrepriseId,
        'nom' => '',
        'adresse' => '',
        'telephone' => '',
        'boite_postale' => '',
    ];
}

function require_current_entreprise_id(): int
{
    $entrepriseId = current_entreprise_id();
    if ($entrepriseId <= 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Entreprise non identifiée']);
        exit;
    }

    return $entrepriseId;
}

function current_subscription(PDO $pdo): ?array
{
    $entrepriseId = require_current_entreprise_id();
    $statement = $pdo->prepare(
        'SELECT a.*, p.code AS plan_code, p.nom AS plan_nom, p.description AS plan_description,
                p.prix_mensuel, p.prix_annuel
         FROM abonnements a
         INNER JOIN plans p ON p.idPlan = a.idPlan
         WHERE a.idEntreprise = ?
         ORDER BY a.idAbonnement DESC
         LIMIT 1'
    );
    $statement->execute([$entrepriseId]);
    return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
}

function subscription_is_active(array $subscription): bool
{
    return in_array($subscription['statut'] ?? '', ['trialing', 'active'], true)
        && ($subscription['date_fin'] ?? '') >= date('Y-m-d');
}

function subscription_feature(PDO $pdo, string $feature): ?array
{
    $subscription = current_subscription($pdo);
    if (!$subscription) {
        return null;
    }
    $statement = $pdo->prepare(
        'SELECT pf.fonctionnalite_code, pf.limite
         FROM plan_fonctionnalites pf
         WHERE pf.idPlan = ? AND pf.fonctionnalite_code = ?'
    );
    $statement->execute([$subscription['idPlan'], $feature]);
    return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
}

function get_or_create_category(PDO $pdo, int $entrepriseId, string $label): int
{
    $statement = $pdo->prepare(
        'INSERT INTO categorie (idEntreprise, libelle) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE idCategorie = LAST_INSERT_ID(idCategorie)'
    );
    $statement->execute([$entrepriseId, $label]);
    return (int) $pdo->lastInsertId();
}

function entreprise_feature_enabled(string $feature): bool
{
    global $pdo;

    $entrepriseId = current_entreprise_id();
    if ($entrepriseId <= 0) {
        return true;
    }

    try {
        $statement = $pdo->prepare(
            'SELECT 1
             FROM entreprise_types et
             INNER JOIN type_fonctionnalites tf ON tf.type_code = et.type_code
             INNER JOIN fonctionnalites f ON f.code = tf.fonctionnalite_code
             INNER JOIN entreprises e ON e.idEntreprise = et.idEntreprise
             WHERE et.idEntreprise = ? AND et.idEntreprise = e.idEntreprise
               AND e.actif = 1 AND f.actif = 1 AND f.code = ?
             LIMIT 1'
        );
        $statement->execute([$entrepriseId, $feature]);
        return (bool) $statement->fetchColumn();
    } catch (PDOException $exception) {
        // Compatibilite temporaire avec une base qui n'a pas encore applique la migration SaaS.
        error_log('Fonctionnalites SaaS indisponibles: ' . $exception->getMessage());
        return true;
    }
}

function require_entreprise_feature(string $feature): void
{
    if (!entreprise_feature_enabled($feature)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Cette fonctionnalite n’est pas activee pour votre entreprise.',
        ]);
        exit;
    }
}
