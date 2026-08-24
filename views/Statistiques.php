<?php
require_once('inc/DataBase.php');

/* ============================================================
   ANALYSE DES VENTES - Récupération des données réelles
============================================================ */
$mois_fr = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$annee_courante = date('Y');

try {
    // --- Ventes mensuelles de l'année courante ---
    $ventes_mensuelles = array_fill(1, 12, 0);
    $stmt = $pdo->prepare("SELECT MONTH(v.date_vente) AS mois, COALESCE(SUM(d.montant),0) AS total
                           FROM vente v
                           LEFT JOIN details_vente d ON v.idvente = d.idvente
                           WHERE YEAR(v.date_vente) = :annee
                           GROUP BY MONTH(v.date_vente)
                           ORDER BY MONTH(v.date_vente)");
    $stmt->execute(['annee' => $annee_courante]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $ventes_mensuelles[(int)$row['mois']] = (float)$row['total'];
    }

    // --- Total annuel ---
    $total_annuel = array_sum($ventes_mensuelles);

    // --- Meilleur mois ---
    $max_mois = max($ventes_mensuelles);
    $meilleur_mois = $max_mois > 0 ? array_search($max_mois, $ventes_mensuelles) : 0;

    // --- Moyenne mensuelle ---
    $nb_mois_avec_ventes = count(array_filter($ventes_mensuelles, function($v){ return $v > 0; }));
    $moyenne_mensuelle = $nb_mois_avec_ventes > 0 ? $total_annuel / $nb_mois_avec_ventes : 0;

    // --- Nombre de produits ---
    $nb_produits = (int)$pdo->query("SELECT COUNT(*) FROM produit")->fetchColumn();

    // --- Nombre de réparations ---
    $nb_reparations = (int)$pdo->query("SELECT COUNT(*) FROM reparation")->fetchColumn();

    // --- Nombre de clients (ventes distinctes) ---
    $nb_clients = (int)$pdo->query("SELECT COUNT(DISTINCT client) FROM vente")->fetchColumn();

    // --- Top 5 produits les plus vendus ---
    $top_produits = $pdo->query("SELECT COALESCE(p.designation) AS designation,
                                        COALESCE(p.caracteristique) AS caracteristique,
                                        SUM(d.quantite) AS qte,
                                        SUM(d.montant) AS montant
                                 FROM details_vente d INNER JOIN produit p
                                 WHERE d.idproduit = p.idproduit
                                 GROUP BY p.designation, p.caracteristique
                                 ORDER BY qte DESC
                                 LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // --- Réparations par statut ---
    $repa_statut = $pdo->query("SELECT statut, COUNT(*) AS nb FROM reparation GROUP BY statut")->fetchAll(PDO::FETCH_ASSOC);
    $map_statut = [];
    foreach ($repa_statut as $rs) { $map_statut[$rs['statut']] = (int)$rs['nb']; }
    $nb_repa_attente  = $map_statut['en_attente'] ?? 0;
    $nb_repa_cours    = $map_statut['en_cours'] ?? 0;
    $nb_repa_terminee = $map_statut['terminee'] ?? 0;

    /* ===== VENTES DES 7 DERNIERS JOURS (comparaison hebdomadaire) ===== */
    $ventes_7j = [];
    $stmt = $pdo->query("SELECT DATE(v.date_vente) AS jour, COALESCE(SUM(d.montant), 0) AS total
                         FROM vente v
                         LEFT JOIN details_vente d ON v.idvente = d.idvente
                         WHERE v.date_vente >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
                         GROUP BY DATE(v.date_vente)
                         ORDER BY DATE(v.date_vente)");
    $ventes_par_jour = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ventes_indexees = [];
    foreach ($ventes_par_jour as $vj) {
        $ventes_indexees[$vj['jour']] = (float)$vj['total'];
    }
    // 14 derniers jours (2 semaines) pour comparer
    $semaine_courante = [];
    $semaine_precedente = [];
    for ($i = 13; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i day"));
        $val = $ventes_indexees[$date] ?? 0;
        if ($i >= 7) {
            $semaine_precedente[] = $val;
        } else {
            $semaine_courante[] = $val;
        }
    }
    $labels_semaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    $total_semaine_courante = array_sum($semaine_courante);
    $total_semaine_precedente = array_sum($semaine_precedente);
    $evolution_semaine = $total_semaine_precedente > 0 ? round((($total_semaine_courante - $total_semaine_precedente) / $total_semaine_precedente) * 100) : 0;

} catch(PDOException $e) {
    $ventes_mensuelles = array_fill(1, 12, 0);
    $total_annuel = 0;
    $meilleur_mois = 0;
    $moyenne_mensuelle = 0;
    $nb_produits = 0;
    $nb_reparations = 0;
    $nb_clients = 0;
    $top_produits = [];
    $nb_repa_attente = 0;
    $nb_repa_cours = 0;
    $nb_repa_terminee = 0;
    $semaine_courante = [0,0,0,0,0,0,0];
    $semaine_precedente = [0,0,0,0,0,0,0];
    $total_semaine_courante = 0;
    $total_semaine_precedente = 0;
    $evolution_semaine = 0;
}

// --- Progression mensuelle (vs mois précédent) pour le tableau ---
$progressions = [];
foreach ($ventes_mensuelles as $m => $v) {
    if ($m == 1) {
        $progressions[$m] = null;
    } else {
        $prev = $ventes_mensuelles[$m - 1];
        $progressions[$m] = $prev > 0 ? round((($v - $prev) / $prev) * 100) : null;
    }
}

// --- Données JSON pour Chart.js ---
// array_slice($mois_fr, 1) retire le premier élément vide (index 0) pour
// aligner les 12 labels (Janvier..Décembre) avec les 12 valeurs de $ventes_mensuelles
$labels_json = json_encode(array_slice($mois_fr, 1));
$data_json = json_encode(array_values($ventes_mensuelles));
$semaine_courante_json = json_encode($semaine_courante);
$semaine_precedente_json = json_encode($semaine_precedente);
$labels_semaine_json = json_encode($labels_semaine);

// Couleurs doughnut pour top 5
$doughnut_colors = [
    'var(--fuscha)', 'var(--cyan)', 'var(--yellow)', 'var(--desaturate-fuscha-2)', 'var(--primary)'
];
$top_labels_json = json_encode(array_map(function($p){ return $p['designation']; }, $top_produits));
$top_data_json = json_encode(array_map(function($p){ return (int)$p['qte']; }, $top_produits));
$doughnut_colors_json = json_encode($doughnut_colors);

$date_aujourdhui = new DateTime();
$jours_fr = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
$date_fr = $jours_fr[(int)$date_aujourdhui->format('w')] . ' ' . $date_aujourdhui->format('d') . ' ' . $mois_fr[(int)$date_aujourdhui->format('n')] . ' ' . $date_aujourdhui->format('Y');
?>

<div class="graphe-header">
    <h1><span class="material-icons-sharp">bar_chart</span> Statistiques des ventes <?= $annee_courante ?></h1>
    <p class="header-subtitle">Analyse détaillée des performances commerciales de l'année</p>
    <div class="header-badge">
        <span class="material-icons-sharp">calendar_today</span>
        Données mises à jour : <?= $date_fr ?>
    </div>
</div>
<header class="header-right">
    <button class="toggle-menu-btn" id="openSidebar">
        <span class="material-icons-sharp">menu</span>
    </button>
</header>
<!-- Cartes KPI -->
<div class="graphe-stats">
    <div class="graphe-stat-card accent-fuscha graphe-animate">
        <div class="stat-header">
            <div>
                <div class="stat-title">Ventes totales <?= $annee_courante ?></div>
                <div class="stat-value"><?= number_format($total_annuel, 0, ',', ' ') ?> FCFA</div>
            </div>
            <div class="stat-icon"><span class="material-icons-sharp">payments</span></div>
        </div>
        <div class="stat-trend trend-up"><span class="material-icons-sharp">trending_up</span> Cumul annuel</div>
    </div>

    <div class="graphe-stat-card accent-cyan graphe-animate">
        <div class="stat-header">
            <div>
                <div class="stat-title">Meilleur mois</div>
                <div class="stat-value"><?= $meilleur_mois ? $mois_fr[$meilleur_mois] : '—' ?></div>
            </div>
            <div class="stat-icon"><span class="material-icons-sharp">emoji_events</span></div>
        </div>
        <div class="stat-trend trend-up"><span class="material-icons-sharp">arrow_upward</span>
            <?= number_format($max_mois, 0, ',', ' ') ?> FCFA</div>
    </div>

    <div class="graphe-stat-card accent-yellow graphe-animate">
        <div class="stat-header">
            <div>
                <div class="stat-title">Moyenne mensuelle</div>
                <div class="stat-value"><?= number_format($moyenne_mensuelle, 0, ',', ' ') ?> FCFA</div>
            </div>
            <div class="stat-icon"><span class="material-icons-sharp">calculate</span></div>
        </div>
        <div class="stat-trend trend-up"><span class="material-icons-sharp">calendar_month</span>
            <?= max($nb_mois_avec_ventes, 1) ?> mois actifs</div>
    </div>

    <div class="graphe-stat-card accent-success graphe-animate">
        <div class="stat-header">
            <div>
                <div class="stat-title">Catalogue</div>
                <div class="stat-value"><?= $nb_produits ?> produits</div>
            </div>
            <div class="stat-icon"><span class="material-icons-sharp">inventory_2</span></div>
        </div>
        <div class="stat-trend trend-up"><span class="material-icons-sharp">group</span> <?= $nb_clients ?> clients
        </div>
    </div>
</div>

<!-- ===== GRAPHIQUE 1 : Évolution mensuelle ===== -->
<section class="graphe-chart-section graphe-animate">
    <div class="section-header">
        <div>
            <h2 class="section-title"><span class="material-icons-sharp">trending_up</span> Évolution des ventes
                mensuelles <?= $annee_courante ?></h2>
            <p class="section-subtitle">Progression de votre chiffre d'affaires mois par mois</p>
        </div>
        <div class="graphe-legend">
            <div class="graphe-legend-item">
                <div class="graphe-legend-dot" style="background: linear-gradient(135deg, var(--fuscha), var(--cyan));">
                </div>
                <span>Ventes réelles (FCFA)</span>
            </div>
        </div>
    </div>

    <div class="graphe-chart-wrap">
        <canvas id="ventesChart"></canvas>
    </div>

    <!-- Analyse rapide -->
    <div class="graphe-quick">
        <div class="graphe-quick-card">
            <div class="quick-icon blue"><span class="material-icons-sharp">arrow_upward</span></div>
            <div>
                <p class="quick-title">Période forte</p>
                <p class="quick-value"><?= $meilleur_mois ? $mois_fr[$meilleur_mois] : '—' ?></p>
            </div>
        </div>
        <div class="graphe-quick-card">
            <div class="quick-icon green"><span class="material-icons-sharp">build</span></div>
            <div>
                <p class="quick-title">Réparations</p>
                <p class="quick-value"><?= $nb_reparations ?> au total</p>
            </div>
        </div>
        <div class="graphe-quick-card">
            <div class="quick-icon pink"><span class="material-icons-sharp">insights</span></div>
            <div>
                <p class="quick-title">Tendance</p>
                <p class="quick-value"><?= $total_annuel > 0 ? 'de croissance' : 'à établir' ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ===== GRAPHIQUE 2 : Comparaison hebdomadaire ===== -->
<section class="graphe-chart-section graphe-animate">
    <div class="section-header">
        <div>
            <h2 class="section-title"><span class="material-icons-sharp">date_range</span> Comparaison des semaines</h2>
            <p class="section-subtitle">Ventes des 7 derniers jours vs les 7 jours précédents</p>
        </div>
        <span class="graphe-hebdo-total <?= $evolution_semaine >= 0 ? 'up' : 'down' ?>">
            <span class="material-icons-sharp"><?= $evolution_semaine >= 0 ? 'trending_up' : 'trending_down' ?></span>
            <?= $evolution_semaine >= 0 ? '+' : '' ?><?= $evolution_semaine ?>%
        </span>
    </div>

    <div class="graphe-chart-wrap">
        <canvas id="semaineChart"></canvas>
    </div>

    <div class="graphe-hebdo-summary">
        <div class="graphe-hebdo-item">
            <span class="graphe-hebdo-dot semaine-courante"></span>
            <span class="graphe-hebdo-label">Cette semaine</span>
            <span class="graphe-hebdo-value"><?= number_format($total_semaine_courante, 0, ',', ' ') ?> FCFA</span>
        </div>
        <div class="graphe-hebdo-item">
            <span class="graphe-hebdo-dot semaine-precedente"></span>
            <span class="graphe-hebdo-label">Semaine précédente</span>
            <span class="graphe-hebdo-value"><?= number_format($total_semaine_precedente, 0, ',', ' ') ?> FCFA</span>
        </div>
    </div>
</section>

<!-- ===== GRAPHIQUE 3 : Top 5 produits (circulaire) + répartitions ===== -->
<section class="graphe-chart-section graphe-animate">
    <div class="section-header">
        <div>
            <h2 class="section-title"><span class="material-icons-sharp">analytics</span> Répartition des ventes</h2>
            <p class="section-subtitle">Top 5 produits les plus vendus et état des réparations</p>
        </div>
    </div>

    <div class="graphe-double">
        <!-- Doughnut top 5 produits -->
        <div class="graphe-double-col">
            <h3 class="graphe-mini-title"><span class="material-icons-sharp">star</span> Top 5 produits vendus</h3>
            <div class="graphe-chart-wrap-small">
                <canvas id="topProduitsChart"></canvas>
            </div>
            <div class="graphe-doughnut-legend">
                <?php foreach ($top_produits as $i => $tp): ?>
                <div class="graphe-doughnut-item">
                    <span class="graphe-doughnut-dot" style="background-color: <?= $doughnut_colors[$i] ?>;"></span>
                    <span class="graphe-doughnut-name"><?= htmlspecialchars($tp['designation']) ?></span>
                    <span class="graphe-doughnut-qte"><?= $tp['qte'] ?> vendus</span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($top_produits)): ?>
                <p class="graphe-empty">Aucune vente enregistrée.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Répartitions réparations + statuts -->
        <div class="graphe-double-col">
            <h3 class="graphe-mini-title"><span class="material-icons-sharp">build_circle</span> Réparations par statut
            </h3>
            <div class="graphe-chart-wrap-small">
                <canvas id="repaChart"></canvas>
            </div>
            <div class="graphe-doughnut-legend">
                <div class="graphe-doughnut-item">
                    <span class="graphe-doughnut-dot" style="background: var(--yellow);"></span>
                    <span class="graphe-doughnut-name">En attente</span>
                    <span class="graphe-doughnut-qte"><?= $nb_repa_attente ?></span>
                </div>
                <div class="graphe-doughnut-item">
                    <span class="graphe-doughnut-dot" style="background: var(--cyan);"></span>
                    <span class="graphe-doughnut-name">En cours</span>
                    <span class="graphe-doughnut-qte"><?= $nb_repa_cours ?></span>
                </div>
                <div class="graphe-doughnut-item">
                    <span class="graphe-doughnut-dot" style="background: var(--success);"></span>
                    <span class="graphe-doughnut-name">Terminées</span>
                    <span class="graphe-doughnut-qte"><?= $nb_repa_terminee ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== Tableau détaillé mensuel ===== -->
<section class="graphe-table-section graphe-animate">
    <div class="table-header">
        <h2 class="table-title"><span class="material-icons-sharp">table_chart</span> Détail mensuel des ventes</h2>
        <div class="total-badge">Total : <?= number_format($total_annuel, 0, ',', ' ') ?> FCFA</div>
    </div>

    <div class="graphe-table-wrap">
        <table class="graphe-table">
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>Ventes (FCFA)</th>
                    <th>% du total</th>
                    <th>Progression</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventes_mensuelles as $m => $v): ?>
                <?php $pct_total = $total_annuel > 0 ? round(($v / $total_annuel) * 100) : 0; ?>
                <?php $prog = $progressions[$m]; ?>
                <tr>
                    <td>
                        <div class="graphe-td-month">
                            <div class="graphe-month-num"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></div>
                            <span class="graphe-month-name"><?= $mois_fr[$m] ?></span>
                        </div>
                    </td>
                    <td><strong><?= number_format($v, 0, ',', ' ') ?></strong></td>
                    <td>
                        <span class="graphe-progress"><span class="graphe-progress-fill"
                                style="width: <?= max($pct_total, 2) ?>%;"></span></span>
                        <span class="graphe-percent"><?= $pct_total ?>%</span>
                    </td>
                    <td>
                        <?php if ($prog === null): ?>
                        <span class="graphe-trend-badge neutral"><span class="material-icons-sharp">remove</span>
                            —</span>
                        <?php elseif ($prog >= 0): ?>
                        <span class="graphe-trend-badge up"><span class="material-icons-sharp">arrow_upward</span>
                            <?= $prog ?>%</span>
                        <?php else: ?>
                        <span class="graphe-trend-badge down"><span class="material-icons-sharp">arrow_downward</span>
                            <?= abs($prog) ?>%</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Résumé annuel -->
    <div class="graphe-summary">
        <h3 class="graphe-summary-title"><span class="material-icons-sharp">emoji_events</span> Résumé de l'année
            <?= $annee_courante ?></h3>
        <div class="graphe-summary-grid">
            <div class="graphe-summary-item">
                <div class="graphe-summary-value"><?= $meilleur_mois ? $mois_fr[$meilleur_mois] : '—' ?></div>
                <div class="graphe-summary-label">Meilleur mois</div>
            </div>
            <div class="graphe-summary-item">
                <div class="graphe-summary-value green"><?= number_format($total_annuel, 0, ',', ' ') ?></div>
                <div class="graphe-summary-label">CA annuel (FCFA)</div>
            </div>
            <div class="graphe-summary-item">
                <div class="graphe-summary-value fuscha"><?= $nb_reparations ?></div>
                <div class="graphe-summary-label">Réparations</div>
            </div>
            <div class="graphe-summary-item">
                <div class="graphe-summary-value yellow"><?= $nb_clients ?></div>
                <div class="graphe-summary-label">Clients</div>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Graphique 1 : Évolution mensuelle =====
    const ctx = document.getElementById('ventesChart').getContext('2d');
    const labels = <?= $labels_json ?>;
    const data = <?= $data_json ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventes <?= $annee_courante ?> (FCFA)',
                data: data,
                backgroundColor: 'rgba(67, 97, 238, 0.75)',
                borderColor: '#4361ee',
                borderWidth: 1,
                borderRadius: 8,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(226, 101, 101, 0.85)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return 'Ventes : ' + Number(context.parsed.y).toLocaleString('fr-FR') +
                                ' FCFA';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'var(--text-color-secondary)',
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        color: 'var(--text-color-third)',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // ===== Graphique 2 : Comparaison hebdomadaire =====
    const ctx2 = document.getElementById('semaineChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?= $labels_semaine_json ?>,
            datasets: [{
                    label: 'Semaine précédente',
                    data: <?= $semaine_precedente_json ?>,
                    backgroundColor: 'rgba(155, 89, 182, 0.35)',
                    borderColor: '#9b59b6',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Cette semaine',
                    data: <?= $semaine_courante_json ?>,
                    backgroundColor: 'rgba(67, 97, 238, 0.7)',
                    borderColor: '#4361ee',
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.85)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ' : ' + Number(context.parsed.y)
                                .toLocaleString('fr-FR') + ' FCFA';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'var(--text-color-secondary)'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        color: 'var(--text-color-third)',
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // ===== Graphique 3 : Top 5 produits (doughnut) =====
    const ctx3 = document.getElementById('topProduitsChart').getContext('2d');
    new Chart(ctx3, {
        type: 'pie',
        data: {
            labels: <?= $top_labels_json ?>,
            datasets: [{
                data: <?= $top_data_json ?>,
                backgroundColor: [
                    'hsl(334, 94%, 57%)',
                    'hsl(184, 46%, 57%)',
                    'rgba(255, 251, 23, 0.96)',
                    'hsla(334, 94%, 57%, 0.1)',
                    'rgba(41, 37, 229, 0.7)'
                ],
                borderWidth: 2,
                borderColor: 'var(--white)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '0%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.85)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.label + ' : ' + context.parsed + ' vendus';
                        }
                    }
                }
            }
        }
    });

    // ===== Graphique 4 : Réparations par statut (doughnut) =====
    const ctx4 = document.getElementById('repaChart').getContext('2d');
    new Chart(ctx4, {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'En cours', 'Terminées'],
            datasets: [{
                data: [<?= $nb_repa_attente ?>, <?= $nb_repa_cours ?>,
                    <?= $nb_repa_terminee ?>
                ],
                backgroundColor: [
                    'hsl(334, 94%, 57%)',
                    'hsl(184, 46%, 57%)',
                    'rgba(41, 37, 229, 0.7)'
                ],
                borderWidth: 2,
                borderColor: 'var(--white)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '0%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.85)',
                    padding: 12,
                    cornerRadius: 8
                }
            }
        }
    });
});
</script>