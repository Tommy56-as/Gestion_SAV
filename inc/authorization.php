<?php
/**
 * Autorisation centralisee des utilisateurs connectes.
 * Les permissions explicites en base remplacent les droits par defaut du role.
 */
require_once __DIR__ . '/Database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user_id(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function is_authenticated(): bool
{
    return current_user_id() > 0 && !empty($_SESSION['user_type']);
}

function default_permissions_for_role(string $role): array
{
    return match ($role) {
        'Administrateur' => ['*'],
        'Caissier' => ['vente.create', 'vente.read', 'produit.read', 'client.read'],
        'Technicien' => ['reparation.create', 'reparation.read', 'reparation.update', 'produit.read'],
        default => [],
    };
}

function user_can(string $permission): bool
{
    global $pdo;

    if (!is_authenticated()) {
        return false;
    }

    if ($_SESSION['user_type'] === 'Administrateur') {
        return true;
    }

    static $permissions = null;
    if ($permissions === null) {
        $permissions = default_permissions_for_role((string)$_SESSION['user_type']);
        try {
            $statement = $pdo->prepare('SELECT permission FROM user_permissions WHERE idUser = ? AND allowed = 1');
            $statement->execute([current_user_id()]);
            $explicit = $statement->fetchAll(PDO::FETCH_COLUMN);
            if ($explicit) {
                $permissions = $explicit;
            }
        } catch (PDOException $exception) {
            error_log('Permissions indisponibles: ' . $exception->getMessage());
        }
    }

    return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
}

function require_permission(string $permission): void
{
    if (!is_authenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Connexion requise']);
        exit;
    }

    if (!user_can($permission)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à effectuer cette action']);
        exit;
    }
}

function require_page_access(): void
{
    if (!is_authenticated()) {
        header('Location: index.php');
        exit;
    }
}

function require_admin(): void
{
    if (!is_authenticated() || $_SESSION['user_type'] !== 'Administrateur') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès réservé à l\'administrateur']);
        exit;
    }
}

function page_permission(string $page): ?string
{
    return [
        'ventes' => 'vente.read',
        'produits' => 'produit.read',
        'reparations' => 'reparation.read',
        'utilisateurs' => 'user.read',
        'fournisseurs' => '__admin__',
        'historiques' => '__admin__',
        'Statistiques' => '__admin__',
        'autorisations' => '__admin__',
        'sessions' => '__admin__',
    ][$page] ?? null;
}

function is_admin(): bool
{
    return is_authenticated() && ($_SESSION['user_type'] ?? '') === 'Administrateur';
}

function register_user_session(): void
{
    global $pdo;
    if (!is_authenticated()) return;

    try {
        $statement = $pdo->prepare("INSERT INTO user_sessions (session_id, idUser, last_activity, ip_address, user_agent)
            VALUES (?, ?, NOW(), ?, ?) ON DUPLICATE KEY UPDATE last_activity = NOW(), ip_address = VALUES(ip_address), user_agent = VALUES(user_agent)");
        $statement->execute([session_id(), current_user_id(), $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
    } catch (PDOException $exception) {
        error_log('Sessions utilisateurs indisponibles: ' . $exception->getMessage());
    }
}

function remove_user_session(): void
{
    global $pdo;
    if (!session_id()) return;

    try {
        $statement = $pdo->prepare('DELETE FROM user_sessions WHERE session_id = ?');
        $statement->execute([session_id()]);
    } catch (PDOException $exception) {
        error_log('Suppression de session impossible: ' . $exception->getMessage());
    }
}