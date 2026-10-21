<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/Database.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Referrer-Policy: no-referrer');

$token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? ''));
$tokenValid = preg_match('/^[a-f0-9]{64}$/', $token) === 1;
$success = false;
$error = '';

if ($tokenValid) {
    try {
        $statement = $pdo->prepare(
            'SELECT id, idUser FROM password_reset_tokens
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1'
        );
        $statement->execute([hash('sha256', $token)]);
        $resetRecord = $statement->fetch(PDO::FETCH_ASSOC);
        $tokenValid = is_array($resetRecord);
    } catch (PDOException $exception) {
        error_log('Erreur verification jeton de reinitialisation: ' . $exception->getMessage());
        $tokenValid = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providedCsrf = $_POST['csrf_token'] ?? '';
    if (!is_string($providedCsrf) || !hash_equals(csrf_token(), $providedCsrf)) {
        http_response_code(403);
        $error = 'Votre session a expiré. Ouvrez à nouveau le lien reçu par e-mail.';
    } elseif (!$tokenValid) {
        $error = 'Ce lien est invalide ou a expiré. Faites une nouvelle demande.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        if (strlen($password) < 8) {
            $error = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($password !== $passwordConfirmation) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            try {
                $pdo->beginTransaction();
                $statement = $pdo->prepare(
                    'SELECT id, idUser FROM password_reset_tokens
                     WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
                     LIMIT 1 FOR UPDATE'
                );
                $statement->execute([hash('sha256', $token)]);
                $resetRecord = $statement->fetch(PDO::FETCH_ASSOC);

                if (!$resetRecord) {
                    $pdo->rollBack();
                    $tokenValid = false;
                    $error = 'Ce lien a déjà été utilisé ou a expiré. Faites une nouvelle demande.';
                } else {
                    $statement = $pdo->prepare(
                        'UPDATE utilisateur SET MotDePasse = ? WHERE idUser = ? AND supprime = 0'
                    );
                    $statement->execute([
                        password_hash($password, PASSWORD_DEFAULT),
                        (int) $resetRecord['idUser'],
                    ]);

                    if ($statement->rowCount() !== 1) {
                        throw new RuntimeException('Le compte associé au jeton est introuvable.');
                    }

                    $statement = $pdo->prepare(
                        'UPDATE password_reset_tokens SET used_at = NOW(), expires_at = NOW()
                         WHERE idUser = ? AND used_at IS NULL'
                    );
                    $statement->execute([(int) $resetRecord['idUser']]);
                    $pdo->commit();
                    $success = true;
                    $tokenValid = false;
                }
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Erreur reinitialisation mot de passe: ' . $exception->getMessage());
                $error = 'La réinitialisation a échoué. Réessayez avec le lien reçu.';
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
    <title>Nouveau mot de passe - G.S.A.V</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <main class="container">
        <section class="header">
            <p class="login-kicker">Accès au compte</p>
            <h1>Choisissez un nouveau mot de passe</h1>
            <p class="login-promise">Votre lien est personnel et ne peut être utilisé qu’une seule fois.</p>
        </section>
        <section class="forms-container">
            <div class="form-section">
                <h2>Réinitialisation</h2>
                <?php if ($success): ?>
                <p class="login-intro" role="status">Votre mot de passe a été modifié. Vous pouvez vous connecter.</p>
                <div class="link-container">
                    <p><a href="index.php" class="link">Retour à la connexion</a></p>
                </div>
                <?php elseif (!$tokenValid): ?>
                <p class="error-messages" role="alert"><?= htmlspecialchars($error ?: 'Ce lien est invalide ou a expiré. Faites une nouvelle demande.', ENT_QUOTES, 'UTF-8') ?></p>
                <div class="link-container">
                    <p><a href="mot_de_passe_oublie.php" class="link">Demander un nouveau lien</a></p>
                </div>
                <?php else: ?>
                <p class="login-intro">Le nouveau mot de passe doit contenir au moins 8 caractères.</p>
                <?php if ($error !== ''): ?>
                <p class="error-messages" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form method="post" action="mot_de_passe_reinitialiser.php" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group">
                        <label for="new-password">Nouveau mot de passe</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            <input type="password" id="new-password" name="password" minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password-confirmation">Confirmer le mot de passe</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            <input type="password" id="password-confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                    <button type="submit" class="button">Enregistrer le mot de passe</button>
                </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>

</html>