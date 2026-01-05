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

?>
        <div class="main-sidebar">
            <div class="aside-header" style="margin-top: -25px;">
                <div class="brand">
                    <div class="user-profile">
                        <img src="img/ceci.jpg" alt="Profil utilisateur" class="profile-img">
                        <div class="user-info">
                            <p class="greeting">Salut,</p>
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
                <div class="toggle-transparent">
                   <!-- bouton pour activer et desactiver le mode transparent -->
                    <p> mode transparent   
                        <span class="material-icons-sharp" id="toggleTransparency">opacity</span>
                    </p>     
                </div>
            </div>
            <div class="sidebar">
                <ul class="list-items" id="sidebarMenu">
                    <li class="item">
                        <a href="home.php?page=dashboard" >
                            <span class="material-icons-sharp">dashboard</span>
                            <span>Tableau de bord</span> 
                        </a>
                    </li>
                    
                     <li class="item">
                        <a href="home.php?page=produits">
                            <span class="material-icons-sharp">add_shopping_cart</span>
                            <span>ajouter produit</span>
                        </a>
                    </li>                   
                     <li class="item">
                        <a href="home.php?page=utilisateurPoo">
                            <span class="material-icons-sharp">shopping_cart_checkout</span>
                            <span>Ventes</span>
                        </a>
                    </li>                    
                    <li class="item">
                        <a href="home.php?page=fournisseurs">
                            <span class="material-icons-sharp">groups</span>
                            <span>Fournisseurs</span>
                        </a>
                    </li>
                    <li class="item">
                        <a href="home.php?page=users">
                            <span class="material-icons-sharp">local_offer</span>
                            <span>commande</span>
                        </a>
                    </li>
                    <li class="item">
                        <a href="home.php?page=utilisateurs">
                            <span class="material-icons-sharp">person_add_alt</span>
                            <span>Utilisateurs</span>
                        </a>
                    </li>
                    <li class="item">
                        <a href="home.php?page=graphe">
                            <span class="material-icons-sharp">history</span>
                            <span>Historique</span>
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
