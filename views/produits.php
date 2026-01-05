<?php
require_once 'inc/DataBase.php';
?>
 <style>
   .detail-price{
   position: absolute;
   top:1rem;
   left:1rem;
   padding: 0.8rem;
   border-radius: .5rem;
   background-color: var(--light);
   font-size: 1.4rem;
   color:var(--primary);
    }
    </style>
<div class="notification" id="notification"></div>

<div class="container-product">
    <h1><span class="material-icons-sharp">add_shopping_cart</span> Gestion des Produits</h1>
    <header>
        <header class="header-right">
            <button class="toggle-menu-btn" id="openSidebar">
                <span class="material-icons-sharp">menu</span>
            </button>
        </header>
        <div class="search-container" style="padding-right: 40px;">
            <input type="text" class="search-box" placeholder="Rechercher un produit...">
            <button class="btn btn-primary" id="addProductBtn">
                <span class="material-icons-sharp">add_shopping_cart</span> Ajouter un produit
            </button>
        </div>
    </header>

    <!-- modal pour ajouter des produits-->
    <div class="admin-panel" id="adminPanel">
        <h2 id="Title">Ajouter un nouveau produit</h2>
        <div class="form-grid">
            <input type="hidden" id="idproduit" name="idproduit">
            <div class="form-group">
                <label for="designation">Designation</label>
                <input type="text" id="designation" placeholder="Ex: Laptop Dell Inspiron">
            </div>
            <div class="form-group">
                <label for="caracteristique">caracteristique</label>
                <input type="text" id="caracteristique" placeholder="Ex: Hdd500go/Ram8go/dd2go/cpu2GHz">
            </div>
            <div class="form-group">
                <label for="quantite">Quantite</label>
                <input type="number" id="quantite" placeholder="Ex: 250">
            </div>
            <div class="form-group">
                <label for="quantite_min">Quantite Minimale</label>
                <input type="number" id="quantite_min" placeholder="Ex: 30">
            </div>
            <div class="form-group">
                <label for="categorie">categorie</label>
                <select id="categorie">
                    <option value="ordinateur">ordinateur</option>
                    <option value="equipement">equipement</option>
                </select>
            </div>
            <div class="form-group">
                <label for="prixUnitaire">Prix Unitaire</label>
                <input type="number" id="prixUnitaire" placeholder="Ex: 150000 FCFA">
            </div>

            <!-- Section d'upload d'image -->
            <div class="form-group image-upload-container">
                <label>Image du produit</label>
                <button type="button" class="upload-btn" id="uploadTrigger">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Choisir une image
                </button>
                <input type="file" id="imageUpload" class="file-input" accept="image/*">
                <div class="image-preview" id="imagePreview">
                    <div class="placeholder">
                        <div>Aucune image sélectionnée</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admin-actions">
            <button class="admin-btn add-btn" id="addProduit">Ajouter un produit</button>
            <button class="admin-btn update-btn" id="updateProduit" style="display: none;">Modifier le produit</button>
            <button class="admin-btn reset-btn" id="resetForm">Réinitialiser</button>
        </div>
    </div>
</div>
<div class="produits-container" id="produitContainer">

</div>

<script type="text/javascript" src="js/produit.js"></script>