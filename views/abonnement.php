<?php
if (!function_exists('is_admin') || !is_admin()) {
    exit;
}

$subscription = current_subscription($pdo);
$subscriptionActive = $subscription ? subscription_is_active($subscription) : false;
$daysRemaining = $subscription
    ? max(0, (int) (new DateTimeImmutable('today'))->diff(new DateTimeImmutable($subscription['date_fin']))->format('%r%a'))
    : 0;
$subscriptionPrice = $subscription
    ? ($subscription['periode'] === 'annuelle' ? $subscription['prix_annuel'] : $subscription['prix_mensuel'])
    : 0;
$subscriptionDuration = $subscription
    ? max(1, (int) (new DateTimeImmutable($subscription['date_debut']))->diff(new DateTimeImmutable($subscription['date_fin']))->format('%a'))
    : 30;
$subscriptionProgress = $subscription
    ? min(100, max(0, ($daysRemaining / $subscriptionDuration) * 100))
    : 0;
$availablePlans = $pdo->query(
    'SELECT idPlan, code, nom, description, prix_mensuel, prix_annuel
     FROM plans WHERE actif = 1 ORDER BY prix_mensuel, nom'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="settings-page subscription-page">
    <header class="header-right">
        <button class="toggle-menu-btn" id="openSidebar">
            <span class="material-icons-sharp">menu</span>
        </button>
    </header>
    <div class="settings-heading">
        <div>
            <p class="settings-eyebrow">Espace SaaS</p>
            <h1>Mon abonnement</h1>
            <p>Consultez le plan et la période active de votre entreprise.</p>
        </div>
        <span class="material-icons-sharp settings-heading-icon">workspace_premium</span>
    </div>
    <section class="subscription-card">
        <?php if (!$subscription): ?>
        <div class="subscription-empty">
            <span class="material-icons-sharp">cloud_off</span>
            <h2>Aucun abonnement actif</h2>
            <p>Aucun abonnement n’est associé à cette entreprise.</p>
        </div>
        <?php else: ?>
        <div class="subscription-overview">
            <div class="subscription-plan-mark"><span class="material-icons-sharp">workspace_premium</span></div>
            <div class="subscription-plan-copy">
                <div class="subscription-status <?= $subscriptionActive ? 'is-active' : 'is-expired' ?>">
                    <span class="material-icons-sharp"><?= $subscriptionActive ? 'verified' : 'warning' ?></span>
                    <?= $subscriptionActive ? 'Abonnement actif' : 'Abonnement expiré' ?>
                </div>
                <h2><?= htmlspecialchars($subscription['plan_nom'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($subscription['plan_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="subscription-price">
                <strong><?= number_format((float) $subscriptionPrice, 0, ',', ' ') ?> FCFA</strong>
                <span>/
                    <?= htmlspecialchars($subscription['periode'] === 'annuelle' ? 'an' : 'mois', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
        <div class="subscription-expiry <?= $daysRemaining <= 7 ? 'is-warning' : '' ?>">
            <span class="material-icons-sharp"><?= $daysRemaining <= 7 ? 'warning' : 'event_available' ?></span>
            <?= $daysRemaining === 0 ? 'Votre abonnement expire aujourd’hui.' : ($daysRemaining <= 7 ? 'Votre abonnement expire bientôt.' : 'Votre abonnement est à jour.') ?>
        </div>
        <div class="subscription-progress" aria-label="Durée restante de l’abonnement">
            <div><span>Durée restante</span><strong><?= $daysRemaining ?>
                    jour<?= $daysRemaining > 1 ? 's' : '' ?></strong></div>
            <span class="subscription-progress-track"><span
                    style="width: <?= $subscriptionProgress ?>%"></span></span>
        </div>
        <dl class="subscription-details">
            <div><span class="material-icons-sharp">event</span>
                <dt>Début</dt>
                <dd><?= htmlspecialchars($subscription['date_debut'], ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div><span class="material-icons-sharp">event_available</span>
                <dt>Expiration</dt>
                <dd><?= htmlspecialchars($subscription['date_fin'], ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
            <div><span class="material-icons-sharp">autorenew</span>
                <dt>Facturation</dt>
                <dd><?= htmlspecialchars(ucfirst($subscription['periode']), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
        </dl>
        <div class="subscription-note"><span class="material-icons-sharp">info</span>
            <p>Le changement de plan et le paiement sont gérés depuis l’espace super utilisateur.</p>
        </div>
        <?php endif; ?>
    </section>

    <section class="subscription-catalog">
        <div class="subscription-catalog-heading">
            <div>
                <p class="settings-eyebrow">Évolution de votre espace</p>
                <h2>Plans et tarifs disponibles</h2>
                <p>Comparez les offres proposées par la plateforme.</p>
            </div>
            <span class="material-icons-sharp">sell</span>
        </div>
        <div class="subscription-plan-grid">
            <?php foreach ($availablePlans as $plan): ?>
            <article class="subscription-plan-card <?= $subscription && (int) $subscription['idPlan'] === (int) $plan['idPlan'] ? 'is-current' : '' ?>">
                <?php if ($subscription && (int) $subscription['idPlan'] === (int) $plan['idPlan']): ?>
                <span class="subscription-current-label">Votre plan actuel</span>
                <?php endif; ?>
                <span class="material-icons-sharp subscription-plan-icon">workspace_premium</span>
                <h3><?= htmlspecialchars($plan['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($plan['description'] ?: 'Une offre adaptée à votre activité.', ENT_QUOTES, 'UTF-8') ?></p>
                <div class="subscription-plan-prices">
                    <div><strong><?= number_format((float) $plan['prix_mensuel'], 0, ',', ' ') ?> FCFA</strong><span>par mois</span></div>
                    <div><strong><?= number_format((float) $plan['prix_annuel'], 0, ',', ' ') ?> FCFA</strong><span>par an</span></div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>