// Gestion des ventes et factures avec support multi-produits
const BASE_URL = window.location.origin + "/GESTION_SAV/";
let productsData = {};
let addedProducts = []; // Tableau des produits ajoutés à la facture
let salesData = [];
let filteredSales = [];
let salesCurrentPage = 1;
let detailsData = [];
let detailsCurrentPage = 1;
const PAGE_SIZE = 10;
let prixRecu = 0;
let currentViewedSale = null;
let currentViewedDetails = [];

// Initialisation
document.addEventListener("DOMContentLoaded", function () {
  setDateToday();
  loadProduits();
  setupModalListeners();
  setupFormListeners();
  loadSalesList();
  document.getElementById("salesSearch").addEventListener("input", filterSales);
});

// === GESTION DU MODAL ===
function setupModalListeners() {
  const modal = document.getElementById("venteModal");
  const openBtn = document.getElementById("openVenteModal");
  const closeBtn = document.getElementById("closeVenteModal");

  // Ouvrir le modal
  openBtn.addEventListener("click", function () {
    modal.classList.add("active");
    resetForm();
  });

  // Fermer le modal
  closeBtn.addEventListener("click", function () {
    modal.classList.remove("active");
  });

  // Fermer en cliquant en dehors du modal
  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });

  const detailsModal = document.getElementById("detailsModal");
  if (detailsModal) {
    detailsModal.addEventListener("click", function (e) {
      if (e.target === detailsModal) {
        detailsModal.classList.remove("active");
      }
    });
  }

  // Bouton Réinitialiser
  document
    .getElementById("resetVenteForm")
    .addEventListener("click", resetForm);
}

// Réinitialiser le formulaire
function resetForm() {
  document.getElementById("venteForm").reset();
  document.getElementById("produit_vendu").value = "";
  document.getElementById("quantite").value = "1";
  document.getElementById("fin_garantie").value = "";
  document.getElementById("prixRecu").value = "0";
  document.getElementById("remboursement").value = "0";
  document.getElementById("prixTotal").value = "0";
  addedProducts = [];
  prixRecu = 0;
  setDateToday();
  updateProductsList();
  updateInvoice();
}

// Charger les produits
function loadProduits() {
  const url = `${BASE_URL}Controller/produit/get_produits.php`;

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && Array.isArray(data.data)) {
        const select = document.getElementById("produit_vendu");
        select.innerHTML =
          '<option value="">-- Sélectionnez un produit --</option>';

        data.data.forEach((produit) => {
          const option = document.createElement("option");
          option.value = produit.idproduit;
          option.textContent = `${produit.designation} - ${produit.caracteristique}`;
          select.appendChild(option);

          // Stocker les données du produit
          productsData[produit.idproduit] = produit;
        });
      }
    })
    .catch((err) => {
      console.error("Erreur lors du chargement des produits:", err);
      showNotification("Erreur lors du chargement des produits", "error");
    });
}

function loadSalesList() {
  fetch(`${BASE_URL}Controller/vente/get_ventes.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && Array.isArray(data.data)) {
        salesData = data.data;
        filteredSales = salesData;
        salesCurrentPage = 1;
        displaySalesTable();
      } else {
        showNotification("Impossible de charger les ventes", "error");
      }
    })
    .catch((err) => {
      console.error("Erreur lors du chargement des ventes:", err);
      showNotification("Erreur lors du chargement des ventes", "error");
    });
}

function displaySalesTable(ventes = filteredSales) {
  const tbody = document.getElementById("salesTableBody");
  const paginationContainer = document.getElementById("salesPagination");
  tbody.innerHTML = "";

  if (!Array.isArray(ventes) || ventes.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="7" style="text-align:center;">Aucune vente trouvée</td></tr>';
    if (paginationContainer) paginationContainer.innerHTML = "";
    return;
  }

  const totalPages = Math.ceil(ventes.length / PAGE_SIZE);
  if (salesCurrentPage > totalPages) {
    salesCurrentPage = totalPages;
  }

  const startIndex = (salesCurrentPage - 1) * PAGE_SIZE;
  const pagedVentes = ventes.slice(startIndex, startIndex + PAGE_SIZE);

  pagedVentes.forEach((vente) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${vente.client}</td>
            <td>${vente.telephone || "-"}</td>
            <td>${formatDate(vente.date_vente)}</td>
            <td>${formatCurrency(vente.prixTotal ?? vente.totalHT ?? 0)}</td>
            <td>${formatCurrency(vente.prixRecu ?? 0)}</td>
            <td>${formatCurrency(vente.remboursement ?? 0)}</td>
            <td>
                <button type="button" class="btn btn-secondary" onclick="openSaleDetails(${
                  vente.idvente
                })">Détails</button>
            </td>
        `;
    tbody.appendChild(row);
  });

  renderPagination(
    "salesPagination",
    ventes.length,
    PAGE_SIZE,
    salesCurrentPage,
    (page) => {
      salesCurrentPage = page;
      displaySalesTable(ventes);
    }
  );
}

function renderPagination(
  containerId,
  totalItems,
  pageSize,
  currentPage,
  onPageChange
) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = "";

  const totalPages = Math.ceil(totalItems / pageSize);
  if (totalPages <= 1) return;

  const pagination = document.createElement("div");
  pagination.className = "pagination";

  const createButton = (label, page, disabled = false, active = false) => {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = label;
    button.disabled = disabled;
    if (active) button.classList.add("active");
    button.addEventListener("click", () => onPageChange(page));
    return button;
  };

  pagination.appendChild(
    createButton("Préc", currentPage - 1, currentPage === 1)
  );

  if (totalPages <= 7) {
    for (let i = 1; i <= totalPages; i += 1) {
      pagination.appendChild(createButton(i, i, false, currentPage === i));
    }
  } else {
    pagination.appendChild(createButton(1, 1, false, currentPage === 1));

    if (currentPage > 4) {
      const dots = document.createElement("span");
      dots.textContent = "...";
      dots.className = "pagination-dots";
      pagination.appendChild(dots);
    }

    const start = Math.max(2, currentPage - 1);
    const end = Math.min(totalPages - 1, currentPage + 1);

    for (let i = start; i <= end; i += 1) {
      pagination.appendChild(createButton(i, i, false, currentPage === i));
    }

    if (currentPage < totalPages - 3) {
      const dots = document.createElement("span");
      dots.textContent = "...";
      dots.className = "pagination-dots";
      pagination.appendChild(dots);
    }

    pagination.appendChild(
      createButton(totalPages, totalPages, false, currentPage === totalPages)
    );
  }

  pagination.appendChild(
    createButton("Suiv", currentPage + 1, currentPage === totalPages)
  );

  const info = document.createElement("div");
  info.className = "pagination-info";
  const firstItem = (currentPage - 1) * pageSize + 1;
  const lastItem = Math.min(currentPage * pageSize, totalItems);
  info.textContent = `Affichage ${firstItem}-${lastItem} sur ${totalItems}`;

  container.appendChild(info);
  container.appendChild(pagination);
}

function filterSales(event) {
  const query = event.target.value.toLowerCase();
  const filtered = salesData.filter(
    (vente) =>
      String(vente.idvente).includes(query) ||
      (vente.client || "").toLowerCase().includes(query) ||
      (vente.telephone || "").toLowerCase().includes(query) ||
      (vente.date_vente || "").toLowerCase().includes(query)
  );
  salesCurrentPage = 1;
  displaySalesTable(filtered);
}

function openSaleDetails(idvente) {
  fetch(
    `${BASE_URL}Controller/vente/get_vente_details.php?idvente=${encodeURIComponent(
      idvente
    )}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        showNotification("Impossible de charger les détails", "error");
        return;
      }

      const vente = data.vente;
      const details = data.details;

      // Stocker la vente et les détails pour l'impression depuis le modal
      currentViewedSale = vente;
      currentViewedDetails = Array.isArray(details) ? details : [];

      document.getElementById("detailsModalClient").textContent = vente.client;
      document.getElementById("detailsModalTelephone").textContent =
        vente.telephone || "-";
      document.getElementById("detailsModalDate").textContent = formatDate(
        vente.date_vente
      );
      document.getElementById("detailsModalTotal").textContent = formatCurrency(
        vente.totalHT ?? 0
      );
      document.getElementById("detailsModalRecu").textContent = formatCurrency(
        vente.prixRecu ?? 0
      );
      document.getElementById("detailsModalRemboursement").textContent =
        formatCurrency(vente.remboursement ?? 0);
      // Montant réduction (si présent depuis le back)
      const montantReductionVal = parseFloat(vente.montantReduction) || 0;
      const montantRedEl = document.getElementById(
        "detailsModalMontantReduction"
      );
      if (montantRedEl)
        montantRedEl.textContent = formatCurrency(montantReductionVal);

      detailsData = Array.isArray(details) ? details : [];
      detailsCurrentPage = 1;
      displayDetailsTable();

      const detailsModal = document.getElementById("detailsModal");
      detailsModal.classList.add("active");
    })
    .catch((err) => {
      console.error("Erreur lors du chargement des détails:", err);
      showNotification("Erreur lors du chargement des détails", "error");
    });
}

function closeDetailsModal() {
  document.getElementById("detailsModal").classList.remove("active");
}

function displayDetailsTable() {
  const tbody = document.getElementById("detailsTableBody");
  const paginationContainer = document.getElementById("detailsPagination");
  tbody.innerHTML = "";

  if (!Array.isArray(detailsData) || detailsData.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="5" style="text-align:center;">Aucun détail pour cette vente</td></tr>';
    if (paginationContainer) paginationContainer.innerHTML = "";
    return;
  }

  const totalPages = Math.ceil(detailsData.length / PAGE_SIZE);
  if (detailsCurrentPage > totalPages) {
    detailsCurrentPage = totalPages;
  }

  const startIndex = (detailsCurrentPage - 1) * PAGE_SIZE;
  const pagedDetails = detailsData.slice(startIndex, startIndex + PAGE_SIZE);

  pagedDetails.forEach((item) => {
    const row = document.createElement("tr");
    row.innerHTML = `
        <td>${item.designation ?? "-"}</td>
        <td>${item.caracteristique ?? "-"}</td>
        <td>${item.quantite}</td>
        <td>${formatCurrency(item.prixUnitaire)}</td>
        <td>${formatCurrency(item.montant)}</td>
      `;
    tbody.appendChild(row);
  });

  renderPagination(
    "detailsPagination",
    detailsData.length,
    PAGE_SIZE,
    detailsCurrentPage,
    (page) => {
      detailsCurrentPage = page;
      displayDetailsTable();
    }
  );
}

// === GESTION DES PRODUITS AJOUTÉS ===
function setupFormListeners() {
  // Ajouter un produit à la facture
  document
    .getElementById("addProductBtn")
    .addEventListener("click", addProductToInvoice);

  // Soumission du formulaire
  document
    .getElementById("venteForm")
    .addEventListener("submit", handleFormSubmit);

  // Changement du montant reçu
  document
    .getElementById("prixRecu")
    .addEventListener("input", calculateRemboursement);

  // Changement du taux de réduction et moyen de paiement
  document
    .getElementById("tauxReduction")
    .addEventListener("input", calculateRemboursement);
  document
    .getElementById("moyenPaiement")
    .addEventListener("change", updateInvoice);

  // Changement du produit sélectionné
  document
    .getElementById("produit_vendu")
    .addEventListener("change", function () {
      const productId = this.value;
      const stockInfo = document.getElementById("stockInfo");

      if (productId && productsData[productId]) {
        const product = productsData[productId];
        const stockDisponible = parseInt(product.quantite) || 0;
        const quantiteDejaAjoutee = addedProducts
          .filter((p) => p.idproduit === productId)
          .reduce((sum, p) => sum + p.quantite, 0);
        const stockRestant = stockDisponible - quantiteDejaAjoutee;

        if (stockRestant <= 0) {
          stockInfo.textContent = `(qte : ${stockRestant} --> Stock épuisé)`;
          stockInfo.style.color = "var(--danger)";
        } else if (stockRestant <= 5) {
          stockInfo.textContent = `(qte : ${stockRestant} --> Stock faible ⚠️)`;
          stockInfo.style.color = "var(--warning, #ff9800)";
        } else {
          stockInfo.textContent = `(qte : ${stockRestant} --> Stock disponible)`;
          stockInfo.style.color = "var(--success)";
        }
      } else {
        stockInfo.textContent = "";
      }
    });
}

// Calculer le remboursement automatiquement
function calculateRemboursement() {
  const totalHT = addedProducts.reduce(
    (sum, p) => sum + p.prixUnitaire * p.quantite,
    0
  );
  const tauxReduction =
    parseFloat(document.getElementById("tauxReduction").value) || 0;
  const montantReduction = (totalHT * tauxReduction) / 100;
  const totalApresReduction = totalHT - montantReduction;
  const prixRecu = parseFloat(document.getElementById("prixRecu").value) || 0;
  const remboursement = prixRecu - totalApresReduction;

  // Mise à jour des champs de paiement
  document.getElementById("montantReduction").value =
    montantReduction.toFixed(0);
  document.getElementById("totalApresReduction").value =
    totalApresReduction.toFixed(0);
  document.getElementById("remboursement").value = remboursement;

  updateInvoice();
}

// Jouer une alerte sonore pour les alertes de stock
function playStockAlert(type = "warning") {
  try {
    const audioContext = new (window.AudioContext ||
      window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    if (type === "warning") {
      // Son d'avertissement: bip bip
      oscillator.frequency.value = 800;
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
      oscillator.start(audioContext.currentTime);
      oscillator.stop(audioContext.currentTime + 0.15);

      oscillator.start(audioContext.currentTime + 0.25);
      oscillator.stop(audioContext.currentTime + 0.4);
    } else if (type === "error") {
      // Son d'erreur: son plus grave et long
      oscillator.frequency.value = 400;
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
      oscillator.start(audioContext.currentTime);
      oscillator.stop(audioContext.currentTime + 0.5);
    }
  } catch (e) {
    console.warn("Erreur lors de la lecture du son d'alerte:", e);
  }
}

// Vérifier la disponibilité des stocks
function checkStockAvailability(productId, quantite) {
  if (!productsData[productId]) {
    return { available: false, message: "Produit introuvable" };
  }

  const product = productsData[productId];
  const stockDisponible = parseInt(product.quantite) || 0;
  const quantiteDejaAjoutee = addedProducts
    .filter((p) => p.idproduit === productId)
    .reduce((sum, p) => sum + p.quantite, 0);
  const stockRestant = stockDisponible - quantiteDejaAjoutee;

  if (quantite > stockRestant) {
    const message =
      `Stock insuffisant pour "${product.designation}". ` +
      `Disponible: ${stockRestant} unité(s), demandé: ${quantite} unité(s)`;
    return {
      available: false,
      message,
      stockRestant,
      stockDisponible,
      quantiteDejaAjoutee,
    };
  }

  if (stockRestant <= 5) {
    playStockAlert("warning");
    const message =
      `⚠️ Attention: Stock faible pour "${product.designation}". ` +
      `Restant après cette vente: ${stockRestant - quantite} unité(s)`;
    return { available: true, message, isWarning: true, stockRestant };
  }

  return { available: true, message: null };
}

// Ajouter un produit à la facture
function addProductToInvoice() {
  const productId = document.getElementById("produit_vendu").value;
  const quantite = parseInt(document.getElementById("quantite").value) || 0;
  const finGarantie = document.getElementById("fin_garantie").value;

  // Validation
  if (!productId) {
    showNotification("Veuillez sélectionner un produit", "error");
    return;
  }

  if (quantite <= 0) {
    showNotification("La quantité doit être supérieure à 0", "error");
    return;
  }

  if (!productsData[productId]) {
    showNotification("Produit introuvable", "error");
    return;
  }

  // Vérifier la disponibilité du stock
  const stockCheck = checkStockAvailability(productId, quantite);
  if (!stockCheck.available) {
    playStockAlert("error");
    showNotification(stockCheck.message, "error");
    return;
  }

  if (stockCheck.isWarning) {
    showNotification(stockCheck.message, "warning");
  }

  const product = productsData[productId];
  const prixUnitaire = parseFloat(product.prixUnitaire) || 0;
  const montant = prixUnitaire * quantite;

  // Vérifier si le produit existe déjà
  const existingIndex = addedProducts.findIndex(
    (p) => p.idproduit === productId
  );

  if (existingIndex !== -1) {
    // Mettre à jour la quantité si le produit existe déjà
    addedProducts[existingIndex].quantite += quantite;
  } else {
    // Ajouter le nouveau produit
    addedProducts.push({
      idproduit: productId,
      designation: product.designation,
      caracteristique: product.caracteristique,
      quantite: quantite,
      prixUnitaire: prixUnitaire,
      montant: montant,
      finGarantie: finGarantie,
      stockDisponible: parseInt(product.quantite) || 0,
    });
  }

  // Réinitialiser les champs de produit
  document.getElementById("produit_vendu").value = "";
  document.getElementById("quantite").value = "1";
  // Mettre à jour l'affichage
  updateProductsList();
  updateInvoice();
  showNotification("Produit ajouté à la facture", "success");
}

// Mettre à jour la liste des produits ajoutés
function updateProductsList() {
  const container = document.getElementById("productsListContainer");
  const tbody = document.getElementById("productsTableBody");

  if (addedProducts.length === 0) {
    container.style.display = "none";
    tbody.innerHTML = "";
    return;
  }

  container.style.display = "block";
  tbody.innerHTML = "";

  addedProducts.forEach((product, index) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${product.designation}</td>
            <td>${product.caracteristique}</td>
            <td>${product.quantite}</td>
            <td>${formatCurrency(product.prixUnitaire)}</td>
            <td>${formatCurrency(product.montant)}</td>
            <td>
                <button type="button" class="remove-btn" onclick="removeProduct(${index})">
                    Supprimer
                </button>
            </td>
        `;
    tbody.appendChild(row);
  });
}

// Supprimer un produit
function removeProduct(index) {
  addedProducts.splice(index, 1);
  updateProductsList();
  updateInvoice();
  showNotification("Produit supprimé", "success");
}

// === GESTION DE LA FACTURE ===
function updateInvoice() {
  const client = document.getElementById("client").value || "-";
  const telephone = document.getElementById("telephone").value || "-";
  const dateSale = document.getElementById("date_vente").value || "-";
  const datefingarantie = document.getElementById("fin_garantie").value || "-";
  const moyenPaiement = document.getElementById("moyenPaiement").value || "-";

  // Mise à jour des infos client
  document.getElementById("invoiceClient").textContent = client;
  document.getElementById("invoicePhone").textContent = telephone;
  document.getElementById("invoiceDate").textContent = formatDate(dateSale);
  document.getElementById("invoiceWarranty").textContent =
    formatDate(datefingarantie);
  document.getElementById("invoicePaymentMethod").textContent =
    getMoyenPaiementLabel(moyenPaiement);

  // Calcul du total HT
  const totalHT = addedProducts.reduce(
    (sum, p) => sum + p.prixUnitaire * p.quantite,
    0
  );
  const tauxReduction =
    parseFloat(document.getElementById("tauxReduction").value) || 0;
  const montantReduction = (totalHT * tauxReduction) / 100;
  const totalApresReduction = totalHT - montantReduction;

  // Mise à jour du tableau facture
  if (addedProducts.length > 0) {
    const tbody = document.getElementById("invoiceBody");
    tbody.innerHTML = "";

    addedProducts.forEach((product) => {
      const montant = product.prixUnitaire * product.quantite;

      const row = document.createElement("tr");
      row.innerHTML = `
                <td>${product.designation}</td>
                <td>${product.caracteristique}</td>
                <td>${product.quantite}</td>
                <td>${formatCurrency(product.prixUnitaire)}</td>
                <td>${formatCurrency(montant)}</td>
            `;
      tbody.appendChild(row);
    });

    // Afficher la section paiement et mettre à jour les totaux
    document.getElementById("paymentSection").style.display = "block";
    document.getElementById("prixTotal").value = totalHT;
    document.getElementById("totalHT").textContent = formatCurrency(totalHT);

    // Afficher la réduction si elle existe
    const invoiceReductionRow = document.getElementById("invoiceReductionRow");
    if (tauxReduction > 0) {
      invoiceReductionRow.style.display = "flex";
      document.getElementById("invoiceReduction").textContent =
        formatCurrency(montantReduction) +
        " (" +
        tauxReduction.toFixed(2) +
        "%)";
    } else {
      invoiceReductionRow.style.display = "none";
    }

    document.getElementById("invoiceTotalApres").textContent =
      formatCurrency(totalApresReduction);

    // Mettre à jour le prixRecu affichés et le remboursement
    const prixRecu =
      parseFloat(document.getElementById("prixRecu").value) ||
      totalApresReduction;
    const remboursement = prixRecu - totalApresReduction;

    document.getElementById("totalReceived").textContent =
      formatCurrency(prixRecu);

    // Afficher le remboursement ou le montant à payer
    if (remboursement < 0) {
      document.getElementById("totalRefund").textContent =
        formatCurrency(Math.abs(remboursement)) + " (à payer)";
    } else {
      document.getElementById("totalRefund").textContent =
        formatCurrency(remboursement);
    }

    document.getElementById("totalFinal").textContent =
      formatCurrency(totalApresReduction);
  } else {
    document.getElementById("invoiceBody").innerHTML = ``;
    document.getElementById("totalHT").textContent = "0 FCFA";
    document.getElementById("invoiceTotalApres").textContent = "0 FCFA";
    document.getElementById("totalReceived").textContent = "0 FCFA";
    document.getElementById("totalRefund").textContent = "0 FCFA";
    document.getElementById("totalFinal").textContent = "0 FCFA";
    document.getElementById("invoiceReductionRow").style.display = "none";
    document.getElementById("paymentSection").style.display = "none";
  }
}

// Convertir le code moyen paiement en label lisible
function getMoyenPaiementLabel(code) {
  const labels = {
    especes: "Espèces",
    om: "Orange Money (OM)",
    mobile_money: "Mobile Money",
  };
  return labels[code] || "-";
}
// === GESTION DE LA SOUMISSION ===
function handleFormSubmit(e) {
  e.preventDefault();

  const client = document.getElementById("client").value.trim();
  const telephone = document.getElementById("telephone").value.trim();
  const dateSale = document.getElementById("date_vente").value;
  const datefingarantie = document.getElementById("fin_garantie").value;
  const prixRecu = parseFloat(document.getElementById("prixRecu").value) || 0;
  const remboursement =
    parseFloat(document.getElementById("remboursement").value) || 0;

  // Validation
  if (!client) {
    showNotification("Veuillez entrer le nom du client", "error");
    return;
  }

  if (addedProducts.length === 0) {
    showNotification(
      "Veuillez ajouter au moins un produit à la facture",
      "error"
    );
    return;
  }

  if (!dateSale) {
    showNotification("Veuillez sélectionner une date de vente", "error");
    return;
  }

  // IMPORTANT: Recharger les données actuelles des produits depuis la BD pour vérifier les stocks réels
  // Cela évite les désynchronisations si d'autres utilisateurs ont modifié les stocks
  const url = `${BASE_URL}Controller/produit/get_produits.php`;

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (!data.success || !Array.isArray(data.data)) {
        showNotification("Impossible de vérifier les stocks actuels", "error");
        return;
      }

      // Créer une map des produits actuels
      const produitsActuels = {};
      data.data.forEach((produit) => {
        produitsActuels[produit.idproduit] = produit;
      });

      // Vérifier que tous les produits de la facture existent et ont assez de stock
      for (const product of addedProducts) {
        const produitActuel = produitsActuels[product.idproduit];

        if (!produitActuel) {
          showNotification(
            `Le produit "${product.designation}" n'existe plus en base de données`,
            "error"
          );
          return;
        }

        const stockActuel = parseInt(produitActuel.quantite) || 0;

        if (product.quantite > stockActuel) {
          showNotification(
            `Stock insuffisant pour "${product.designation}". ` +
              `Stock réel: ${stockActuel} unité(s), demandé: ${product.quantite} unité(s)`,
            "error"
          );
          return;
        }
      }

      // Si on arrive ici, tous les stocks sont OK, on peut valider la vente
      validateAndSubmitSale(
        client,
        telephone,
        dateSale,
        prixRecu,
        remboursement
      );
    })
    .catch((err) => {
      console.error("Erreur lors de la vérification des stocks:", err);
      showNotification("Erreur lors de la vérification des stocks", "error");
    });
}

// Fonction pour valider et soumettre la vente après vérification des stocks
function validateAndSubmitSale(
  client,
  telephone,
  dateSale,
  prixRecu,
  remboursement
) {
  // Créer la facture avec tous les produits
  const totalHT = addedProducts.reduce(
    (sum, p) => sum + p.prixUnitaire * p.quantite,
    0
  );
  const tauxReduction =
    parseFloat(document.getElementById("tauxReduction").value) || 0;
  const montantReduction = (totalHT * tauxReduction) / 100;
  const totalApresReduction = totalHT - montantReduction;
  const moyenPaiement = document.getElementById("moyenPaiement").value || "";

  // Validation du moyen de paiement
  if (!moyenPaiement) {
    showNotification("Veuillez sélectionner un moyen de paiement", "error");
    return;
  }

  // Validation du paiement
  if (prixRecu < totalApresReduction) {
    showNotification(
      "Le montant reçu doit être au moins égal au total après réduction",
      "error"
    );
    return;
  }

  // Envoyer les données au serveur
  const productsToSend = addedProducts.map((p) => ({
    idproduit: p.idproduit,
    designation: p.designation,
    caracteristique: p.caracteristique,
    quantite: p.quantite,
    prixUnitaire: p.prixUnitaire,
    finGarantie: document.getElementById("fin_garantie").value,
  }));

  const formData = new FormData();
  formData.append("client", client);
  formData.append("telephone", telephone);
  formData.append("date_vente", dateSale);
  formData.append("totalHT", totalHT);
  formData.append("tauxReduction", tauxReduction);
  formData.append("montantReduction", montantReduction);
  formData.append("totalApresReduction", totalApresReduction);
  formData.append("prixRecu", prixRecu);
  formData.append("remboursement", remboursement);
  formData.append("moyenPaiement", moyenPaiement);
  formData.append("produits", JSON.stringify(productsToSend));

  fetch(`${BASE_URL}Controller/vente/addVentes.php`, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      // Vérifier que la réponse est du texte avant de parser
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        return response.text().then((text) => {
          console.error("Réponse non-JSON reçue:", text);
          throw new Error(
            "Réponse du serveur invalide: " + text.substring(0, 200)
          );
        });
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        showNotification("Vente enregistrée avec succès!", "success");
        setTimeout(() => {
          document.getElementById("venteModal").classList.remove("active");
          resetForm();
        }, 1500);
      } else {
        showNotification(
          "Erreur: " + (data.message || "Impossible d'enregistrer la vente"),
          "error"
        );
      }
    })
    .catch((err) => {
      console.error("Erreur:", err);
      showNotification("Erreur: " + err.message, "error");
    });
}

// === UTILITAIRES ===
function formatCurrency(value) {
  return new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "XAF",
    maximumFractionDigits: 0,
  }).format(value);
}

// Définir la date d'aujourd'hui
function setDateToday() {
  const today = new Date().toISOString().split("T")[0];
  document.getElementById("date_vente").value = today;
}

function formatDate(dateString) {
  if (!dateString) return "-";
  const options = { year: "numeric", month: "long", day: "numeric" };
  return new Date(dateString + "T00:00:00").toLocaleDateString(
    "fr-FR",
    options
  );
}

// Fonction pour afficher les notifications
function showNotification(message, type = "success") {
  const notification = document.getElementById("notification");
  if (!notification) {
    console.error("Notification element not found");
    alert(`${type}: ${message}`);
    return;
  }
  notification.textContent = message;
  notification.className = `notification ${type}`;

  // Forcer le navigateur à recalculer les styles
  notification.offsetHeight;

  setTimeout(() => {
    notification.style.opacity = "0";
    setTimeout(() => {
      notification.classList.remove("success", "error", "warning");
      notification.style.opacity = "1";
    }, 300);
  }, 5000);
  console.log(`Notification (${type}): ${message}`);
}

// Mettre à jour la facture lors du changement de certains champs
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("client").addEventListener("input", updateInvoice);
  document.getElementById("telephone").addEventListener("input", updateInvoice);
  document
    .getElementById("date_vente")
    .addEventListener("change", updateInvoice);
  document
    .getElementById("fin_garantie")
    .addEventListener("change", updateInvoice);
  document.getElementById("prixRecu").addEventListener("input", function () {
    calculateRemboursement();
    updateInvoice();
  });
});

// === GÉNÉRATION DE RÉFÉRENCE ET PDF ===

// Générer une référence de facture unique
function generateInvoiceReference() {
  const today = new Date();
  const year = today.getFullYear().toString().slice(-2); // Derniers 2 chiffres de l'année
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  const timestamp = Date.now().toString().slice(-6); // Les 6 derniers chiffres du timestamp
  const reference = `FAC-${year}${month}${day}-${timestamp}`;
  return reference;
}

// Imprimer la facture directement
function generatePDF(sale = null, details = null) {
  let invoiceRef = generateInvoiceReference();
  let invoiceClient, invoicePhone, invoiceDate, invoiceWarranty, invoicePayment;
  let totalHT, totalApres, totalReceived, totalRefund, totalFinal;
  let showReduction = false;
  let reductionVal = "";
  let lignesProduits = "";

  if (!sale) {
    // Utilisation du modal de création de vente
    if (addedProducts.length === 0) {
      showNotification(
        "Aucun produit à imprimer. Veuillez ajouter des produits.",
        "error"
      );
      return;
    }

    invoiceClient = document.getElementById("invoiceClient").textContent;
    invoicePhone = document.getElementById("invoicePhone").textContent;
    invoiceDate = document.getElementById("invoiceDate").textContent;
    invoiceWarranty = document.getElementById("invoiceWarranty").textContent;
    invoicePayment = document.getElementById(
      "invoicePaymentMethod"
    ).textContent;
    totalHT = document.getElementById("totalHT").textContent;
    totalApres = document.getElementById("invoiceTotalApres").textContent;
    totalReceived = document.getElementById("totalReceived").textContent;
    totalRefund = document.getElementById("totalRefund").textContent;
    totalFinal = document.getElementById("totalFinal").textContent;

    const reductionRowEl = document.getElementById("invoiceReductionRow");
    reductionVal = document.getElementById("invoiceReduction").textContent;
    showReduction = reductionRowEl.style.display !== "none";

    addedProducts.forEach((p) => {
      const montant = p.prixUnitaire * p.quantite;
      lignesProduits += `
      <tr>
        <td>${p.designation}</td>
        <td>${p.caracteristique}</td>
        <td style="text-align:center">${p.quantite}</td>
        <td style="text-align:right">${formatCurrency(p.prixUnitaire)}</td>
        <td style="text-align:right">${formatCurrency(montant)}</td>
      </tr>`;
    });

    // Mettre à jour le ref visible du modal facture si présent
    const invRefEl = document.getElementById("invoiceRef");
    if (invRefEl) invRefEl.textContent = invoiceRef;
  } else {
    // Utilisation des données d'une vente existante
    invoiceClient = sale.client || "-";
    invoicePhone = sale.telephone || "-";
    invoiceDate = formatDate(sale.date_vente);
    invoiceWarranty = sale.finGarantie ? formatDate(sale.finGarantie) : "-";
    invoicePayment = sale.moyenPaiement || sale.moyen_paiement || "-";

    const rawTotal = parseFloat(sale.montant ?? sale.totalHT ?? 0) || 0;
    const rawReduction = parseFloat(sale.montantReduction) || 0;
    totalHT = formatCurrency(rawTotal);
    totalApres = formatCurrency(rawTotal - rawReduction);
    totalReceived = formatCurrency(parseFloat(sale.prixRecu) || 0);
    totalRefund = formatCurrency(parseFloat(sale.remboursement) || 0);
    totalFinal = formatCurrency(rawTotal - rawReduction);
    showReduction = rawReduction > 0;
    reductionVal =
      formatCurrency(rawReduction) +
      (sale.tauxReduction
        ? ` (${parseFloat(sale.tauxReduction).toFixed(2)}%)`
        : "");

    const usedDetails = Array.isArray(details) ? details : [];
    usedDetails.forEach((item) => {
      const montant =
        parseFloat(item.montant) ||
        (parseFloat(item.prixUnitaire) || 0) * (parseInt(item.quantite) || 0);
      lignesProduits += `
      <tr>
        <td>${item.designation ?? "-"}</td>
        <td>${item.caracteristique ?? "-"}</td>
        <td style="text-align:center">${item.quantite}</td>
        <td style="text-align:right">${formatCurrency(
          item.prixUnitaire || 0
        )}</td>
        <td style="text-align:right">${formatCurrency(montant)}</td>
      </tr>`;
    });
  }

  // HTML complet de la facture (autonome, sans dépendance externe)
  const factureHTML = `<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Facture ${invoiceRef}</title>
  <style>
    /* ── Reset ── */
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    /* ── Mode A4 par défaut ── */
    body {
      font-family: Arial, Helvetica, sans-serif;
      background: white;
      color: #000;
      padding: 20mm 15mm;
      width: 210mm;
      min-height: 297mm;
    }

    .facture-wrapper {
      max-width: 180mm;
      margin: 0 auto;
    }

    /* ── En-tête ── */
    .invoice-header {
      text-align: center;
      margin-bottom: 12mm;
      padding-bottom: 5mm;
      border-bottom: 2px solid #000;
    }
    .invoice-header .shop-name  { font-size: 18pt; font-weight: bold; }
    .invoice-header .shop-tel   { font-size: 10pt; margin-top: 2mm; }
    .invoice-header .facture-label {
      font-size: 14pt; font-weight: bold;
      margin-top: 3mm;
      text-transform: uppercase;
      letter-spacing: 2px;
    }
    .invoice-header .facture-ref { font-size: 9pt; margin-top: 2mm; color: #444; }

    /* ── Infos client ── */
    .invoice-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2mm 6mm;
      margin-bottom: 8mm;
      font-size: 10pt;
    }
    .invoice-info-item { display: flex; gap: 4px; }
    .invoice-info-item strong { min-width: 30mm; color: #333; }

    /* ── Tableau produits ── */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10pt;
      margin-bottom: 6mm;
    }
    thead tr { background: #1e3a5f; color: white; }
    th { padding: 3mm 2mm; text-align: left; font-weight: bold; font-size: 9pt; }
    td { padding: 2.5mm 2mm; border-bottom: 1px solid #e5e7eb; }
    tbody tr:nth-child(even) { background: #f9fafb; }

    /* ── Totaux ── */
    .totals-section {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 8mm;
    }
    .totals-box { width: 80mm; font-size: 10pt; }
    .total-line {
      display: flex;
      justify-content: space-between;
      padding: 1.5mm 0;
      border-bottom: 1px solid #e5e7eb;
    }
    .total-line.final {
      border-top: 2px solid #000;
      border-bottom: 2px solid #000;
      padding: 2mm 0;
      font-weight: bold;
      font-size: 12pt;
      margin-top: 1mm;
    }
    .total-line.reduction { color: #dc2626; }

    /* ── Pied de page ── */
    .invoice-footer {
      text-align: center;
      font-size: 9pt;
      color: #666;
      margin-top: 10mm;
      padding-top: 4mm;
      border-top: 1px solid #ccc;
    }

    /* ── Bouton mode ticket (visible à l'écran, masqué à l'impression) ── */
    .print-actions {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-bottom: 8mm;
    }
    .print-actions button {
      padding: 8px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
    }
    .btn-a4     { background: #2563eb; color: white; }
    .btn-ticket { background: #059669; color: white; }

    @media print {
      .print-actions { display: none !important; }
    }

    /* ── Mode ticket 80mm ── */
    body.mode-ticket {
      width: 80mm;
      padding: 3mm;
      min-height: auto;
      font-size: 8pt;
    }
    body.mode-ticket .facture-wrapper { max-width: 74mm; }
    body.mode-ticket .invoice-header .shop-name    { font-size: 11pt; }
    body.mode-ticket .invoice-header .shop-tel     { font-size: 8pt; }
    body.mode-ticket .invoice-header .facture-label{ font-size: 10pt; }
    body.mode-ticket .invoice-header .facture-ref  { font-size: 7pt; }
    body.mode-ticket .invoice-header               { margin-bottom: 3mm; padding-bottom: 2mm; border-bottom: 1px dashed #000; }

    body.mode-ticket .invoice-info { grid-template-columns: 1fr; gap: 0.5mm; font-size: 8pt; margin-bottom: 3mm; }
    body.mode-ticket .invoice-info-item strong { min-width: 20mm; }

    body.mode-ticket table      { font-size: 7.5pt; margin-bottom: 3mm; }
    body.mode-ticket thead tr   { background: white; color: black; }
    body.mode-ticket th         { padding: 1mm; border-bottom: 1px solid #000; font-size: 7pt; }
    body.mode-ticket td         { padding: 1mm; border-bottom: none; }
    body.mode-ticket tbody tr:nth-child(even) { background: white; }
    body.mode-ticket tr:last-child td { border-bottom: 1px solid #000; }

    body.mode-ticket .totals-section { justify-content: flex-start; margin-bottom: 3mm; }
    body.mode-ticket .totals-box     { width: 100%; font-size: 7.5pt; }
    body.mode-ticket .total-line     { padding: 0.8mm 0; border-bottom: none; }
    body.mode-ticket .total-line.final { font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000; }

    body.mode-ticket .invoice-footer { font-size: 7pt; margin-top: 3mm; padding-top: 2mm; }

    @page { size: auto; margin: 0mm; }
    @page:body.mode-ticket { size: 80mm auto; margin: 3mm; }
  </style>
</head>
<body>
  <!-- Boutons de choix d'impression (disparaissent à l'impression) -->
  <div class="print-actions">
    <button class="btn-a4" onclick="imprimerA4()">🖨️ Imprimer A4</button>
    <button class="btn-ticket" onclick="imprimerTicket()">🧾 Imprimer Ticket (80mm)</button>
  </div>

  <div class="facture-wrapper">
    <!-- En-tête boutique -->
    <div class="invoice-header">
      <div class="shop-name">RAOUL PC SHOP</div>
      <div class="shop-tel">658688907 / 653770286</div>
      <div class="facture-label">Facture</div>
      <div class="facture-ref">Réf : ${invoiceRef}</div>
    </div>

    <!-- Infos client -->
    <div class="invoice-info">
      <div class="invoice-info-item"><strong>Client :</strong> ${invoiceClient}</div>
      <div class="invoice-info-item"><strong>Téléphone :</strong> ${invoicePhone}</div>
      <div class="invoice-info-item"><strong>Date :</strong> ${invoiceDate}</div>
      <div class="invoice-info-item"><strong>Fin garantie :</strong> ${invoiceWarranty}</div>
    </div>

    <!-- Tableau produits -->
    <table>
      <thead>
        <tr>
          <th>Produit</th>
          <th>Caractéristiques</th>
          <th style="text-align:center">Qté</th>
          <th style="text-align:right">P.U (FCFA)</th>
          <th style="text-align:right">Montant (FCFA)</th>
        </tr>
      </thead>
      <tbody>
        ${lignesProduits}
      </tbody>
    </table>

    <!-- Totaux -->
    <div class="totals-section">
      <div class="totals-box">
        <div class="total-line"><span>Total HT</span><span>${totalHT}</span></div>
        ${
          showReduction
            ? `<div class="total-line reduction"><span>Réduction</span><span>- ${reductionVal}</span></div>`
            : ""
        }
        <div class="total-line"><span>Total après réduction</span><span>${totalApres}</span></div>
        <div class="total-line"><span>Moyen de paiement</span><span>${invoicePayment}</span></div>
        <div class="total-line"><span>Prix reçu</span><span>${totalReceived}</span></div>
        <div class="total-line"><span>Remboursement</span><span>${totalRefund}</span></div>
        <div class="total-line final"><span>NET À PAYER</span><span>${totalFinal}</span></div>
      </div>
    </div>

    <!-- Pied de page -->
    <div class="invoice-footer">Merci pour votre achat et à bientôt !</div>
  </div>

  <script>
    function imprimerA4() {
      document.body.classList.remove("mode-ticket");
      window.print();
    }
    function imprimerTicket() {
      document.body.classList.add("mode-ticket");
      window.print();
      // Remettre en A4 après impression
      setTimeout(() => document.body.classList.remove("mode-ticket"), 1000);
    }
    // Lancer l'impression A4 automatiquement à l'ouverture
    window.onload = function() {
      // Petite attente pour que la page soit rendue
      setTimeout(imprimerA4, 300);
    };
  </script>
</body>
</html>`;

  // Ouvrir la fenêtre d'impression
  const printWindow = window.open("", "_blank", "width=900,height=700");
  if (!printWindow) {
    showNotification(
      "Le navigateur a bloqué l'ouverture. Autorisez les popups pour ce site.",
      "error"
    );
    return;
  }
  printWindow.document.write(factureHTML);
  printWindow.document.close();
}

// Imprimer la facture pour la vente actuellement affichée dans le modal détails
function printViewedSale() {
  if (!currentViewedSale) {
    showNotification("Aucune vente sélectionnée pour l'impression", "error");
    return;
  }

  const vente = currentViewedSale;
  const details = Array.isArray(currentViewedDetails)
    ? currentViewedDetails
    : [];
  const invoiceRef = generateInvoiceReference();

  const totalHT = parseFloat(vente.montant) || parseFloat(vente.totalHT) || 0;
  const montantReduction = parseFloat(vente.montantReduction) || 0;
  const totalApres = totalHT - montantReduction;
  const prixRecu = parseFloat(vente.prixRecu) || 0;
  const remboursement = parseFloat(vente.remboursement) || 0;

  let lignesProduits = "";
  details.forEach((item) => {
    const montant =
      parseFloat(item.montant) ||
      (parseFloat(item.prixUnitaire) || 0) * (parseInt(item.quantite) || 0);
    lignesProduits += `
      <tr>
        <td>${item.designation ?? "-"}</td>
        <td>${item.caracteristique ?? "-"}</td>
        <td style="text-align:center">${item.quantite}</td>
        <td style="text-align:right">${formatCurrency(
          item.prixUnitaire || 0
        )}</td>
        <td style="text-align:right">${formatCurrency(montant)}</td>
      </tr>`;
  });

  const reductionRow =
    montantReduction > 0
      ? `
    <tr class="total-line reduction">
      <td colspan="2">Réduction</td>
      <td colspan="3" style="text-align:right">- ${formatCurrency(
        montantReduction
      )}</td>
    </tr>`
      : "";

  const factureHTML = `<!DOCTYPE html>
<html lang="fr"><head><meta charset="utf-8"><title>Facture ${invoiceRef}</title>
<style>body{font-family:Arial;padding:20px;color:#000}table{width:100%;border-collapse:collapse}th,td{padding:6px;border-bottom:1px solid #eee}thead{background:#1e3a5f;color:#fff}</style>
</head><body>
<h2 style="text-align:center">RAOUL PC SHOP</h2>
<p style="text-align:center">Réf: ${invoiceRef}</p>
<div><strong>Client:</strong> ${
    vente.client || "-"
  }<br><strong>Téléphone:</strong> ${
    vente.telephone || "-"
  }<br><strong>Date:</strong> ${formatDate(vente.date_vente)}</div>
<table><thead><tr><th>Produit</th><th>Caractéristiques</th><th>Qté</th><th style="text-align:right">P.U</th><th style="text-align:right">Montant</th></tr></thead><tbody>${lignesProduits}</tbody></table>
<div style="margin-top:10px; text-align:right;">
<div>Total HT: ${formatCurrency(totalHT)}</div>
${
  montantReduction > 0
    ? `<div>Réduction: - ${formatCurrency(montantReduction)}</div>`
    : ""
}
<div>Total après réduction: ${formatCurrency(totalApres)}</div>
<div>Prix reçu: ${formatCurrency(prixRecu)}</div>
<div>Remboursement: ${formatCurrency(remboursement)}</div>
</div>
</body></html>`;

  const printWindow = window.open("", "_blank", "width=900,height=700");
  if (!printWindow)
    return showNotification(
      "Le navigateur a bloqué l'ouverture. Autorisez les popups.",
      "error"
    );
  printWindow.document.write(factureHTML);
  printWindow.document.close();
}
