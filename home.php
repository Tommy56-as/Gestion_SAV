<?php 
require_once 'Controller/admin_auth.php';
require_once 'inc/authorization.php';
require_once 'inc/saas.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$title = $page;

$pageFeature = [
    'dashboard' => 'dashboard',
    'produits' => 'produits',
    'ventes' => 'ventes',
    'fournisseurs' => 'fournisseurs',
    'commandes' => 'commandes',
    'reparations' => 'reparations',
    'utilisateurs' => 'utilisateurs',
    'Statistiques' => 'statistiques',
    'parametres' => 'parametres',
    'abonnement' => null,
    'sessions' => 'sessions',
    'autorisations' => 'autorisations',
    'historiques' => 'historiques',
][$page] ?? null;

if ($pageFeature !== null && $page !== 'dashboard' && !entreprise_feature_enabled($pageFeature)) {
    $_SESSION['access_error'] = 'Cette fonctionnalite n’est pas activee pour votre entreprise.';
    header('Location: home.php?page=dashboard');
    exit;
}

$requiredPermission = page_permission($page);
if ($requiredPermission === '__admin__' && !is_admin()) {
    $_SESSION['access_error'] = 'Cette page est réservée à l’administrateur.';
    header('Location: home.php?page=dashboard');
    exit;
} elseif ($requiredPermission !== null && $requiredPermission !== '__admin__' && !user_can($requiredPermission)) {
    $_SESSION['access_error'] = 'Vous n’êtes pas autorisé à accéder à cette page.';
    header('Location: home.php?page=dashboard');
    exit;
}
?>
<?php include 'Model/header.php'; ?>
<?php $currentEntreprise = current_entreprise($pdo); ?>
<script>window.APP_COMPANY = <?= json_encode($currentEntreprise, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>

<div class="dashboard-container">

    <!-- Sidebar -->
    <?php include 'views/siderbar.php'; ?>

    <!-- Contenu dynamique -->
    <div class="main-container">
        <?php if (isset($_SESSION['access_error'])): ?>
        <div class="notification error" role="alert">
            <?= htmlspecialchars($_SESSION['access_error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['access_error']); ?>
        <?php endif; ?>
        <?php 
            $file = "views/".$page . ".php";
            if (file_exists($file)) {
                include $file;
                $title = $page."php";
                
            } else {
    echo "<h2 style='padding:20px'>Page introuvable !</h2>";
      }
        ?>
    </div>

</div>


<?php include 'Model/footer.php'; ?>