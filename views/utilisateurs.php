<?php
require_once 'inc/DataBase.php';
?>
    <!-- Notification -->
    <div class="notification" id="notification"></div>
 
    <div class="container">
        <h1><span class="material-icons-sharp">group</span> Gestion des Utilisateurs</h1>
        <header>
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Rechercher un utilisateur...">
                <button class="btn btn-primary" id="addUserBtn">
                    <span class="material-icons-sharp">person_add</span> Ajouter un utilisateur
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

        <div class="users-grid" id="usersContainer"></div>
    </div>
    <header class="header-right">
            <button class="toggle-menu-btn" id="openSidebar">
                  <span class="material-icons-sharp">menu</span>
            </button>
    </header>
    <!-- Modal Ajout/Modification -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><span class="material-icons-sharp" id="modalIcon">person_add</span> <span id="modalTitle">Ajouter un utilisateur</span></h2>
            </div>
            
            <form id="userForm" method="POST" action="">
                <input type="hidden" id="userId" name="idUser" value="">
                <input type="hidden" id="userStatus" name="statut" value="0">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="Nom_Utilisateur">
                            <span class="material-icons-sharp">person</span> Nom d'utilisateur
                        </label>
                        <input type="text" id="nomUtilisateur" name="Nom_Utilisateur" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="Email">
                            <span class="material-icons-sharp">mail</span> Email
                        </label>
                        <input type="email" id="email" name="Email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="TypeDeCompte">
                            <span class="material-icons-sharp">badge</span> Type de compte
                        </label>
                        <select id="typeCompte" name="TypeDeCompte" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <option value="Administrateur">Administrateur</option>
                            <option value="Caissier">Caissier</option>
                            <option value="Technicien">Technicien</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="MotDePasse">
                            <span class="material-icons-sharp">lock</span> Mot de passe
                        </label>
                        <input type="password" id="motDePasse" name="MotDePasse" class="form-control" required>
                        <small id="passwordHint" style="display: none; color: var(--primary-color);">
                            laisser vide pour ne pas modifier</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="NomComplet">
                            <span class="material-icons-sharp">contact_page</span> Nom complet
                        </label>
                        <input type="text" id="nomComplet" name="NomComplet" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="Telephone">
                            <span class="material-icons-sharp">phone</span> Téléphone
                        </label>
                        <input type="tel" id="telephone" name="Telephone" class="form-control" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="Adresse">
                            <span class="material-icons-sharp">location_on</span> Adresse
                        </label>
                        <input type="text" id="adresse" name="Adresse" class="form-control" required>
                    </div>
                </div>
                
                 <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="button" class="btn btn-secondary" id="closeModal" style="flex: 1;">
                        <span class="material-icons-sharp">close</span> Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="saveUser" style="flex: 1;">
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
<script type="text/javascript" src="js/user.js"></script>