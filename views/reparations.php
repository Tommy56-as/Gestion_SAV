<div class="notification" id="notification"></div>

<div class="reparations-page">
    <h1><span class="material-icons-sharp">build</span> Gestion des réparations</h1>
    <div class="reparations-header">
        <input type="text" class="search-box" placeholder="Rechercher un fournisseur...">
        <button class="btn btn-primary" type="button" id="openReparationModal"><span
                class="material-icons-sharp">build</span> Nouvelle réparation
        </button>
    </div>
    <header class="header-right">
        <button class="toggle-menu-btn" id="openSidebar">
            <span class="material-icons-sharp">menu</span>
        </button>
    </header>
    <section class="reparation-modal" id="reparationModal" aria-hidden="true">
        <div class="reparation-modal-content" role="dialog" aria-modal="true" aria-labelledby="reparationModalTitle">
            <button class="reparation-modal-close" type="button" id="closeReparationModal" aria-label="Fermer"><span
                    class="material-icons-sharp">close</span></button>
            <h2 id="reparationModalTitle">Nouvelle réparation</h2>
            <form id="reparationForm">
                <div class="reparation-form-grid">
                    <div class="form-group">
                        <label for="nomClient">Nom du client</label>
                        <input id="nomClient" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input id="telephone" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email">
                    </div>
                    <div class="form-group">
                        <label for="appareil">Appareil à dépanner</label>
                        <input id="appareil" required>
                    </div>
                    <div class="form-group">
                        <label for="iduser">Technicien</label>
                        <select id="iduser" required>
                            <option value="">Sélectionner</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mainOeuvre">Main-d'œuvre (FCFA)</label>
                        <input id="mainOeuvre" type="number" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="diagnostic">Diagnostic</label>
                        <textarea id="diagnostic"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="solution">Solution</label>
                        <textarea id="solution"></textarea>
                    </div>
                </div>
                <div class="reparation-pieces-builder">
                    <h3>Pièces utilisées</h3>
                    <div class="piece-entry">
                        <select id="idproduit">
                            <option value="">Sélectionner une pièce</option>
                        </select>
                        <input id="quantitePiece" type="number" min="1" value="1">
                        <button type="button" class="btn btn-primary" id="addPiece">
                            <span class="material-icons-sharp">add</span> Ajouter
                        </button>
                    </div>
                    <table class="pieces-table">
                        <thead>
                            <tr>
                                <th>Pièce</th>
                                <th>Qté</th>
                                <th>P.U.</th>
                                <th>Montant</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="piecesBody"></tbody>
                    </table>
                </div>
                <div class="admin-actions">
                    <button class="admin-btn add-btn" type="submit">
                        <span class="material-icons-sharp">save</span>Enregistrer
                    </button>
                    <button class="admin-btn reset-btn" type="reset">Réinitialiser</button>
                </div>
            </form>
        </div>
    </section>

    <section class="reparations-list">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Appareil</th>
                        <th>Technicien</th>
                        <th>Pièces</th>
                        <th>Main-d'œuvre</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="reparationsBody">
                    <tr>
                        <td colspan="8">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
<section class="reparation-modal" id="detailsReparationModal" aria-hidden="true">
    <div class="reparation-modal-content">
        <button class="reparation-modal-close" type="button" data-close-modal="detailsReparationModal">
            <span class="material-icons-sharp">close</span>
        </button>
        <div id="detailsReparationBody"></div>
    </div>
</section>
<section class="reparation-modal" id="factureReparationModal" aria-hidden="true">
    <div class="reparation-modal-content invoice-reparation" id="factureReparationContent">
        <button class="reparation-modal-close" type="button" data-close-modal="factureReparationModal">
            <span class="material-icons-sharp">close</span>
        </button>
        <div id="factureReparationBody"></div>
        <button class="btn btn-primary" type="button" id="printReparationInvoice">
            <span class="material-icons-sharp">print</span> Imprimer la facture
        </button>
    </div>
</section>
<script src="js/reparation.js"></script>