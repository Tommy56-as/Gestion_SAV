<?php
require_once __DIR__ . '/inc/super_auth.php';
require_super_admin();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Entreprises et abonnements</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="css/super-admin.css">
    <script src="js/icon-fallback.js" defer></script>
</head>

<body>
    <main class="saas-shell">
        <header class="saas-header">
            <div>
                <p class="saas-eyebrow">Console plateforme</p>
                <h1>Gestion SaaS</h1>
                <p>Bienvenue,
                    <?= htmlspecialchars($_SESSION['super_admin_nom'] ?? 'Super utilisateur', ENT_QUOTES, 'UTF-8') ?>.
                </p>
            </div>
            <a class="saas-logout" href="super_logout.php"><span class="material-icons-sharp">logout</span>
                Déconnexion</a>
        </header>
        <nav class="saas-nav" aria-label="Navigation de la plateforme">
            <a class="is-active" href="super_admin.php"><span class="material-icons-sharp">business_center</span> Entreprises et abonnements</a>
            <a href="super_plans.php"><span class="material-icons-sharp">sell</span> Plans et tarifs</a>
        </nav>
        <div id="saasMessage" class="saas-message" role="status"></div>
        <section class="saas-metrics" id="saasMetrics"></section>
        <section class="saas-section">
            <div class="saas-section-heading">
                <div>
                    <p class="saas-eyebrow">Gestion des clients</p>
                    <h2>Entreprises et abonnements</h2>
                    <p class="saas-section-description">Choisissez un plan, une période et une date d’expiration pour chaque entreprise.</p>
                </div><span class="material-icons-sharp">business_center</span>
            </div>
            <div id="companiesList" class="company-list">Chargement...</div>
        </section>
    </main>
    <script src="js/super-admin.js"></script>
</body>

</html>