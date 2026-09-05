<?php
require_once __DIR__ . '/../../inc/super_auth.php';
require_super_admin();
header('Content-Type: application/json');

try {
    $companies = $pdo->query(
        "SELECT e.idEntreprise, e.nom, e.slug, e.actif,
                a.idAbonnement, a.statut, a.periode, a.date_debut, a.date_fin,
                p.idPlan, p.code AS plan_code, p.nom AS plan_nom
         FROM entreprises e
         LEFT JOIN abonnements a ON a.idAbonnement = (
             SELECT a2.idAbonnement FROM abonnements a2
             WHERE a2.idEntreprise = e.idEntreprise ORDER BY a2.idAbonnement DESC LIMIT 1
         )
         LEFT JOIN plans p ON p.idPlan = a.idPlan
         ORDER BY e.nom"
    )->fetchAll(PDO::FETCH_ASSOC);
    $plans = $pdo->query('SELECT idPlan, code, nom, description, prix_mensuel, prix_annuel, actif FROM plans ORDER BY prix_mensuel, nom')->fetchAll(PDO::FETCH_ASSOC);
    $revenueStatement = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM paiements WHERE statut = 'paid' AND YEAR(paid_at) = YEAR(CURDATE()) AND MONTH(paid_at) = MONTH(CURDATE())");
    $monthlyRevenue = (float) $revenueStatement->fetchColumn();
    $activeCompanies = (int) $pdo->query("SELECT COUNT(*) FROM entreprises WHERE actif = 1")->fetchColumn();
    echo json_encode(['success' => true, 'companies' => $companies, 'plans' => $plans, 'monthlyRevenue' => $monthlyRevenue, 'activeCompanies' => $activeCompanies]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible de charger les données SaaS.']);
}
