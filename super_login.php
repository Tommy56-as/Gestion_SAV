<?php
require_once __DIR__ . '/inc/super_auth.php';

if (is_super_admin()) {
    header('Location: super_admin.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || !super_admin_login($pdo, $email, $password)) {
        $error = 'Identifiants super utilisateur invalides.';
    } else {
        header('Location: super_admin.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Administration SaaS</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="css/super-login.css">
    <script src="js/icon-fallback.js" defer></script>
</head>
<body>
    <main class="super-login-shell">
        <section class="super-login-intro">
            <div class="super-mark"><span class="material-icons-sharp">hub</span></div>
            <span class="super-kicker">Administration de la plateforme</span>
            <h1>Le centre de contrôle de votre SaaS.</h1>
            <p>Pilotez les entreprises, les abonnements et les revenus depuis un espace réservé à votre équipe.</p>
            <ul class="super-login-points">
                <li><span class="material-icons-sharp">check_circle</span> Suivi des entreprises</li>
                <li><span class="material-icons-sharp">check_circle</span> Plans et tarifs personnalisables</li>
                <li><span class="material-icons-sharp">check_circle</span> Prolongation et paiements manuels</li>
            </ul>
        </section>
        <section class="super-login-form">
            <span class="super-kicker">Espace sécurisé</span>
            <h2>Connexion administrateur</h2>
            <p>Accédez aux réglages de la plateforme.</p>
            <?php if ($error): ?><div class="super-login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group"><label for="email">Adresse email</label><input id="email" type="email" name="email" required autofocus></div>
                <div class="form-group"><label for="password">Mot de passe</label><input id="password" type="password" name="password" required></div>
                <button class="super-login-submit" type="submit">Ouvrir la console <span class="material-icons-sharp">arrow_forward</span></button>
            </form>
            <?php if (super_admin_count($pdo) === 0): ?><a class="super-login-link" href="super_setup.php">Créer le premier compte administrateur</a><?php endif; ?>
        </section>
    </main>
</body>
</html>
