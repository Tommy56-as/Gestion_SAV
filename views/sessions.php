<section class="sessions-page">
    <header class="header-right">
        <button class="toggle-menu-btn" id="openSidebar">
            <span class="material-icons-sharp">menu</span>
        </button>
    </header>
    <header class="sessions-heading">
        <div>
            <p class="sessions-eyebrow">Administration</p>
            <h1>Sessions utilisateurs</h1>
            <p>Visualisez les comptes actuellement actifs et les revenus générés.</p>
        </div>
        <span class="material-icons-sharp sessions-heading-icon">monitor_heart</span>
    </header>

    <div class="sessions-panel">
        <div class="sessions-toolbar">
            <h2>Sessions ouvertes</h2>
            <button type="button" id="refreshSessions" class="sessions-refresh">
                <span class="material-icons-sharp">refresh</span> Actualiser
            </button>
        </div>
        <div id="sessionsMessage" class="sessions-message" aria-live="polite"></div>
        <div class="sessions-table-wrap">
            <table class="sessions-table">
                <thead><tr><th>Utilisateur</th><th>Compte</th><th>Dernière activité</th><th>IP</th><th>Ventes</th><th>Réparations</th><th>Total généré</th></tr></thead>
                <tbody id="sessionsBody"><tr><td colspan="7">Chargement...</td></tr></tbody>
            </table>
        </div>
    </div>
</section>
<script src="js/sessions.js"></script>
