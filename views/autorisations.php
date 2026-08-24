<?php if (!function_exists('is_admin') || !is_admin()) { exit; } ?>
<section class="permissions-page">
    <header class="permissions-heading">
        <div>
            <p class="permissions-eyebrow">Administration</p>
            <h1>Autorisations utilisateurs</h1>
            <p>Définissez précisément les actions accessibles à chaque caissier et technicien.</p>
        </div>
        <span class="material-icons-sharp permissions-heading-icon">admin_panel_settings</span>
    </header>
    <header class="header-right">
        <button class="toggle-menu-btn" id="openSidebar">
            <span class="material-icons-sharp">menu</span>
        </button>
    </header>
    <div class="permissions-panel">
        <div id="permissionsMessage" class="permissions-message" aria-live="polite"></div>
        <div id="permissionsList">Chargement des utilisateurs...</div>
    </div>

    <div class="revenue-panel">
        <div class="revenue-heading">
            <div>
                <h2>Revenus générés</h2>
                <p>Suivi des ventes par caissier et des réparations par technicien.</p>
            </div>
            <span class="material-icons-sharp">query_stats</span>
        </div>
        <div class="revenue-grid" id="revenueList">Chargement des revenus...</div>
    </div>
</section>
<script src="js/permissions.js"></script>