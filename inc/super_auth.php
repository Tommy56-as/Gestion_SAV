<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Database.php';

function is_super_admin(): bool
{
    return (int) ($_SESSION['super_admin_id'] ?? 0) > 0;
}

function require_super_admin(): void
{
    if (!is_super_admin()) {
        header('Location: super_login.php');
        exit;
    }
}

function super_admin_count(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM super_administrateurs WHERE actif = 1')->fetchColumn();
}

function super_admin_login(PDO $pdo, string $email, string $password): bool
{
    $statement = $pdo->prepare('SELECT idSuperAdmin, nom, email, mot_de_passe FROM super_administrateurs WHERE email = ? AND actif = 1 LIMIT 1');
    $statement->execute([$email]);
    $admin = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !password_verify($password, $admin['mot_de_passe'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['super_admin_id'] = (int) $admin['idSuperAdmin'];
    $_SESSION['super_admin_nom'] = $admin['nom'];
    $_SESSION['super_admin_email'] = $admin['email'];
    $pdo->prepare('UPDATE super_administrateurs SET dernier_acces = NOW() WHERE idSuperAdmin = ?')
        ->execute([$admin['idSuperAdmin']]);
    return true;
}
