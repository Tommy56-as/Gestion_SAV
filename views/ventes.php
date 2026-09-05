<div class="vente-container">
    <h1><span class="material-icons-sharp">add_shopping_cart</span> Gestion des Ventes</h1>
    <header>
        <div class="search-container">
            <input type="text" id="salesSearch" class="search-box" placeholder="Effectuer une recherche...">
            <button class="btn btn-primary" id="openVenteModal">
                <span class="material-icons-sharp">add</span> Nouvelle Vente
            </button>
        </div>
    </header>
    <header class="header-right">
        <button class="toggle-menu-btn" id="openSidebar">
            <span class="material-icons-sharp">menu</span>
        </button>
    </header>
    <div class="notification" id="notification"></div>
    <section class="sales-list">
        <h2 style="margin-bottom: 20px; justify-content: center;text-align: center">Liste des ventes</h2>
        <div class="table-responsive">
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Date</th>
                        <th>Total HT</th>
                        <th>Reçu</th>
                        <th>Remboursement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
                    <tr>
                        <td colspan="7" style="text-align:center;">Chargement...</td>
                    </tr>
                </tbody>
            </table>
            <div class="pagination-container" id="salesPagination"></div>
        </div>
    </section>

    <!-- Modal de Vente et Facture -->
    <div class="vente-modal" id="venteModal">
        <div class="modal-content-vente">
            <div class="modal-header">
                <h2>
                    <span class="material-icons-sharp">shopping_cart</span> Nouvelle Vente & Facture
                </h2>
                <button class="modal-close" id="closeVenteModal">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Section Formulaire -->
                <div>
                    <div class="form-section">
                        <h3>Informations Client</h3>
                        <form id="venteForm">
                            <div class="form-fields-grid">
                                <div class="form-group">
                                    <label for="client">Nom du Client *</label>
                                    <input type="text" id="client" name="client" required
                                        placeholder="Entrez le nom du client">
                                </div>

                                <div class="form-group">
                                    <label for="telephone">Téléphone</label>
                                    <input type="tel" id="telephone" name="telephone" placeholder="Téléphone du client">
                                </div>

                                <div class="form-group">
                                    <label for="date_vente">Date de Vente</label>
                                    <input type="date" id="date_vente" name="date_vente" disabled aria-disabled="true">
                                </div>
                                <div class="form-group">
                                    <label for="fin_garantie">Fin de Garantie</label>
                                    <input type="date" id="fin_garantie" name="fin_garantie" min="">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Section Ajout de Produits -->
                    <div class="product-input-section">
                        <h4>
                            <span class="material-icons-sharp">add_shopping_cart</span>Ajouter un Produit
                        </h4>
                        <div class="product-grid">
                            <div>
                                <div class="form-group" style="margin-bottom: 5px;">
                                    <label for="produit_vendu">Produit * <span id="stockInfo"
                                            style="font-size: 11px; color: var(--success);"></span></label>
                                    <select id="produit_vendu" name="produit_vendu" required>
                                        <option value="">-- Sélectionnez un produit --</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <div class="form-group" style="margin-bottom: 5px;">
                                    <label for="quantite">Quantité *</label>
                                    <input type="number" id="quantite" name="quantite" min="1" value="1" required>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="add-product-btn" id="addProductBtn">
                            <span class="material-icons-sharp">add_circle</span> Ajouter Produit à la Facture
                        </button>
                    </div>

                    <!-- Tableau des produits ajoutés -->
                    <div id="productsListContainer" style="display: none; margin-top: 15px;">
                        <h4 style="color: var(--primary); margin-bottom: 10px;">Produits Ajoutés</h4>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Caractéristiques</th>
                                    <th>Qté</th>
                                    <th>P.U (FCFA)</th>
                                    <th>Montant (FCFA)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                            </tbody>
                        </table>
                    </div>

                    <!-- Section Paiement -->
                    <div class="payment-section" id="paymentSection"
                        style="display: none; margin-top: 20px; padding: 15px; background: var(--white); border-radius: 8px;">
                        <h4>Gestion du Paiement</h4>

                        <div class="payment-fields-grid">
                            <div class="form-group">
                                <label for="moyenPaiement">Moyen de Paiement *</label>
                                <select id="moyenPaiement" name="moyenPaiement" required
                                    style="background: var(--white);">
                                    <option value="">-- Sélectionnez un moyen de paiement --</option>
                                    <option value="especes">Espèces</option>
                                    <option value="om">Orange Money (OM)</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tauxReduction">Taux de Réduction (%)</label>
                                <input type="number" id="tauxReduction" name="tauxReduction" min="0" max="100" value="0"
                                    placeholder="Ex: 10 pour 10%" style="background: var(--white);">
                            </div>

                            <div class="form-group">
                                <label for="prixTotal">Total (FCFA) *</label>
                                <input type="number" id="prixTotal" name="prixTotal" readonly value="0"
                                    style="background: var(--white);">
                            </div>

                            <div class="form-group">
                                <label for="montantReduction">Montant Réduction (FCFA)</label>
                                <input type="number" id="montantReduction" name="montantReduction" readonly value="0"
                                    style="background: var(--white);">
                            </div>

                            <div class="form-group">
                                <label for="totalApresReduction">Total Après Réduction (FCFA)</label>
                                <input type="number" id="totalApresReduction" name="totalApresReduction" readonly
                                    value="0"
                                    style="background: var(--white); font-weight: bold; border: 2px solid var(--primary);">
                            </div>

                            <div class="form-group">
                                <label for="prixRecu">Montant Reçu (FCFA) *</label>
                                <input type="number" id="prixRecu" name="prixRecu" min="0" value="0"
                                    style="background: var(--white) ;" required>
                            </div>
                            <div class="form-group">
                                <label for="remboursement">Remboursement (FCFA)</label>
                                <input type="number" id="remboursement" name="remboursement" readonly value="0"
                                    style="background: var(--white);">
                            </div>
                        </div>
                    </div>

                    <!-- Actions du Modal -->
                    <div class="modal-actions">
                        <button type="submit" form="venteForm" class="btn btn-primary" id="submitVente">
                            <span class="material-icons-sharp">check_circle</span> Enregistrer la Vente
                        </button>
                        <button type="button" class="btn btn-reset" id="resetVenteForm">
                            <span class="material-icons-sharp">refresh</span> Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Section Facture -->
                <div class="invoice-section">
                    <div class="invoice-header">
                        <h2><?= htmlspecialchars($currentEntreprise['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                        <h4><?= htmlspecialchars($currentEntreprise['telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
                        <h3>FACTURE</h3>
                        <p>Réf: <span id="invoiceRef">EN ATTENTE</span></p>
                    </div>

                    <div class="invoice-info">
                        <div class="invoice-info-item">
                            <label>Client:</label>
                            <span id="invoiceClient">-</span>
                        </div>
                        <div class="invoice-info-item">
                            <label>Téléphone:</label>
                            <span id="invoicePhone">-</span>
                        </div>
                        <div class="invoice-info-item">
                            <label>Date:</label>
                            <span id="invoiceDate">-</span>
                        </div>
                        <div class="invoice-info-item">
                            <label>Fin Garantie:</label>
                            <span id="invoiceWarranty">-</span>
                        </div>
                    </div>

                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Caractéristiques</th>
                                <th>Qté</th>
                                <th>P.U (FCFA)</th>
                                <th>Montant (FCFA)</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceBody">
                            <tr>
                            </tr>
                        </tbody>
                    </table>
                    <div class="invoice-totals">
                        <div class="totals-box">
                            <div class="total-row">
                                <label>Total HT:</label>
                                <span id="totalHT">0 FCFA</span>
                            </div>
                            <div class="total-row" id="invoiceReductionRow"
                                style="display: none; color: var(--danger);">
                                <label>Réduction:</label>
                                <span id="invoiceReduction">0 FCFA</span>
                            </div>
                            <div class="total-row">
                                <label>Total Après Réduction:</label>
                                <span id="invoiceTotalApres">0 FCFA</span>
                            </div>
                            <div class="total-row">
                                <label>Moyen de Paiement:</label>
                                <span id="invoicePaymentMethod">-</span>
                            </div>
                            <div class="total-row">
                                <label>Prix Reçu:</label>
                                <span id="totalReceived">0 FCFA</span>
                            </div>
                            <div class="total-row remboursement">
                                <label>Remboursement:</label>
                                <span id="totalRefund">0 FCFA</span>
                            </div>
                            <div class="total-row final">
                                <label>NET À PAYER:</label>
                                <span id="totalFinal">0 FCFA</span>
                            </div>
                        </div>
                    </div>
                    <p style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                        Merci pour votre achat et a bientot !
                    </p>
                    <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;"
                        class="no-print">
                        <button type="button" class="btn btn-print" onclick="generatePDF()">
                            <span class="material-icons-sharp">print</span> Imprimer la Facture
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="details-modal" id="detailsModal">
        <div class="modal-content-details">
            <div class="modal-header">
                <h2><span class="material-icons-sharp">receipt_long</span> Détails de la Vente</h2>
                <button class="modal-close" type="button" onclick="closeDetailsModal()">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="invoice-info">
                    <div class="invoice-info-item">
                        <label>Client :</label>
                        <span id="detailsModalClient">-</span>
                    </div>
                    <div class="invoice-info-item">
                        <label>Téléphone :</label>
                        <span id="detailsModalTelephone">-</span>
                    </div>
                    <div class="invoice-info-item">
                        <label>Date :</label>
                        <span id="detailsModalDate">-</span>
                    </div>
                    <div class="invoice-info-item">
                        <label>Total :</label>
                        <span id="detailsModalTotal">0 FCFA</span>
                    </div>
                    <div class="invoice-info-item">
                        <label>Reçu :</label>
                        <span id="detailsModalRecu">0 FCFA</span>
                    </div>
                    <div class="invoice-info-item">
                        <label>Remboursement :</label>
                        <span id="detailsModalRemboursement">0 FCFA</span>
                    </div>
                    <div class="invoice-info-item">
                        <label>Montant Réduction :</label>
                        <span id="detailsModalMontantReduction">0 FCFA</span>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <h4>Produits de la vente</h4>
                    <div class="table-responsive">
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th>Désignation</th>
                                    <th>Caractéristiques</th>
                                    <th>Qté</th>
                                    <th>Prix unitaire</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody id="detailsTableBody">
                                <tr>
                                    <td colspan="5" style="text-align:center;">Chargement...</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="pagination-container" id="detailsPagination"></div>
                        <div style="text-align: center; margin-top: 10px;" class="no-print">
                            <!--<button type="button" class="btn btn-print" onclick="printViewedSale()">
                                <span class="material-icons-sharp">print</span> Imprimer la Facture
                            </button> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="js/ventes.js"></script>