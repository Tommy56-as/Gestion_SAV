<div class="notification" id="notification"></div>

<div class="container-product commandes-page">
    <!-- Formulaire de création d'une commande d'approvisionnement. -->
    <div class="commandes-header">
        <h1><span class="material-icons-sharp">local_shipping</span> Gestion des approvisionnements</h1>
        <div class="commandes-header-actions">

            <input class="search-box" type="search" id="rechercheCommande" placeholder="Rechercher une commande..."
                autocomplete="off">

            <button class="btn btn-primary" type="button" id="openCommandeModal"><span
                    class="material-icons-sharp">local_offer</span> Nouvelle commande</button>
        </div>
        <header class="header-right">
            <button class="toggle-menu-btn" id="openSidebar">
                <span class="material-icons-sharp">menu</span>
            </button>
        </header>
    </div>
    <section class="commande-modal" id="commandeModal" aria-hidden="true">
        <div class="commande-modal-content" role="dialog" aria-modal="true" aria-labelledby="commandeModalTitle">
            <button class="commande-modal-close" type="button" id="closeCommandeModal" aria-label="Fermer"><span
                    class="material-icons-sharp">close</span></button>
            <h2 id="commandeModalTitle"><span class="material-icons-sharp">add_shopping_cart</span> Nouvelle commande
            </h2>
            <form id="commandeForm">
                <input type="hidden" id="idApp">
                <div class="form-grid">
                    <div class="form-group"><label for="commandeProduit">Produit</label>
                        <select id="commandeProduit" required>
                            <option value="">Sélectionner un produit</option>
                        </select>
                        <p id="stockActuel" style="font-size : 20px; color: green;">Stock actuel : -</p>
                    </div>
                    <div class="form-group"><label for="commandeFournisseur">Fournisseur</label><select
                            id="commandeFournisseur">
                            <option value="">Aucun fournisseur</option>
                        </select></div>
                    <div class="form-group"><label for="quantiteApp">Quantité commandée</label><input type="number"
                            id="quantiteApp" min="1" required></div>
                    <div class="form-group"><label for="prixTotal">Prix total</label><input type="number" id="prixTotal"
                            min="0" step="0.01" placeholder="FCFA"></div>
                    <div class="form-group"><label for="dateApp">Date prévue</label><input type="date" id="dateApp"
                            required></div>
                </div>
                <div class="admin-actions"><button class="admin-btn add-btn" id="saveCommande" type="submit"><span
                            class="material-icons-sharp">save</span> Enregistrer</button><button
                        class="admin-btn reset-btn" id="resetCommande" type="reset">Réinitialiser</button></div>
            </form>
        </div>
    </section>
    <!-- Tableau de suivi : la réception modifie aussi le stock produit. -->
    <section class="commandes-list">
        <div class="commandes-toolbar">
            <h2>Suivi des commandes</h2>
            <select id="filtreStatut" aria-label="Filtrer par statut">
                <option value="">Toutes les commandes</option>
                <option value="encours">En cours</option>
                <option value="terminee">Terminées</option>
            </select>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Fournisseur</th>
                        <th>Stock avant</th>
                        <th>Quantité</th>
                        <th>Prix total</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="commandesBody">
                    <tr>
                        <td colspan="8">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
<script src="js/approvisionnement.js"></script>