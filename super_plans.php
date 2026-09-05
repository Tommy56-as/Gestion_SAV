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
    <title>Plans et tarifs</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="css/super-admin.css">
    <script src="js/icon-fallback.js" defer></script>
</head>
<body>
    <main class="saas-shell">
        <header class="saas-header">
            <div><p class="saas-eyebrow">Catalogue commercial</p><h1>Plans et tarifs</h1><p>Créez et ajustez les offres proposées aux entreprises.</p></div>
            <a class="saas-logout" href="super_logout.php"><span class="material-icons-sharp">logout</span> Déconnexion</a>
        </header>
        <nav class="saas-nav" aria-label="Navigation de la plateforme">
            <a href="super_admin.php"><span class="material-icons-sharp">business_center</span> Entreprises et abonnements</a>
            <a class="is-active" href="super_plans.php"><span class="material-icons-sharp">sell</span> Plans et tarifs</a>
        </nav>
        <div id="saasMessage" class="saas-message" role="status"></div>
        <section class="saas-section">
            <div class="saas-section-heading"><div><p class="saas-eyebrow">Nouvelle offre</p><h2>Créer un plan</h2><p class="saas-section-description">Définissez le nom et les tarifs mensuel et annuel.</p></div><span class="material-icons-sharp">add_business</span></div>
            <form id="createPlanForm" class="create-plan-form">
                <div><label for="planCode">Code interne</label><input id="planCode" name="code" placeholder="ex. premium" pattern="[a-z0-9_-]{2,40}" required></div>
                <div><label for="planName">Nom affiché</label><input id="planName" name="nom" placeholder="ex. Premium" maxlength="100" required></div>
                <div class="create-plan-description"><label for="planDescription">Description</label><input id="planDescription" name="description" placeholder="Ce que comprend cette offre"></div>
                <div><label for="planMonthly">Prix mensuel</label><input id="planMonthly" name="prix_mensuel" type="number" min="0" step="1" placeholder="FCFA" required></div>
                <div><label for="planAnnual">Prix annuel</label><input id="planAnnual" name="prix_annuel" type="number" min="0" step="1" placeholder="FCFA" required></div>
                <button type="submit"><span class="material-icons-sharp">add</span> Ajouter le plan</button>
            </form>
        </section>
        <section class="saas-section">
            <div class="saas-section-heading"><div><p class="saas-eyebrow">Offres disponibles</p><h2>Catalogue des plans</h2></div><span class="material-icons-sharp">sell</span></div>
            <div id="plansList" class="plans-list">Chargement...</div>
        </section>
    </main>
    <script src="js/super-admin.js"></script>
</body>
</html>