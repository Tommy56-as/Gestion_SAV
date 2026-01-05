<?php
require_once 'inc/DataBase.php';
?>
<!-- Notification -->
<div class="notification" id="notification"></div>
<div class="container">
    <h1><span class="material-icons-sharp">group</span> Gestion des Fournisseurs</h1>
    <header>
        <div class="search-container">
            <input type="text" class="search-box" placeholder="Rechercher un fournisseur...">
            <button class="btn btn-primary" id="addFournisseurBtn">
                <span class="material-icons-sharp">person_add</span> Ajouter un fournisseur
            </button>
        </div>
    </header>

    <?php if (isset($error_message)): ?>
    <div class="error-message">
        <span class="material-icons-sharp">error</span>
        <div>
            <strong>Erreur :</strong> <?php echo $error_message; ?>
            <p style="margin-top: 5px; font-size: 0.9rem;">Vérifiez votre configuration de base de données.</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="users-grid" id="FournisseurContainer"></div>
</div>
<header class="header-right">
    <button class="toggle-menu-btn" id="openSidebar">
        <span class="material-icons-sharp">menu</span>
    </button>
</header>
<!-- Modal Ajout/Modification -->
<div class="modal" id="fournisseurModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><span class="material-icons-sharp" id="modalIcon">person_add</span>
                <span id="modalTitle">Ajouter un fournisseur</span>
            </h2>
        </div>
        <form id="fournisseurForm" method="POST" action="">
            <input type="hidden" id="idfour" name="fournisseurId">
            <input type="hidden" id="fournisseurStatus" name="statut" value="0">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nom">
                        <span class="material-icons-sharp">person</span> Nom *
                    </label>
                    <input type="text" id="nom" name="nom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="prenom">
                        <span class="material-icons-sharp">person</span> Prénom *
                    </label>
                    <input type="text" id="prenom" name="prenom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="telephone">
                        <span class="material-icons-sharp">phone</span> Téléphone *
                    </label>
                    <input type="text" id="telephone" name="telephone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="adresse">
                        <span class="material-icons-sharp">location_on</span> Adresse *
                    </label>
                    <input type="text" id="adresse" name="adresse" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="designation">Produit *</label>
                    <select id="designation" class="form-control" required>
                        <option value="">Sélectionner un produit</option>
                        <?php
                        $stmt = $pdo->query("SELECT DISTINCT designation FROM produit");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($row['designation']) . '">'
                                . htmlspecialchars($row['designation'])
                               . '</option>';
                         }
                       ?>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="produitLivre">Caractéristique *</label>
                    <select id="produitLivre" name="produitLivre" class="form-control" required disabled>
                        <option value="">Sélectionner une caractéristique</option>
                    </select>

                </div>
            </div>
            <div style="display: flex; gap: 15px; margin-top: 25px;">
                <button type="button" class="btn btn-secondary" id="closeModal" style="flex: 1;">
                    <span class="material-icons-sharp">close</span> Fermer
                </button>
                <button type="button" class="btn btn-primary" id="saveFournisseur" style="flex: 1;">
                    <span class="material-icons-sharp">save</span> Enregistrer
                </button>
                <button type="button" class="btn btn-primary" id="updateUser" style="flex: 1; display:none;">
                    <span class="material-icons-sharp">save</span> Modifier
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<script type="text/javascript" src="js/fournisseur.js"></script>