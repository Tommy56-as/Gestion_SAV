<?php

require_once('inc/Database.php');
$user_id = $_SESSION['user_id']; 

try {
   
    // montant total annuel
    $sql_total = "SELECT SUM(prixTotal) AS total FROM lignevente";
    $stmt = $pdo->query($sql_total);
    $result_total = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $result_total['total'] ?: 0;
    
    //montant journalier
    $sql_total_jour = "SELECT SUM(l.prixTotal) AS total FROM vente v 
                       JOIN lignevente l ON v.idvente = l.idvente 
                       WHERE DATE(v.date_vente) = CURDATE()";
    $stmt = $pdo->query($sql_total_jour);
    $result_total_jour = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_jour = $result_total_jour['total'] ?: 0;

    //utilisateurs actifs/bloques
    $sql_user = "SELECT SUM(CASE WHEN Statut = FALSE THEN 1 ELSE 0 END) AS nb_actifs,
                        SUM(CASE WHEN Statut = TRUE  THEN 1 ELSE 0 END) AS nb_bloques
                 FROM utilisateur";
    $stmt = $pdo->query($sql_user);
    $result_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_actif  = $result_user['nb_actifs'] ?: 0;
    $user_bloque = $result_user['nb_bloques'] ?: 0;

    //reparations en cours - en attente - terminée
    $sql_reparation = "SELECT SUM(CASE WHEN statut = 'en cours' THEN 1 ELSE 0 END) AS en_cours,
                              SUM(CASE WHEN statut = 'en attente' THEN 1 ELSE 0 END) AS en_attente,
                              SUM(CASE WHEN statut = 'terminée' THEN 1 ELSE 0 END) AS terminée
                       FROM reparation";
    $stmt = $pdo->query($sql_reparation);
    $result_reparation = $stmt->fetch(PDO::FETCH_ASSOC);
    $en_cours  = $result_reparation['en_cours'] ?: 0;
    $en_attente = $result_reparation['en_attente'] ?: 0;
    $terminee = $result_reparation['terminée'] ?: 0;

} catch(PDOException $e) {
    echo ("Erreur: " . $e->getMessage());
    $total = 0;
    $total_jour = 0;
    $user_actif = 0;
    $user_bloque = 0;
    $en_cours  =  0;
    $en_attente =  0;
    $terminee =  0;
}

?>
<style>
    h2{
    font-size : 25px;
    background: linear-gradient(135deg, var(--fuscha), var(--cyan));
    -webkit-background-clip: text;
    background-clip: text;
    }
</style>
            <div class="main-header">
               <h1 class="main-title"><span class="material-icons-sharp">bar_chart</span> Tableau de bord</h1>
                <header class="header-right">
                    <button class="toggle-menu-btn" id="openSidebar">
                        <span class="material-icons-sharp">menu</span>
                    </button>
                </header>
            </div>
            <!--card-->
            <div class="inside">
                <div class="card">
                    <div class="card-container">
                        <div class="card-header">
                            <span class="material-icons-sharp">
                                bar_chart
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <h3>Ventes totales</h3>
                                <h2><?= number_format($total, 0, ',', ' ') ?> FCFA</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-container">
                        <div class="card-header">
                            <span class="material-icons-sharp">
                                currency_franc
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <h3>Ventes journalieres</h3>
                                <h2><?= number_format($total_jour, 0, ',', ' ') ?> FCFA</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-container">
                        <div class="card-header">
                            <span class="material-icons-sharp">
                                person_add
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <h3>Utilisateurs actifs</h3>
                                <h2><?= $user_actif ?> utilisateur(s)</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-container">
                        <div class="card-header">
                            <span class="material-icons-sharp">
                                person_add_disabled
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <h3>Utilisateurs bloqués</h3>
                                <h2><?= $user_bloque ?>  utilisateur(s)</h2>
                            </div>
                        </div>
                    </div>
               </div>
               <div class="card">
                    <div class="card-container">
                        <div class="card-header">
                            <span class="material-icons-sharp">
                                bar_chart
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <h3>Reparations en attente</h3>
                                <h2><?= $en_attente ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-container">
                        <div class="card-header">
                            <span class="material-icons-sharp">
                                bar_chart
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <h3>Reparations en cours</h3>
                                <h2><?= $en_cours ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-container">
                        <div class="card-header">
                            <span class="material-icons-sharp">
                                bar_chart
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="card-info">
                                <h3>Reparations terminées</h3>
                                <h2><?= $terminee ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>