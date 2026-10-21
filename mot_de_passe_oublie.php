<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/Database.php';
require_once __DIR__ . '/inc/password_reset.php';

$submitted = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providedCsrf = $_POST['csrf_token'] ?? '';
    if (!is_string($providedCsrf) || !hash_equals(csrf_token(), $providedCsrf)) {
        http_response_code(403);
        $error = 'Votre session a expiré. Rechargez la page puis réessayez.';
    } else {
        $submitted = true;
        $email = trim((string) ($_POST['email'] ?? ''));

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $statement = $pdo->prepare('SELECT idUser FROM utilisateur WHERE Email = ? AND supprime = 0 LIMIT 1');
                $statement->execute([$email]);
                $userId = $statement->fetchColumn();

                if ($userId !== false) {
                    $userId = (int) $userId;
                    $statement = $pdo->prepare(
                        'SELECT COUNT(*) FROM password_reset_tokens
                         WHERE idUser = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)'
                    );
                    $statement->execute([$userId]);

                    if ((int) $statement->fetchColumn() < 3) {
                        $token = bin2hex(random_bytes(32));
                        $tokenHash = hash('sha256', $token);

                        $pdo->beginTransaction();
                        $statement = $pdo->prepare(
                            'UPDATE password_reset_tokens SET expires_at = NOW()
                             WHERE idUser = ? AND used_at IS NULL AND expires_at > NOW()'
                        );
                        $statement->execute([$userId]);
                        $statement = $pdo->prepare(
                            'INSERT INTO password_reset_tokens (idUser, token_hash, expires_at)
                             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
                        );
                        $statement->execute([$userId, $tokenHash]);
                        $pdo->commit();

                        if (!send_password_reset_email($email, $token)) {
                            $statement = $pdo->prepare(
                                'UPDATE password_reset_tokens SET expires_at = NOW() WHERE token_hash = ?'
                            );
                            $statement->execute([$tokenHash]);
                            error_log('Echec de l’envoi du courriel de reinitialisation. Verifiez la configuration mail PHP.');
                        }
                    }
                }
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Erreur demande de reinitialisation: ' . $exception->getMessage());
            }
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
    <title>Mot de passe oublié - G.S.A.V</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <main class="container">
        <section class="header">
            <p class="login-kicker">Accès au compte</p>
            <h1>Récupération du mot de passe</h1>
            <p class="login-promise">Un lien temporaire vous permettra de choisir un nouveau mot de passe.</p>
        </section>
        <section class="forms-container">
            <div class="form-section">
                <h2>Mot de passe oublié ?</h2>
                <?php if ($submitted && $error === ''): ?>
                <p class="login-intro" role="status">Si cette adresse correspond à un compte actif, un lien de réinitialisation sera envoyé.</p>
                <?php else: ?>
                <p class="login-intro">Saisissez l’adresse e-mail associée à votre compte.</p>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                <p class="error-messages" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form method="post" action="mot_de_passe_oublie.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group">
                        <label for="reset-email">Adresse e-mail</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <input type="email" id="reset-email" name="email" autocomplete="email" required>
                        </div>
                    </div>
                    <button type="submit" class="button">Envoyer le lien</button>
                    <div class="link-container">
                        <p><a href="index.php" class="link">Retour à la connexion</a></p>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>

</html>