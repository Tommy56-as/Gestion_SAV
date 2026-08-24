<?php

require_once('inc/Database.php');
$user_id = $_SESSION['user_id']; // Assurez-vous que l'utilisateur est connecté

try {
       // Récupérer les données de l'utilisateur depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE idUser = ?");
    $stmt->execute([$user_id]); // Ici on passe la variable $user_id, pas la chaîne 'id'
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $role = $user['TypeDeCompte'] ?? 'inconnu';
        $nomUser = $user['Nom_Utilisateur'] ??'user';
    }else{
        $role = 'inconnu';
        $nomUser ='inconnu';
    }
    
} catch(PDOException $e) {
    error_log("Erreur récupération utilisateur: " . $e->getMessage());
    
}

// Détermine la page courante (par défaut : dashboard)
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Fonction pour marquer un lien actif
function is_active($page) {
    global $current_page;
    return $current_page === $page ? ' class="active"' : '';
}

$isAdmin = ($_SESSION['user_type'] ?? '') === 'Administrateur';

?>
<div class="main-sidebar">
    <div class="aside-header">
        <div class="brand">
            <div class="user-profile">
                <img src="img/OIP.webp" alt="Profil utilisateur" class="profile-img">
                <div class="user-info">
                    <p class="greeting">Bienvenue,</p>
                    <h2 class="user-name"><?php echo htmlspecialchars($nomUser); ?></h2>
                    <p class="user-role"><?php echo htmlspecialchars($role); ?></p>
                </div>
            </div>
        </div>
        <div class="close" id="close">
            <span class="material-icons-sharp">close</span>
        </div>
        <div class="toggle-theme">
            <span class="material-icons-sharp active">light_mode</span>
            <span class="material-icons-sharp">dark_mode</span>
        </div>
    </div>
    <div class="sidebar">
        <ul class="list-items" id="sidebarMenu">
            <li class="item">
                <a href="home.php?page=dashboard" <?= is_active('dashboard') ?>>
                    <span class="material-icons-sharp">dashboard</span>
                    <span>Tableau de bord</span>
                </a>
            </li>

            <li class="item">
                <a href="home.php?page=produits" <?= is_active('produits') ?>>
                    <span class="material-icons-sharp">add_shopping_cart</span>
                    <span>Ajouter produit</span>
                </a>
            </li>
            <li class="item">
                <a href="home.php?page=ventes" <?= is_active('ventes') ?>>
                    <span class="material-icons-sharp">shopping_cart_checkout</span>
                    <span>Ventes</span>
                </a>
            </li>
            <?php if ($isAdmin): ?>
            <li class="item">
                <a href="home.php?page=fournisseurs" <?= is_active('fournisseurs') ?>>
                    <span class="material-icons-sharp">groups</span>
                    <span>Fournisseurs</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="item">
                <a href="home.php?page=commandes" <?= is_active('commandes') ?>>
                    <span class="material-icons-sharp">local_offer</span>
                    <span>Commandes</span>
                </a>
            </li>
            <li class="item">
                <a href="home.php?page=reparations" <?= is_active('reparations') ?>>
                    <span class="material-icons-sharp">build</span>
                    <span>Réparations</span>
                </a>
            </li>
            <?php if ($isAdmin): ?>
            <li class="item">
                <a href="home.php?page=utilisateurs" <?= is_active('utilisateurs') ?>>
                    <span class="material-icons-sharp">person_add_alt</span>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
            <li class="item">
                <a href="home.php?page=historiques" <?= is_active('historiques') ?>>
                    <span class="material-icons-sharp">history</span>
                    <span>Historiques</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
            <li class="item">
                <a href="home.php?page=Statistiques" <?= is_active('Statistiques') ?>>
                    <span class="material-icons-sharp">bar_chart</span>
                    <span>Statistiques</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="item">
                <a href="home.php?page=parametres" <?= is_active('parametres') ?>>
                    <span class="material-icons-sharp">settings</span>
                    <span>Paramètres</span>
                </a>
            </li>
            <?php if (($_SESSION['user_type'] ?? '') === 'Administrateur'): ?>
            <li class="item">
                <a href="home.php?page=autorisations" <?= is_active('autorisations') ?>>
                    <span class="material-icons-sharp">admin_panel_settings</span>
                    <span>Autorisations</span>
                </a>
            </li>
            <li class="item">
                <a href="home.php?page=sessions" <?= is_active('sessions') ?>>
                    <span class="material-icons-sharp">monitor_heart</span>
                    <span>Sessions</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="item">
                <a href="logout.php">
                    <span class="material-icons-sharp">logout</span>
                    <span>Deconnexion</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<script src="js/script.js"></script>
<!-- End Sidebar -->