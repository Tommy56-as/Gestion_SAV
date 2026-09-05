// Variables globales
const btnUpdate = document.getElementById("updateFournisseur");
const btnAdd = document.getElementById("saveFournisseur");
let editingFournisseurId = null;
const BASE_URL = window.location.origin + "/GESTION_SAV/";

// Fonction pour construire l'URL correcte
function getApiUrl(endpoint) {
  return `${window.location.origin}/GESTION_SAV/Controller/fournisseur/${endpoint}`;
}
// Fonction pour charger les fournisseurs
function loadFournisseurs() {
  fetch(getApiUrl("getAllFournisseur.php"))
    .then((response) => response.json())
    .then((data) => {
      if (data) {
        const container = document.getElementById("FournisseurContainer");
        if (!data.success || data.fournisseurs.length === 0) {
          container.innerHTML = `
                    <div class="no-users">
                        <span class="material-icons-sharp">person_off</span>
                        <h3>Aucun fournisseur trouvé</h3>
                        <p>Ajoutez votre premier fournisseur.</p>
                    </div>
                `;
          return;
        }

        container.innerHTML = "";

        data.fournisseurs.forEach((fournisseur) => {
          const status = fournisseur.statut ?? 0;
          const fournisseurId = fournisseur.idfour ?? 0;

          container.innerHTML += `
                    <div class="user-card ${
                      status == 1 ? "archiver" : "active"
                    }" id="user-card-${fournisseurId}">
                        
                        <div class="user-status ${
                          status == 1 ? "status-blocked" : "status-active"
                        }">
                            ${
                              status == 1
                                ? `<span class="material-icons-sharp">lock</span> Archivé`
                                : `<span class="material-icons-sharp">check_circle</span> Actif`
                            }
                        </div>

                        <div class="user-info">
                            <h3 class="user-name">
                                <span class="material-icons-sharp">person</span>
                                ${escapeHtml(
                                  fournisseur.nom ?? "Non défini"
                                )} <span>${escapeHtml(
            fournisseur.prenom ?? "Non défini"
          )}</span>
                            </h3>

                            <div class="user-detail">
                                <span class="material-icons-sharp">badge</span>
                                <span>${escapeHtml(
                                  fournisseur.adresse ?? "Non défini"
                                )}</span>
                            </div>

                            <div class="user-detail">
                                <span class="material-icons-sharp">phone</span>
                                <span>${escapeHtml(
                                  fournisseur.telephone ?? "Non défini"
                                )}</span>
                            </div>
                            <div class="user-detail">
                                <span class="material-icons-sharp">inventory_2</span>
                                <span>${escapeHtml(
                                  fournisseur.produit_livres ?? "Non défini"
                                )}</span>
                            </div>
                        </div>

                        <div class="user-actions">
                            <button class="btn btn-secondary btn-sm" onclick="editFournisseur(${fournisseurId})">
                                <span class="material-icons-sharp">edit</span>
                            </button>

                            ${
                              status == 1
                                ? `<button class="btn btn-success btn-sm" onclick="restaurerFournisseur(${fournisseurId})">
                                        <span class="material-icons-sharp">lock_open</span>
                                   </button>`
                                : `<button class="btn btn-danger btn-sm" onclick="archiverFournisseur(${fournisseurId})">
                                        <span class="material-icons-sharp">lock</span>
                                   </button>`
                            }
                            <button class="btn btn-delete btn-sm" onclick="deleteFournisseur(${fournisseurId})">
                                <span class="material-icons-sharp">delete</span>
                            </button>
                        </div>
                    </div>
                `;
        });
      } else {
        showNotification("Erreur lors du chargement des fournisseurs", "error");
      }
    })
    .catch((error) => {
      showNotification("Erreur réseau ou serveur", "error");
      console.error("Erreur:", error);
    });
}
// Fonction pour ajouter un fournsseur
function saveFournisseur() {
  const form = document.getElementById("fournisseurForm");
  const formData = new FormData(form);
  const endpoint = "addFournisseur.php";
  fetch(getApiUrl(endpoint), {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message || "Succès");
        resetForm();
        loadFournisseurs();
      } else {
        showNotification(data.message || "error");
      }
    })
    .catch((error) => {
      console.error(error);
      showNotification("Erreur serveur", "error");
    });
}
document
  .getElementById("saveFournisseur")
  .addEventListener("click", function () {
    saveFournisseur();
    close();
  });
document
  .getElementById("updateFournisseur")
  .addEventListener("click", function () {
    updateFournisseur();
    console.log("Fournisseur mis à jour");
  });
//ouvrir le modal d'ajout fournisseur
const modalFournisseur = document.getElementById("fournisseurModal");
function openAddFournisseurModal() {
  const addFournisseurBtn = document.getElementById("addFournisseurBtn");
  addFournisseurBtn.addEventListener("click", () => {
    document.getElementById("modalTitle").textContent =
      " Ajouter un fournisseur";
    modalFournisseur.style.display = "flex";
  });
}
// Fonction pour close le modal
function close() {
  document.getElementById("closeModal").addEventListener("click", function () {
    modalFournisseur.style.display = "none";
  });
}

function loadCaracteristiques(designation, selectedProductId = "") {
  const caracteristiqueSelect = document.getElementById("produitLivre");

  caracteristiqueSelect.innerHTML =
    '<option value="">Sélectionner une caractéristique</option>';
  caracteristiqueSelect.disabled = true;

  if (!designation) return Promise.resolve();

  const url = getApiUrl(
    `caracteristique.php?designation=${encodeURIComponent(designation)}`
  );

  console.log("URL caractéristiques:", url);

  return fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }
      return response.json();
    })
    .then((data) => {
      console.log("Caractéristiques reçues:", data);

      if (!Array.isArray(data)) {
        throw new Error("Réponse invalide");
      }

      data.forEach((item) => {
        const option = document.createElement("option");
        option.value = item.idproduit;
        option.textContent = item.caracteristique;
        caracteristiqueSelect.appendChild(option);
      });

      caracteristiqueSelect.disabled = false;
      if (selectedProductId)
        caracteristiqueSelect.value = String(selectedProductId);
    })
    .catch((error) => {
      console.error("Erreur chargement caractéristiques:", error);
      showNotification("Erreur lors du chargement des caractéristiques");
    });
}

document.getElementById("designation").addEventListener("change", function () {
  loadCaracteristiques(this.value);
});
//foncton pour etiter un fournisseur
function editFournisseur(fournisseurId) {
  fetch(getApiUrl(`get_Fournisseur.php?idfour=${fournisseurId}`))
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const fournisseur = data.data[0];
        const produit = data.data[0];
        editingFournisseurId = fournisseur.idfour;
        document.getElementById("idfour").value = fournisseur.idfour;
        document.getElementById("nom").value = fournisseur.nom || "";
        document.getElementById("prenom").value = fournisseur.prenom || "";
        document.getElementById("telephone").value =
          fournisseur.telephone || "";
        document.getElementById("adresse").value = fournisseur.adresse || "";
        // le champ designation et produitLivre ne sent pas rempli automatiquement, il faut corriger ça
        document.getElementById("designation").value =
          produit.designation || "";
        loadCaracteristiques(produit.designation || "", produit.produitLivre);
        document.getElementById("modalTitle").textContent =
          " Modifier le fournisseur";
        btnUpdate.style.display = "flex";
        btnAdd.style.display = "none";
        modalFournisseur.style.display = "flex";
      } else {
        showNotification(
          data.message || "Erreur lors du chargement du fournisseur",
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      showNotification("Erreur réseau ou serveur", "error");
    });
}
// Fonction pour mettre à jour un fournisseur
function updateFournisseur() {
  if (!editingFournisseurId) {
    showNotification("ID fournisseur manquant", "error");
    return;
  }

  const formData = new FormData();
  formData.append("idfour", editingFournisseurId);
  formData.append("nom", document.getElementById("nom").value);
  formData.append("prenom", document.getElementById("prenom").value);
  formData.append("telephone", document.getElementById("telephone").value);
  formData.append("adresse", document.getElementById("adresse").value);
  formData.append("designation", document.getElementById("designation").value);
  formData.append(
    "produitLivre",
    document.getElementById("produitLivre").value
  );

  const url = getApiUrl("update_fournisseur.php");

  fetch(url, {
    method: "POST",
    body: formData,
  })
    .then(async (response) => {
      const text = await response.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (error) {
        console.error("Réponse non JSON du serveur:", text);
        throw new Error("Le serveur n'a pas renvoyé une réponse valide");
      }
      if (!response.ok) {
        throw new Error(data.message || `Erreur HTTP: ${response.status}`);
      }
      return data;
    })
    .then((data) => {
      if (data.success) {
        showNotification(data.message || "Fournisseur modifié avec succès");
        resetForm();
        loadFournisseurs();
        modalFournisseur.style.display = "none";
      } else {
        showNotification(
          data.message || "Erreur lors de la modification",
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Erreur updateFournisseur:", error);
      showNotification("Erreur serveur: " + error.message, "error");
    });
}
// Fonction pour réinitialiser le formulaire
function resetForm() {
  document.getElementById("idfour").value = "";
  editingFournisseurId = null;
  document.getElementById("nom").value = "";
  document.getElementById("prenom").value = "";
  document.getElementById("telephone").value = "";
  document.getElementById("adresse").value = "";
  document.getElementById("designation").value = "";
  document.getElementById("produitLivre").value = "";
}

function deleteFournisseur(fournisseurId) {
  if (
    !confirm(
      "Voulez-vous supprimer ce fournisseur ? Il sera masqué mais conservé dans l'historique."
    )
  )
    return;
  const formData = new FormData();
  formData.append("idfour", fournisseurId);
  fetch(getApiUrl("delete_fournisseur.php"), { method: "POST", body: formData })
    .then((response) => response.json())
    .then((data) => {
      showNotification(
        data.message || "Opération terminée",
        data.success ? "success" : "error"
      );
      if (data.success) loadFournisseurs();
    })
    .catch(() => showNotification("Erreur lors de la suppression", "error"));
}

function modifierStatutFournisseur(fournisseurId, statut) {
  const formData = new FormData();
  formData.append("idfour", fournisseurId);
  formData.append("statut", statut);

  fetch(getApiUrl("update_statut_fournisseur.php"), {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      showNotification(
        data.message || "Opération terminée",
        data.success ? "success" : "error"
      );
      if (data.success) loadFournisseurs();
    })
    .catch(() =>
      showNotification("Erreur lors de la mise à jour du statut", "error")
    );
}

function archiverFournisseur(fournisseurId) {
  if (confirm("Voulez-vous archiver ce fournisseur ?")) {
    modifierStatutFournisseur(fournisseurId, 1);
  }
}

function restaurerFournisseur(fournisseurId) {
  if (confirm("Voulez-vous restaurer ce fournisseur ?")) {
    modifierStatutFournisseur(fournisseurId, 0);
  }
}

// Charger les fournisseurs au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  loadFournisseurs();
  openAddFournisseurModal();
  close();
});
