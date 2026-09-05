<?php
require_once __DIR__ . '/inc/super_auth.php';

if (super_admin_count($pdo) > 0) {
    header('Location: super_login.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name = trim((string) ($_POST['nom'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['confirmation'] ?? '');

    if ($name === '') $errors[] = 'Le nom est obligatoire.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'L’email est invalide.';
    if (strlen($password) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    if ($password !== $confirmation) $errors[] = 'Les mots de passe ne correspondent pas.';

    if (!$errors) {
        try {
            $statement = $pdo->prepare('INSERT INTO super_administrateurs (email, nom, mot_de_passe) VALUES (?, ?, ?)');
            $statement->execute([$email, $name, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: super_login.php');
            exit;
        } catch (Throwable $exception) {
            $errors[] = 'Impossible de créer le compte. Vérifiez la migration.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Initialiser l’administration SaaS</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="css/super-login.css">
    <script src="js/icon-fallback.js" defer></script>
</head>
<body>
    <main class="super-login-shell">
        <section class="super-login-intro">
            <div class="super-mark"><span class="material-icons-sharp">admin_panel_settings</span></div>
            <span class="super-kicker">Première configuration</span>
            <h1>Construisez votre console SaaS.</h1>
            <p>Ce compte sera réservé à la gestion globale des entreprises, plans et abonnements.</p>
        </section>
        <section class="super-login-form">
            <span class="super-kicker">Compte principal</span>
            <h2>Créer le super utilisateur</h2>
            <p>Cette étape n’est disponible qu’une seule fois.</p>
            <?php foreach ($errors as $error): ?><div class="super-login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endforeach; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group"><label for="nom">Nom</label><input id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="form-group"><label for="email">Email</label><input id="email" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="form-group"><label for="password">Mot de passe</label><input id="password" type="password" name="password" minlength="8" required></div>
                <div class="form-group"><label for="confirmation">Confirmation</label><input id="confirmation" type="password" name="confirmation" minlength="8" required></div>
                <button class="button" type="submit">Créer le compte</button>
            </form>
        </section>
    </main>
</body>
</html>
