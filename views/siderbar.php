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

?>
<div class="main-sidebar">
    <div class="aside-header">
        <div class="brand">
            <div class="user-profile">
                <img src="img/ceci.jpg" alt="Profil utilisateur" class="profile-img">
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
            <li class="item">
                <a href="home.php?page=fournisseurs" <?= is_active('fournisseurs') ?>>
                    <span class="material-icons-sharp">groups</span>
                    <span>Fournisseurs</span>
                </a>
            </li>
            <li class="item">
                <a href="home.php?page=commandes" <?= is_active('commandes') ?>>
                    <span class="material-icons-sharp">local_offer</span>
                    <span>Commandes</span>
                </a>
            </li>
            <li class="item">
                <a href="home.php?page=utilisateurs" <?= is_active('utilisateurs') ?>>
                    <span class="material-icons-sharp">person_add_alt</span>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li class="item">
                <a href="home.php?page=historiques" <?= is_active('historiques') ?>>
                    <span class="material-icons-sharp">history</span>
                    <span>Historiques</span>
                </a>
            </li>
            <li class="item">
                <a href="home.php?page=graphe" <?= is_active('graphe') ?>>
                    <span class="material-icons-sharp">bar_chart</span>
                    <span>Statistiques</span>
                </a>
            </li>
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