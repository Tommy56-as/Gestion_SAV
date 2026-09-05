<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/Database.php';

$errors = [];
$success = false;
$types = [];
$installationReady = true;
$submittedTypes = isset($_POST['types']) && is_array($_POST['types']) ? $_POST['types'] : [];
$csrfValid = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providedCsrf = $_POST['csrf_token'] ?? '';
    $csrfValid = is_string($providedCsrf)
        && hash_equals(csrf_token(), $providedCsrf);
    if (!$csrfValid) {
        $errors[] = 'Votre session a expiré. Rechargez la page puis recommencez l’installation.';
    }
}

try {
    $types = $pdo->query(
        'SELECT code, libelle FROM types_entreprise WHERE actif = 1 ORDER BY libelle'
    )->fetchAll(PDO::FETCH_ASSOC);
    $installationExists = (bool) $pdo->query('SELECT 1 FROM entreprises LIMIT 1')->fetchColumn();
} catch (PDOException $exception) {
    $installationReady = false;
    $installationExists = false;
    $errors[] = 'Appliquez d’abord la migration SaaS à la base de données.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $installationReady && !$installationExists && $csrfValid) {
    $companyName = trim((string) ($_POST['nom_entreprise'] ?? ''));
    $companyAddress = trim((string) ($_POST['adresse_entreprise'] ?? ''));
    $companyPhone = trim((string) ($_POST['telephone_entreprise'] ?? ''));
    $companyPostalBox = trim((string) ($_POST['boite_postale'] ?? ''));
    $adminName = trim((string) ($_POST['nom_complet'] ?? ''));
    $username = trim((string) ($_POST['nom_utilisateur'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['mot_de_passe'] ?? '');
    $passwordConfirmation = (string) ($_POST['confirmation_mot_de_passe'] ?? '');
    $selectedTypes = $_POST['types'] ?? [];

    if (!is_array($selectedTypes)) {
        $selectedTypes = [];
    }
    $selectedTypes = array_values(array_unique(array_filter($selectedTypes, 'is_string')));

    if ($companyName === '' || mb_strlen($companyName) > 150) {
        $errors[] = 'Le nom de l’entreprise est obligatoire et doit contenir 150 caractères maximum.';
    }
    if ($adminName === '' || $username === '' || $email === '') {
        $errors[] = 'Les informations de l’administrateur sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L’adresse email est invalide.';
    }
    if (count($selectedTypes) === 0) {
        $errors[] = 'Sélectionnez au moins un type d’entreprise.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $passwordConfirmation) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    $validTypes = array_column($types, 'code');
    if (array_diff($selectedTypes, $validTypes)) {
        $errors[] = 'Un type d’entreprise sélectionné est invalide.';
    }

    if (!$errors) {
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $companyName), '-'));
        $slug = substr($slug ?: 'entreprise', 0, 160);

        try {
            $pdo->beginTransaction();

            $statement = $pdo->prepare(
                'SELECT 1 FROM utilisateur WHERE Email = ? OR Nom_Utilisateur = ? LIMIT 1'
            );
            $statement->execute([$email, $username]);
            if ($statement->fetchColumn()) {
                throw new RuntimeException('Cet email ou ce nom utilisateur est déjà utilisé.');
            }

            $statement = $pdo->query(
                "SELECT 1 FROM utilisateur WHERE TypeDeCompte = 'Administrateur' LIMIT 1"
            );
            if ($statement->fetchColumn()) {
                throw new RuntimeException('Un compte administrateur existe déjà.');
            }

            $statement = $pdo->prepare(
                'INSERT INTO entreprises (nom, slug, adresse, telephone, boite_postale) VALUES (?, ?, ?, ?, ?)'
            );
            $statement->execute([$companyName, $slug, $companyAddress ?: null, $companyPhone ?: null, $companyPostalBox ?: null]);
            $companyId = (int) $pdo->lastInsertId();

            $statement = $pdo->prepare(
                'INSERT INTO entreprise_types (idEntreprise, type_code) VALUES (?, ?)'
            );
            foreach ($selectedTypes as $typeCode) {
                $statement->execute([$companyId, $typeCode]);
            }

            $statement = $pdo->prepare('SELECT idPlan FROM plans WHERE code = ? AND actif = 1 LIMIT 1');
            $statement->execute(['decouverte']);
            $trialPlanId = (int) $statement->fetchColumn();
            if ($trialPlanId <= 0) {
                throw new RuntimeException('Le plan d’essai est introuvable. Appliquez la migration des abonnements.');
            }
            $statement = $pdo->prepare(
                'INSERT INTO abonnements (idEntreprise, idPlan, statut, periode, date_debut, date_fin)
                 VALUES (?, ?, \'trialing\', \'mensuelle\', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))'
            );
            $statement->execute([$companyId, $trialPlanId]);

            $statement = $pdo->prepare(
                'INSERT INTO utilisateur
                (idEntreprise, Nom_Utilisateur, Email, TypeDeCompte, MotDePasse, NomComplet, Telephone, Adresse, Statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)'
            );
            $statement->execute([
                $companyId,
                $username,
                $email,
                'Administrateur',
                password_hash($password, PASSWORD_DEFAULT),
                $adminName,
                trim((string) ($_POST['telephone'] ?? '')),
                trim((string) ($_POST['adresse'] ?? '')),
            ]);

            $pdo->commit();
            $_SESSION['installation_success'] = 'Compte administrateur créé avec succès. Vous pouvez maintenant vous connecter.';
            header('Location: index.php');
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'Installation impossible. Vérifiez la migration et les contraintes de la base.';
            error_log('Erreur installation SaaS: ' . $exception->getMessage());
        }
    }
}

if ($installationReady && $installationExists && !$success) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Installation de l’entreprise</title>
    <link rel="stylesheet" href="css/install.css">
</head>

<body data-initial-step="<?= $_SERVER['REQUEST_METHOD'] === 'POST' && $errors ? '3' : '1' ?>">
    <main class="installation-shell">
        <aside class="installation-aside">
            <span class="eyebrow">G.S.A.V SaaS</span>
            <h1>Configurez votre espace de travail.</h1>
            <p>Une entreprise, plusieurs activités, un espace adapté à votre métier.</p>
            <ol class="steps-overview">
                <li class="is-current" data-overview-step="1"><span>01</span>Votre entreprise</li>
                <li data-overview-step="2"><span>02</span>Votre activité</li>
                <li data-overview-step="3"><span>03</span>Compte administrateur</li>
            </ol>
        </aside>
        <section class="installation-card">
            <?php foreach ($errors as $error): ?>
            <p class="form-alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
            <?php if ($success): ?>
            <div class="success-state">
                <span class="success-mark">✓</span>
                <p class="eyebrow">Configuration terminée</p>
                <h2>Votre espace est prêt.</h2>
                <p>Le compte administrateur unique a été créé. Vous pouvez maintenant vous connecter.</p>
                <a class="primary-button" href="index.php">Accéder à la connexion</a>
            </div>
            <?php elseif ($installationReady): ?>
            <div class="card-heading">
                <p class="eyebrow">Première configuration</p>
                <h2>Créons votre espace.</h2>
                <p class="step-caption" id="stepCaption">Étape 1 sur 3</p>
            </div>
            <form method="POST" id="installationForm" novalidate>
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <fieldset class="install-step is-visible" data-step="1">
                    <legend>Nom de l’entreprise</legend>
                    <p class="step-help">Donnez un nom à l’espace qui représentera votre entreprise.</p>
                    <label for="nom_entreprise">Nom de l’entreprise</label>
                    <input id="nom_entreprise" name="nom_entreprise" required maxlength="150"
                        value="<?= htmlspecialchars($_POST['nom_entreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autofocus>
                    <label for="adresse_entreprise">Adresse</label>
                    <input id="adresse_entreprise" name="adresse_entreprise" maxlength="255"
                        value="<?= htmlspecialchars($_POST['adresse_entreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <div class="input-row">
                        <div><label for="telephone_entreprise">Téléphone</label><input id="telephone_entreprise"
                                name="telephone_entreprise" maxlength="40"
                                value="<?= htmlspecialchars($_POST['telephone_entreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div><label for="boite_postale">Boîte postale</label><input id="boite_postale"
                                name="boite_postale" maxlength="80"
                                value="<?= htmlspecialchars($_POST['boite_postale'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <button type="button" class="primary-button next-step">Continuer <span>→</span></button>
                </fieldset>

                <fieldset class="install-step" data-step="2" hidden>
                    <legend>Type(s) d’activité</legend>
                    <p class="step-help">Sélectionnez une ou plusieurs activités pour activer les bons outils.</p>
                    <div class="activity-grid">
                        <?php foreach ($types as $type): ?>
                        <label class="activity-option">
                            <input type="checkbox" name="types[]"
                                value="<?= htmlspecialchars($type['code'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= in_array($type['code'], $submittedTypes, true) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($type['libelle'], ENT_QUOTES, 'UTF-8') ?></span>
                            <small>Activer les outils adaptés</small>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="inline-error" id="typesError" hidden>Sélectionnez au moins une activité.</p>
                    <div class="step-actions"><button type="button"
                            class="text-button previous-step">Retour</button><button type="button"
                            class="primary-button next-step">Continuer <span>→</span></button></div>
                </fieldset>

                <fieldset class="install-step" data-step="3" hidden>
                    <legend>Compte administrateur</legend>
                    <p class="step-help">Ce compte unique administrera votre espace et pourra créer les autres
                        utilisateurs.</p>
                    <div class="input-row">
                        <div><label for="nom_complet">Nom complet</label><input id="nom_complet" name="nom_complet"
                                required
                                value="<?= htmlspecialchars($_POST['nom_complet'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div><label for="nom_utilisateur">Nom utilisateur</label><input id="nom_utilisateur"
                                name="nom_utilisateur" required
                                value="<?= htmlspecialchars($_POST['nom_utilisateur'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <label for="email">Email</label><input id="email" type="email" name="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <div class="input-row">
                        <div><label for="mot_de_passe">Mot de passe</label><input id="mot_de_passe" type="password"
                                name="mot_de_passe" minlength="8" required></div>
                        <div><label for="confirmation_mot_de_passe">Confirmation</label><input
                                id="confirmation_mot_de_passe" type="password" name="confirmation_mot_de_passe"
                                minlength="8" required></div>
                    </div>
                    <div class="step-actions"><button type="button"
                            class="text-button previous-step">Retour</button><button type="submit"
                            class="primary-button">Créer mon espace <span>→</span></button></div>
                </fieldset>
            </form>
            <?php endif; ?>
        </section>
    </main>
    <?php if ($installationReady && !$success): ?><script src="js/install.js"></script><?php endif; ?>
</body>

</html>