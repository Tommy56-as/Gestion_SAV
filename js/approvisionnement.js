const approvisionnementState = {
  produits: [],
  fournisseurs: [],
  commandes: [],
};
const approvisionnementBaseUrl = `${window.location.origin}/Gestion_SAV/`;

// Affiche le résultat des actions sans interrompre le workflow de l'utilisateur.
const notifyApprovisionnement = (message, type = "success") => {
  const notification = document.getElementById("notification");
  notification.textContent = message;
  notification.className = `notification ${type}`;
  setTimeout(() => (notification.className = "notification"), 3500);
};

async function loadApprovisionnementData() {
  // Charger en parallèle les listes nécessaires au formulaire et au tableau.
  const [produitsResponse, fournisseursResponse, commandesResponse] =
    await Promise.all([
      fetch(`${approvisionnementBaseUrl}Controller/produit/get_produits.php`),
      fetch(
        `${approvisionnementBaseUrl}Controller/fournisseur/getAllFournisseur.php?actifs=1`
      ),
      fetch(
        `${approvisionnementBaseUrl}Controller/approvisionnemeent/get_approvisionnements.php`
      ),
    ]);
  const produits = await produitsResponse.json();
  const fournisseurs = await fournisseursResponse.json();
  const commandes = await commandesResponse.json();
  approvisionnementState.produits = produits.data || [];
  approvisionnementState.fournisseurs = fournisseurs.fournisseurs || [];
  approvisionnementState.commandes = commandes.data || [];
  fillSelects();
  renderCommandes();
}

function fillSelects() {
  // Les noms sont échappés avant insertion dans le HTML pour éviter l'injection.
  const produitSelect = document.getElementById("commandeProduit");
  const fournisseurSelect = document.getElementById("commandeFournisseur");
  produitSelect.innerHTML = '<option value="">Sélectionner un produit</option>';
  fournisseurSelect.innerHTML = '<option value="">Aucun fournisseur</option>';
  produitSelect.insertAdjacentHTML(
    "beforeend",
    approvisionnementState.produits
      .map(
        (produit) =>
          `<option value="${produit.idproduit}">${escapeHtml(
            produit.designation
          )} - ${escapeHtml(produit.caracteristique)}</option>`
      )
      .join("")
  );
  fournisseurSelect.insertAdjacentHTML(
    "beforeend",
    approvisionnementState.fournisseurs
      .map(
        (fournisseur) =>
          `<option value="${fournisseur.idfour}">${escapeHtml(
            fournisseur.nom
          )} ${escapeHtml(fournisseur.prenom)}</option>`
      )
      .join("")
  );
  document.getElementById("dateApp").value = new Date()
    .toISOString()
    .slice(0, 10);
}

function renderCommandes() {
  // Les filtres sont appliqués localement après le chargement des commandes.
  const filtre = document.getElementById("filtreStatut").value;
  const recherche = document
    .getElementById("rechercheCommande")
    .value.trim()
    .toLowerCase();
  const commandes = approvisionnementState.commandes.filter((commande) => {
    const contenu = [
      commande.produit,
      commande.fournisseur,
      commande.date_app,
      commande.statut,
    ]
      .join(" ")
      .toLowerCase();
    return (
      (!filtre || commande.statut === filtre) &&
      (!recherche || contenu.includes(recherche))
    );
  });
  const body = document.getElementById("commandesBody");
  if (!commandes.length) {
    body.innerHTML =
      '<tr><td colspan="8">Aucune commande enregistrée</td></tr>';
    return;
  }
  body.innerHTML = commandes
    .map(
      (commande) => `<tr>
    <td>${escapeHtml(commande.produit)}</td><td>${escapeHtml(
        commande.fournisseur || "-"
      )}</td>
    <td>${commande.quantite_stock}</td><td>${commande.quantite_app}</td>
    <td>${formatMoney(commande.prix_total)}</td><td>${commande.date_app}</td>
    <td><span class="commande-status ${commande.statut}">${
        commande.statut === "terminee" ? "Terminée" : "En cours"
      }</span></td>
    <td>${
      commande.statut === "encours"
        ? `<button class="commande-action edit" data-action="edit" data-id="${commande.idApp}" title="Modifier la commande"><span class="material-icons-sharp">edit</span></button><button class="commande-action" data-action="complete" data-id="${commande.idApp}" title="Marquer comme terminée"><span class="material-icons-sharp">check</span></button>`
        : `<span title="Une commande terminée ne peut plus être modifiée">Verrouillée</span>`
    }</td>
  </tr>`
    )
    .join("");
  body
    .querySelectorAll(".commande-action")
    .forEach((button) =>
      button.addEventListener("click", () =>
        button.dataset.action === "edit"
          ? openEditCommande(button.dataset.id)
          : updateCommande(button.dataset.id, "terminee")
      )
    );
}

async function updateCommande(idApp, statut) {
  // Le serveur gère la transaction qui synchronise statut et stock.
  const data = new FormData();
  data.append("idApp", idApp);
  data.append("statut", statut);
  const response = await fetch(
    `${approvisionnementBaseUrl}Controller/approvisionnemeent/update_approvisionnement.php`,
    { method: "POST", body: data }
  );
  const result = await response.json();
  notifyApprovisionnement(result.message, result.success ? "success" : "error");
  if (result.success) await loadApprovisionnementData();
}

function openCommandeModal() {
  const modal = document.getElementById("commandeModal");
  modal.classList.add("is-open");
  modal.setAttribute("aria-hidden", "false");
}

function closeCommandeModal() {
  const modal = document.getElementById("commandeModal");
  modal.classList.remove("is-open");
  modal.setAttribute("aria-hidden", "true");
}

function resetCommandeForm() {
  document.getElementById("commandeForm").reset();
  document.getElementById("idApp").value = "";
  document.getElementById("commandeModalTitle").innerHTML =
    '<span class="material-icons-sharp">add_shopping_cart</span> Nouvelle commande';
  document.getElementById("saveCommande").innerHTML =
    '<span class="material-icons-sharp">save</span> Enregistrer';
  document.getElementById("dateApp").value = new Date()
    .toISOString()
    .slice(0, 10);
  document.getElementById("stockActuel").textContent = "Stock actuel : -";
}

function openEditCommande(idApp) {
  const commande = approvisionnementState.commandes.find(
    (item) => String(item.idApp) === String(idApp)
  );
  if (!commande || commande.statut === "terminee") {
    notifyApprovisionnement(
      "Une commande terminée ne peut plus être modifiée",
      "error"
    );
    return;
  }
  document.getElementById("idApp").value = commande.idApp;
  document.getElementById("commandeProduit").value = commande.idproduit;
  document.getElementById("commandeFournisseur").value = commande.idfour || "";
  document.getElementById("quantiteApp").value = commande.quantite_app;
  document.getElementById("prixTotal").value = commande.prix_total || "";
  document.getElementById("dateApp").value = commande.date_app;
  document.getElementById("commandeProduit").dispatchEvent(new Event("change"));
  document.getElementById("commandeModalTitle").innerHTML =
    '<span class="material-icons-sharp">edit</span> Modifier la commande';
  document.getElementById("saveCommande").innerHTML =
    '<span class="material-icons-sharp">save</span> Enregistrer les modifications';
  openCommandeModal();
}

document
  .getElementById("commandeProduit")
  .addEventListener("change", (event) => {
    // Afficher le stock courant du produit sélectionné avant la commande.
    const produit = approvisionnementState.produits.find(
      (item) => String(item.idproduit) === event.target.value
    );
    document.getElementById("stockActuel").textContent = `Stock actuel : ${
      produit ? produit.quantite : "-"
    }`;
  });
document
  .getElementById("filtreStatut")
  .addEventListener("change", renderCommandes);
document
  .getElementById("rechercheCommande")
  .addEventListener("input", renderCommandes);
document.getElementById("openCommandeModal").addEventListener("click", () => {
  resetCommandeForm();
  openCommandeModal();
});
document
  .getElementById("closeCommandeModal")
  .addEventListener("click", closeCommandeModal);
document
  .getElementById("resetCommande")
  .addEventListener("click", resetCommandeForm);
document.getElementById("commandeModal").addEventListener("click", (event) => {
  if (event.target.id === "commandeModal") closeCommandeModal();
});
document
  .getElementById("commandeForm")
  .addEventListener("submit", async (event) => {
    // Envoyer les champs du formulaire au endpoint de création.
    event.preventDefault();
    const data = new FormData();
    data.append("idproduit", document.getElementById("commandeProduit").value);
    data.append("idfour", document.getElementById("commandeFournisseur").value);
    data.append("quantite_app", document.getElementById("quantiteApp").value);
    data.append("prix_total", document.getElementById("prixTotal").value);
    data.append("date_app", document.getElementById("dateApp").value);
    const idApp = document.getElementById("idApp").value;
    const endpoint = idApp
      ? `${approvisionnementBaseUrl}Controller/approvisionnemeent/edit_approvisionnement.php`
      : `${approvisionnementBaseUrl}Controller/approvisionnemeent/add_approvisionnement.php`;
    if (idApp) data.append("idApp", idApp);
    const response = await fetch(endpoint, { method: "POST", body: data });
    const result = await response.json();
    notifyApprovisionnement(
      result.message,
      result.success ? "success" : "error"
    );
    if (result.success) {
      resetCommandeForm();
      closeCommandeModal();
      await loadApprovisionnementData();
    }
  });

function formatMoney(value) {
  return value ? `${Number(value).toLocaleString("fr-FR")} FCFA` : "-";
}
function escapeHtml(value) {
  return String(value ?? "").replace(
    /[&<>'"]/g,
    (character) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#039;", '"': "&quot;" }[
        character
      ])
  );
}
loadApprovisionnementData().catch(() =>
  notifyApprovisionnement("Impossible de charger les commandes", "error")
);
