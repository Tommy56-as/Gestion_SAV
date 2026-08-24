<?php

require_once('inc/Database.php');
$user_id = $_SESSION['user_id']; 

try {
   
    // montant total annuel
    $sql_total = "SELECT SUM(montant) AS total FROM details_vente";
    $stmt = $pdo->query($sql_total);
    $result_total = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $result_total['total'] ?: 0;

    // Comparaison annuelle des commandes d'approvisionnement et des ventes.
    $annee_dashboard = filter_input(INPUT_GET, 'annee', FILTER_VALIDATE_INT);
    $annee_dashboard = ($annee_dashboard >= 2000 && $annee_dashboard <= (int)date('Y'))
        ? $annee_dashboard
        : (int)date('Y');
    $stmt = $pdo->prepare("SELECT
        (SELECT COALESCE(SUM(a.prix_total), 0) FROM approvisionnement a WHERE YEAR(a.date_app) = ? AND statut = 'terminee') AS total_commandes,
        (SELECT COALESCE(SUM(d.montant), 0) FROM vente v INNER JOIN details_vente d ON d.idvente = v.idvente WHERE YEAR(v.date_vente) = ?) AS total_ventes");
    $stmt->execute([$annee_dashboard, $annee_dashboard]);
    $result_annuel = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_commandes_annuel = (float)($result_annuel['total_commandes'] ?? 0);
    $total_ventes_annuel = (float)($result_annuel['total_ventes'] ?? 0);
    $ecart_annuel = $total_ventes_annuel - $total_commandes_annuel;
    $taux_couverture_annuel = $total_commandes_annuel > 0
        ? ($total_ventes_annuel / $total_commandes_annuel) * 100
        : 0;
    
    //montant journalier
    $sql_total_jour = "SELECT SUM(d.montant) AS total FROM vente v 
                       JOIN details_vente d ON v.idvente = d.idvente 
                       WHERE DATE(v.date_vente) = CURDATE()";
    $stmt = $pdo->query($sql_total_jour);
    $result_total_jour = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_jour = $result_total_jour['total'] ?: 0;

    //utilisateurs actifs/bloques
    $sql_user = "SELECT SUM(CASE WHEN Statut = FALSE THEN 1 ELSE 0 END) AS nb_actifs,
                        SUM(CASE WHEN Statut = TRUE  THEN 1 ELSE 0 END) AS nb_bloques
                 FROM utilisateur WHERE supprime = 0";
    $stmt = $pdo->query($sql_user);
    $result_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_actif  = $result_user['nb_actifs'] ?: 0;
    $user_bloque = $result_user['nb_bloques'] ?: 0;

    //reparations en cours - en attente - terminée
    $sql_reparation = "SELECT SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) AS en_cours,
                              SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) AS en_attente,
                              SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) AS terminee
                       FROM reparation";
    $stmt = $pdo->query($sql_reparation);
    $result_reparation = $stmt->fetch(PDO::FETCH_ASSOC);
    $en_cours  = $result_reparation['en_cours'] ?: 0;
    $en_attente = $result_reparation['en_attente'] ?: 0;
    $terminee = $result_reparation['terminee'] ?: 0;

    /* ===== ANALYSE DES PERFORMANCES (PASSÉ) ===== */

    // Ventes des 7 derniers jours
    $ventes_7j = [];
    $stmt = $pdo->query("SELECT DATE(v.date_vente) AS jour, COALESCE(SUM(d.montant), 0) AS total
                         FROM vente v
                         LEFT JOIN details_vente d ON v.idvente = d.idvente
                         WHERE v.date_vente >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                         GROUP BY DATE(v.date_vente)
                         ORDER BY DATE(v.date_vente)");
    $ventes_par_jour = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ventes_indexees = [];
    foreach ($ventes_par_jour as $vj) {
        $ventes_indexees[$vj['jour']] = (float)$vj['total'];
    }
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i day"));
        $ventes_7j[] = [
            'date'  => $date,
            'label' => date('D', strtotime($date)),
            'total' => $ventes_indexees[$date] ?? 0
        ];
    }
    $max_7j = max(array_column($ventes_7j, 'total')) ?: 1;

    // Montant total des réparations enregistrées.
    $stmt = $pdo->query("SELECT COALESCE(SUM(prixTotal), 0) AS total_reparations FROM reparation WHERE statut IN ('terminee')");
    $result_reparations = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_reparations = (float)($result_reparations['total_reparations'] ?? 0);

    // Top 5 produits les plus vendus
    $stmt = $pdo->query("SELECT COALESCE(p.designation) AS designation, COALESCE( p.caracteristique) AS caracteristique, 
                                SUM(d.quantite) AS qte, SUM(montant) AS montant
                         FROM details_vente d
                         LEFT JOIN produit p ON d.idproduit = p.idproduit
                         GROUP BY designation
                         ORDER BY qte DESC
                         LIMIT 5 ;");
    $top_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $max_top = 1;
    foreach ($top_produits as $tp) { $max_top = max($max_top, (int)$tp['qte']); }

    /* ===== ALERTES & PERSPECTIVES (FUTUR) ===== */

    // Alertes de stock (quantité <= quantité minimale)
    $stmt = $pdo->query("SELECT designation, caracteristique, CAST(quantite AS UNSIGNED) AS qte, CAST(quantite_min AS UNSIGNED) AS qte_min, prixUnitaire
                         FROM produit 
                         WHERE CAST(quantite AS UNSIGNED) <= CAST(quantite_min AS UNSIGNED)
                         ORDER BY CAST(quantite AS UNSIGNED) ASC");
    $alertes_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Garanties à venir dans les 30 prochains jours
    $stmt = $pdo->query("SELECT COALESCE(p.designation) AS designation, v.client, v.telephone, d.finGarantie
                         FROM details_vente d INNER JOIN produit p ON d.idvente = p.idproduit
                         INNER JOIN vente v ON v.idvente = d.idvente
                         WHERE d.finGarantie IS NOT NULL
                         AND d.finGarantie BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                         ORDER BY d.finGarantie ASC
                         LIMIT 6 ;");
    $garanties_30j = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Réparations en attente (détail)
    $stmt = $pdo->query("SELECT idrep, nomClient, appareil, telephone, statut
                         FROM reparation
                         WHERE statut = 'en_attente'
                         ORDER BY idrep DESC
                         LIMIT 6 ");
    $reparations_attente = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo ("Erreur: " . $e->getMessage());
    $total = 0;
    $total_jour = 0;
    $annee_dashboard = (int)date('Y');
    $total_commandes_annuel = 0;
    $total_ventes_annuel = 0;
    $ecart_annuel = 0;
    $taux_couverture_annuel = 0;
    $user_actif = 0;
    $user_bloque = 0;
    $en_cours  =  0;
    $en_attente =  0;
    $terminee =  0;
    $ventes_7j = [];
    $max_7j = 1;
    $total_reparations = 0;
    $top_produits = [];
    $max_top = 1;
    $alertes_stock = [];
    $garanties_30j = [];
    $reparations_attente = [];
}

$date_aujourdhui = new DateTime();
$jours_fr = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
$mois_fr_full = ['', 'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$date_fr = $jours_fr[(int)$date_aujourdhui->format('w')] . ' ' . $date_aujourdhui->format('d') . ' ' . $mois_fr_full[(int)$date_aujourdhui->format('n')] . ' ' . $date_aujourdhui->format('Y');

?>
<div class="dash-header">
    <div class="dash-header-left">
        <h1 class="main-title dash-title"><span class="material-icons-sharp">bar_chart</span> Tableau de bord</h1>
        <p class="dash-subtitle">Vue d'ensemble de votre activité en temps réel</p>
    </div>
    <header class="header-right">
        <span class="dash-date-badge">
            <span class="material-icons-sharp">calendar_today</span>
            <?= $date_fr ?>
        </span>
        <button class="toggle-menu-btn" id="openSidebar">
            <span class="material-icons-sharp">menu</span>
        </button>
    </header>
</div>

<!-- Bannière hero -->
<div class="dash-hero dash-animate">
    <div class="dash-hero-content">
        <span class="dash-hero-badge">
            <span class="material-icons-sharp">trending_up</span>
            Chiffre d'affaires global
        </span>
        <p class="dash-hero-title">Total des ventes générées</p>
        <div class="dash-hero-amount"><?= number_format($total, 0, ',', ' ') ?> <small>FCFA</small></div>
        <p class="dash-hero-note">
            <span class="material-icons-sharp">savings</span>
            Dont <?= number_format($total_jour, 0, ',', ' ') ?> FCFA réalisés aujourd'hui
        </p>
    </div>
    <div class="dash-hero-icon">
        <span class="material-icons-sharp">storefront</span>
    </div>
</div>

<!-- Grille de cartes statistiques -->
<div class="dash-grid">
    <div class="dash-card dash-animate accent-yellow">
        <div class="dash-card-top">
            <span class="dash-card-icon material-icons-sharp">bar_chart</span>
            <span class="dash-card-label">Ventes totales</span>
        </div>
        <div class="dash-card-value"><?= number_format($total, 0, ',', ' ') ?> <small>FCFA</small></div>
        <div class="dash-card-footer">
            <span class="dash-card-trend">
                <span class="material-icons-sharp">trending_up</span>
                Cumul
            </span>
            <span class="dash-card-sub">Toutes périodes</span>
        </div>
    </div>

    <div class="dash-card dash-animate accent-fuscha">
        <div class="dash-card-top">
            <span class="dash-card-icon material-icons-sharp">currency_franc</span>
            <span class="dash-card-label">Ventes journalières</span>
        </div>
        <div class="dash-card-value"><?= number_format($total_jour, 0, ',', ' ') ?> <small>FCFA</small></div>
        <div class="dash-card-footer">
            <span class="dash-card-trend">
                <span class="material-icons-sharp">today</span>
                Aujourd'hui
            </span>
            <span class="dash-card-sub"><?= $date_aujourdhui->format('d/m/Y') ?></span>
        </div>
    </div>

    <div class="dash-card dash-animate accent-success dash-annual-card">
        <div class="dash-card-top">
            <span class="dash-card-icon material-icons-sharp">compare_arrows</span>
            <span class="dash-card-label">Commandes annuelles</span>
        </div>
        <form class="dash-year-form" method="get">
            <input type="hidden" name="page" value="dashboard">
            <label for="anneeDashboard">Année</label>
            <select id="anneeDashboard" name="annee" onchange="this.form.submit()">
                <?php for ($annee = (int)date('Y'); $annee >= 2020; $annee--): ?>
                <option value="<?= $annee ?>" <?= $annee === $annee_dashboard ? 'selected' : '' ?>><?= $annee ?>
                </option>
                <?php endfor; ?>
            </select>
        </form>
        <div class="dash-annual-values">
            <div><span>Commandes</span><strong><?= number_format($total_commandes_annuel, 0, ',', ' ') ?>
                    <small>FCFA</small></strong></div>
            <div><span>Ventes</span><strong><?= number_format($total_ventes_annuel, 0, ',', ' ') ?>
                    <small>FCFA</small></strong></div>
        </div>
        <div class="dash-card-footer">
            <span class="dash-card-trend <?= $ecart_annuel >= 0 ? 'annual-positive' : 'annual-negative' ?>">
                <span class="material-icons-sharp"><?= $ecart_annuel >= 0 ? 'trending_up' : 'trending_down' ?></span>
                Écart <?= $ecart_annuel >= 0 ? '+' : '' ?><?= number_format($ecart_annuel, 0, ',', ' ') ?> FCFA
            </span>
            <span class="dash-card-sub"><?= number_format($taux_couverture_annuel, 1, ',', ' ') ?>% couverture</span>
        </div>
    </div>

    <div class="dash-card dash-animate accent-cyan">
        <div class="dash-card-top">
            <span class="dash-card-icon material-icons-sharp">person_add</span>
            <span class="dash-card-label">Utilisateurs actifs</span>
        </div>
        <div class="dash-card-value"><?= $user_actif ?> <small>utilisateur(s)</small></div>
        <div class="dash-card-footer">
            <span class="dash-card-trend">
                <span class="material-icons-sharp">verified_user</span>
                En service
            </span>
            <span class="dash-card-sub">Comptes actifs</span>
        </div>
    </div>

    <div class="dash-card dash-animate accent-danger">
        <div class="dash-card-top">
            <span class="dash-card-icon material-icons-sharp">person_add_disabled</span>
            <span class="dash-card-label">Utilisateurs bloqués</span>
        </div>
        <div class="dash-card-value"><?= $user_bloque ?> <small>utilisateur(s)</small></div>
        <div class="dash-card-footer">
            <span class="dash-card-trend">
                <span class="material-icons-sharp">block</span>
                Suspendu
            </span>
            <span class="dash-card-sub">Comptes inactifs</span>
        </div>
    </div>

    <div class="dash-card dash-animate accent-fuscha">
        <div class="dash-card-top">
            <span class="dash-card-icon material-icons-sharp">schedule</span>
            <span class="dash-card-label">Réparations en attente</span>
        </div>
        <div class="dash-card-value"><?= $en_attente ?> <small>réparation(s)</small></div>
        <div class="dash-card-footer">
            <span class="dash-card-trend">
                <span class="material-icons-sharp">hourglass_empty</span>
                En attente
            </span>
            <span class="dash-card-sub">Non démarrées</span>
        </div>
    </div>

    <div class="dash-card dash-animate accent-cyan">
        <div class="dash-card-top">
            <span class="dash-card-icon material-icons-sharp">build</span>
            <span class="dash-card-label">Réparations en cours</span>
        </div>
        <div class="dash-card-value"><?= $en_cours ?> <small>réparation(s)</small></div>
        <div class="dash-card-footer">
            <span class="dash-card-trend">
                <span class="material-icons-sharp">autorenew</span>
                En cours
            </span>
            <span class="dash-card-sub">Atelier</span>
        </div>
    </div>

    <div class="dash-card dash-animate accent-success">
        <div class="dash-card-top">
            <span class="dash-card-icon material-icons-sharp">check_circle</span>
            <span class="dash-card-label">Réparations terminées</span>
        </div>
        <div class="dash-card-value"><?= $terminee ?> <small>réparation(s)</small></div>
        <div class="dash-card-footer">
            <span class="dash-card-trend">
                <span class="material-icons-sharp">task_alt</span>
                Terminées
            </span>
            <span class="dash-card-sub">Finalisées</span>
        </div>
    </div>
</div>

<!-- ============================================================
     ANALYSE DES PERFORMANCES (ACTIVITÉS PASSÉES)
============================================================ -->
<div class="dash-analytics dash-animate">

    <!-- Évolution des ventes sur 7 jours -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="dash-panel-title">
                <span class="dash-panel-icon accent-fuscha material-icons-sharp">money</span>
                <div>
                    <h3>Évolution des ventes</h3>
                    <p>Chiffre d'affaires des 7 derniers jours</p>
                </div>
            </div>
            <span
                class="dash-panel-total accent-fuscha"><?= number_format(array_sum(array_column($ventes_7j, 'total')), 0, ',', ' ') ?>
                FCFA</span>
        </div>
        <div class="dash-chart">
            <?php foreach ($ventes_7j as $v): ?>
            <?php $h = $max_7j > 0 ? round(($v['total'] / $max_7j) * 100) : 0; ?>
            <div class="dash-chart-col">
                <div class="dash-chart-bar-wrap">
                    <div class="dash-chart-bar accent-fuscha" style="height: <?= max($h, 3) ?>%;"
                        title="<?= number_format($v['total'], 0, ',', ' ') ?> FCFA"></div>
                </div>
                <span
                    class="dash-chart-value"><?= $v['total'] >= 1000 ? round($v['total']/1000, 1).'k' : $v['total'] ?></span>
                <span class="dash-chart-label"><?= $v['label'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Montant total des réparations -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="dash-panel-title">
                <span class="dash-panel-icon accent-success material-icons-sharp">build</span>
                <div>
                    <h3>Total des réparations</h3>
                    <p>Montant cumulé des réparations</p>
                </div>
            </div>
            <span class="dash-panel-total accent-success">Réparations</span>
        </div>
        <div class="dash-repair-total">
            <span class="material-icons-sharp">payments</span>
            <strong><?= number_format($total_reparations, 0, ',', ' ') ?> <small>FCFA</small></strong>
        </div>
    </div>

    <!-- Top 5 produits les plus vendus -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="dash-panel-title">
                <span class="dash-panel-icon accent-yellow material-icons-sharp">star</span>
                <div>
                    <h3>Top 5 produits</h3>
                    <p>Les produits les plus vendus</p>
                </div>
            </div>
        </div>
        <div class="dash-top-list">
            <?php if (empty($top_produits)): ?>
            <p class="dash-empty">Aucune vente enregistrée pour le moment.</p>
            <?php else: ?>
            <?php $rank = 1; ?>
            <?php foreach ($top_produits as $tp): ?>
            <?php $pct = (int)$tp['qte'] > 0 ? round(((int)$tp['qte'] / $max_top) * 100) : 0; ?>
            <div class="dash-top-item">
                <span class="dash-rank <?= $rank <= 3 ? 'top' : '' ?>"><?= $rank ?></span>
                <div class="dash-top-info">
                    <span class="dash-top-name"><?= htmlspecialchars($tp['designation']) ?> -
                        <?= htmlspecialchars($tp['caracteristique']) ?> </span>
                    <div class="dash-top-bar">
                        <div class="dash-top-fill accent-yellow" style="width: <?= max($pct, 4) ?>%;"></div>
                    </div>
                </div>
                <span class="dash-top-qte"><?= $tp['qte'] ?> <small>vendus</small></span>
            </div>
            <?php $rank++; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============================================================
     ALERTES & PERSPECTIVES (ACTIVITÉS FUTURES)
============================================================ -->
<div class="dash-analytics dash-animate">

    <!-- Alertes de stock -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="dash-panel-title">
                <span class="dash-panel-icon accent-danger material-icons-sharp">warning</span>
                <div>
                    <h3>Alertes de stock</h3>
                    <p>Produits sous le seuil minimum</p>
                </div>
            </div>
            <span class="dash-panel-total accent-danger"><?= count($alertes_stock) ?> alerte(s)</span>
        </div>
        <div class="dash-alert-list">
            <?php if (empty($alertes_stock)): ?>
            <p class="dash-empty">Aucun produit sous le seuil. Stock sain.</p>
            <?php else: ?>
            <?php foreach ($alertes_stock as $as): ?>
            <?php $rupture = (int)$as['qte'] <= 0; ?>
            <div class="dash-alert-item">
                <span class="dash-alert-icon <?= $rupture ? 'rupture' : 'faible' ?> material-icons-sharp">
                    <?= $rupture ? 'block' : 'inventory_2' ?>
                </span>
                <div class="dash-alert-info">
                    <span class="dash-alert-name">
                        <?= htmlspecialchars($as['designation']) ?> -
                        <? htmlspecialchars($as['caracteristique']) ?>
                    </span>
                    <span class="dash-alert-detail">Stock : <?= $as['qte'] ?> · Seuil min : <?= $as['qte_min'] ?></span>
                </div>
                <span class="dash-alert-badge <?= $rupture ? 'rupture' : 'faible' ?>">
                    <?= $rupture ? 'Rupture' : 'Stock faible' ?>
                </span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Garanties à venir -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="dash-panel-title">
                <span class="dash-panel-icon accent-yellow material-icons-sharp">shield</span>
                <div>
                    <h3>Garanties à venir</h3>
                    <p>Expiration dans les 30 prochains jours</p>
                </div>
            </div>
        </div>
        <div class="dash-warranty-list">
            <?php if (empty($garanties_30j)): ?>
            <p class="dash-empty">Aucune garantie n'expire dans les 30 prochains jours.</p>
            <?php else: ?>
            <?php foreach ($garanties_30j as $g): ?>
            <?php $j_restants = (int)((strtotime($g['finGarantie']) - strtotime(date('Y-m-d'))) / 86400); ?>
            <div class="dash-warranty-item">
                <div class="dash-warranty-info">
                    <span class="dash-warranty-name"><?= htmlspecialchars($g['client']) ?></span>
                    <span class="dash-warranty-detail"><?= htmlspecialchars($g['designation']) ?></span>
                </div>
                <span class="dash-warranty-date <?= $j_restants <= 7 ? 'urgent' : '' ?>">
                    <span class="material-icons-sharp">event</span>
                    <?= date('d/m/Y', strtotime($g['finGarantie'])) ?>
                </span>
                <span class="dash-warranty-days <?= $j_restants <= 7 ? 'urgent' : '' ?>">
                    J-<?= $j_restants ?>
                </span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Réparations en attente de traitement -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="dash-panel-title">
                <span class="dash-panel-icon accent-cyan material-icons-sharp">build_circle</span>
                <div>
                    <h3>Réparations à traiter</h3>
                    <p>Appareils en attente de diagnostic</p>
                </div>
            </div>
            <span class="dash-panel-total accent-cyan"><?= count($reparations_attente) ?></span>
        </div>
        <div class="dash-repair-list">
            <?php if (empty($reparations_attente)): ?>
            <p class="dash-empty">Aucune réparation en attente.</p>
            <?php else: ?>
            <?php foreach ($reparations_attente as $r): ?>
            <div class="dash-repair-item">
                <span class="dash-repair-icon material-icons-sharp">devices</span>
                <div class="dash-repair-info">
                    <span class="dash-repair-name"><?= htmlspecialchars($r['nomClient']) ?></span>
                    <span class="dash-repair-detail"><?= htmlspecialchars($r['appareil']) ?></span>
                </div>
                <span class="dash-repair-tel">
                    <span class="material-icons-sharp">phone</span>
                    <?= htmlspecialchars($r['telephone']) ?>
                </span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>
</div>