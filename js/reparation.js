const reparationBaseUrl = `${window.location.origin}/Gestion_SAV/`;
const reparationState = {
  techniciens: [],
  equipements: [],
  reparations: [],
  pieces: [],
};

function notifyReparation(message, type = "success") {
  const notification = document.getElementById("notification");
  notification.textContent = message;
  notification.className = `notification ${type}`;
  setTimeout(() => (notification.className = "notification"), 3500);
}
function formatMoney(value) {
  return `${Number(value || 0).toLocaleString("fr-FR")} FCFA`;
}
function escapeReparationHtml(value) {
  return String(value ?? "").replace(
    /[&<>'"]/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#039;", '"': "&quot;" }[
        c
      ])
  );
}

async function loadReparationData() {
  const [formResponse, repairsResponse] = await Promise.all([
    fetch(`${reparationBaseUrl}Controller/reparation/get_form_data.php`),
    fetch(`${reparationBaseUrl}Controller/reparation/get_reparations.php`),
  ]);
  const formData = await formResponse.json();
  const repairsData = await repairsResponse.json();
  reparationState.techniciens = formData.techniciens || [];
  reparationState.equipements = formData.equipements || [];
  reparationState.reparations = repairsData.data || [];
  fillReparationSelects();
  renderReparations();
}
function fillReparationSelects() {
  document.getElementById("iduser").innerHTML =
    '<option value="">Sélectionner un technicien</option>' +
    reparationState.techniciens
      .map(
        (t) =>
          `<option value="${t.idUser}">${escapeReparationHtml(
            t.NomComplet || t.Nom_Utilisateur
          )}</option>`
      )
      .join("");
  document.getElementById("idproduit").innerHTML =
    '<option value="">Sélectionner une pièce</option>' +
    reparationState.equipements
      .map(
        (p) =>
          `<option value="${p.idproduit}">${escapeReparationHtml(
            p.designation
          )} - ${escapeReparationHtml(p.caracteristique)} (${formatMoney(
            p.prixUnitaire
          )})</option>`
      )
      .join("");
}
function renderPieces() {
  document.getElementById("piecesBody").innerHTML = reparationState.pieces
    .map(
      (p, index) =>
        `<tr><td>${escapeReparationHtml(
          p.designation
        )} - ${escapeReparationHtml(p.caracteristique)}</td><td>${
          p.quantite
        }</td><td>${formatMoney(p.prixUnitaire)}</td><td>${formatMoney(
          p.quantite * p.prixUnitaire
        )}</td><td><button type="button" class="piece-remove" data-index="${index}"><span class="material-icons-sharp">delete</span></button></td></tr>`
    )
    .join("");
  document.querySelectorAll(".piece-remove").forEach((button) =>
    button.addEventListener("click", () => {
      reparationState.pieces.splice(Number(button.dataset.index), 1);
      renderPieces();
    })
  );
}
function renderReparations() {
  const body = document.getElementById("reparationsBody");
  if (!reparationState.reparations.length) {
    body.innerHTML =
      '<tr><td colspan="8">Aucune réparation enregistrée</td></tr>';
    return;
  }
  body.innerHTML = reparationState.reparations
    .map(
      (r) =>
        `<tr><td>${escapeReparationHtml(
          r.nomClient
        )}<br><small>${escapeReparationHtml(
          r.telephone
        )}</small></td><td>${escapeReparationHtml(
          r.appareil
        )}</td><td>${escapeReparationHtml(r.technicien)}</td><td>${
          (r.pieces || []).length
        } pièce(s)</td><td>${formatMoney(r.main_oeuvre)}</td><td>${formatMoney(
          r.prixTotal
        )}</td><td><select class="reparation-status" data-id="${r.idrep}" ${
          r.statut === "terminee" ? "disabled" : ""
        }><option value="en_attente" ${
          r.statut === "en_attente" ? "selected" : ""
        }>En attente</option><option value="en_cours" ${
          r.statut === "en_cours" ? "selected" : ""
        }>En cours</option><option value="terminee" ${
          r.statut === "terminee" ? "selected" : ""
        }>Terminée</option></select></td><td><button class="reparation-action" data-action="details" data-id="${
          r.idrep
        }" title="Voir les détails"><span class="material-icons-sharp">visibility</span></button><button class="reparation-action" data-action="invoice" data-id="${
          r.idrep
        }" title="Générer la facture"><span class="material-icons-sharp">receipt_long</span></button>${
          r.statut === "terminee"
            ? `<button class="reparation-message" data-id="${r.idrep}" title="Envoyer le message client"><span class="material-icons-sharp">send</span></button>`
            : ""
        }</td></tr>`
    )
    .join("");
  body
    .querySelectorAll(".reparation-status")
    .forEach((s) =>
      s.addEventListener("change", () =>
        updateReparation(s.dataset.id, s.value)
      )
    );
  body
    .querySelectorAll(".reparation-action")
    .forEach((b) =>
      b.addEventListener("click", () =>
        b.dataset.action === "details"
          ? openDetails(b.dataset.id)
          : openInvoice(b.dataset.id)
      )
    );
  body
    .querySelectorAll(".reparation-message")
    .forEach((b) =>
      b.addEventListener("click", () => sendClientMessage(b.dataset.id))
    );
}
async function updateReparation(idrep, statut) {
  const r = reparationState.reparations.find(
    (x) => String(x.idrep) === String(idrep)
  );
  const data = new FormData();
  data.append("idrep", idrep);
  data.append("statut", statut);
  data.append("diagnostic", r?.diagnostic || "");
  data.append("solution", r?.solution || "");
  const response = await fetch(
    `${reparationBaseUrl}Controller/reparation/updateReparation.php`,
    { method: "POST", body: data }
  );
  const result = await response.json();
  notifyReparation(result.message, result.success ? "success" : "error");
  if (result.success) {
    if (result.notification) openClientMessage(result.notification);
    await loadReparationData();
  } else renderReparations();
}
function sendClientMessage(idrep) {
  const r = reparationState.reparations.find(
    (x) => String(x.idrep) === String(idrep)
  );
  if (r)
    openClientMessage({
      telephone: r.telephone,
      email: r.email,
      message: buildClientMessage(r),
    });
}
function buildClientMessage(r) {
  return `Bonjour ${r.nomClient}, votre appareil (${r.appareil}) est réparé. Vous pouvez passer le récupérer. Montant à payer : ${r.prixTotal} FCFA.`;
}
function openClientMessage(n) {
  const phone = String(n.telephone || "").replace(/\D/g, "");
  if (phone) {
    window.open(
      `https://wa.me/${phone}?text=${encodeURIComponent(n.message)}`,
      "_blank"
    );
  } else if (n.email)
    window.location.href = `mailto:${n.email}?subject=${encodeURIComponent(
      "Votre réparation est terminée"
    )}&body=${encodeURIComponent(n.message)}`;
}
function openModal(id) {
  document.getElementById(id).classList.add("is-open");
}
function closeModal(id) {
  document.getElementById(id).classList.remove("is-open");
}
function openDetails(idrep) {
  const r = reparationState.reparations.find(
    (x) => String(x.idrep) === String(idrep)
  );
  if (!r) return;
  document.getElementById(
    "detailsReparationBody"
  ).innerHTML = `<h2>Détails de la réparation #${
    r.idrep
  }</h2><p><strong>Client :</strong> ${escapeReparationHtml(
    r.nomClient
  )} | ${escapeReparationHtml(
    r.telephone
  )}</p><p><strong>Appareil :</strong> ${escapeReparationHtml(
    r.appareil
  )}</p><p><strong>Technicien :</strong> ${escapeReparationHtml(
    r.technicien
  )}</p><p><strong>Diagnostic :</strong> ${escapeReparationHtml(
    r.diagnostic || "-"
  )}</p><p><strong>Solution :</strong> ${escapeReparationHtml(
    r.solution || "-"
  )}</p><table class="pieces-table"><thead><tr><th>Pièce</th><th>Qté</th><th>Montant</th></tr></thead><tbody>${(
    r.pieces || []
  )
    .map(
      (p) =>
        `<tr><td>${escapeReparationHtml(p.equipement)}</td><td>${
          p.quantite
        }</td><td>${formatMoney(p.montant)}</td></tr>`
    )
    .join(
      ""
    )}</tbody></table><p class="details-total"><strong>Main-d'œuvre :</strong> ${formatMoney(
    r.main_oeuvre
  )}<br><strong>Total :</strong> ${formatMoney(r.prixTotal)}</p>`;
  openModal("detailsReparationModal");
}
function openInvoice(idrep) {
  const r = reparationState.reparations.find(
    (x) => String(x.idrep) === String(idrep)
  );
  if (!r) return;
  document.getElementById(
    "factureReparationBody"
  ).innerHTML = `<div class="invoice-repair-head"><h2>RAOUL PC SHOP</h2><h3>FACTURE DE RÉPARATION #${
    r.idrep
  }</h3><p>Client : ${escapeReparationHtml(
    r.nomClient
  )} | ${escapeReparationHtml(
    r.telephone
  )}</p><p>Appareil : ${escapeReparationHtml(
    r.appareil
  )}</p></div><table class="pieces-table"><thead><tr><th>Désignation</th><th>Qté</th><th>P.U.</th><th>Montant</th></tr></thead><tbody>${(
    r.pieces || []
  )
    .map(
      (p) =>
        `<tr><td>${escapeReparationHtml(p.equipement)}</td><td>${
          p.quantite
        }</td><td>${formatMoney(p.prix_unitaire)}</td><td>${formatMoney(
          p.montant
        )}</td></tr>`
    )
    .join(
      ""
    )}<tr><td colspan="3"><strong>Main-d'œuvre</strong></td><td><strong>${formatMoney(
    r.main_oeuvre
  )}</strong></td></tr><tr><td colspan="3"><strong>Total à payer</strong></td><td><strong>${formatMoney(
    r.prixTotal
  )}</strong></td></tr></tbody></table>`;
  openModal("factureReparationModal");
}

document.getElementById("openReparationModal").addEventListener("click", () => {
  reparationState.pieces = [];
  renderPieces();
  openModal("reparationModal");
});
document
  .getElementById("closeReparationModal")
  .addEventListener("click", () => closeModal("reparationModal"));
document
  .querySelectorAll("[data-close-modal]")
  .forEach((b) =>
    b.addEventListener("click", () => closeModal(b.dataset.closeModal))
  );
document.getElementById("addPiece").addEventListener("click", () => {
  const select = document.getElementById("idproduit");
  const product = reparationState.equipements.find(
    (p) => String(p.idproduit) === select.value
  );
  const quantity = Number(document.getElementById("quantitePiece").value);
  if (!product || quantity < 1)
    return notifyReparation(
      "Sélectionnez une pièce et une quantité valide",
      "error"
    );
  const existing = reparationState.pieces.find(
    (p) => String(p.idproduit) === select.value
  );
  if (existing) existing.quantite += quantity;
  else reparationState.pieces.push({ ...product, quantite: quantity });
  renderPieces();
  select.value = "";
});
document
  .getElementById("reparationForm")
  .addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!reparationState.pieces.length)
      return notifyReparation("Ajoutez au moins une pièce", "error");
    const data = new FormData();
    [
      "iduser",
      "nomClient",
      "telephone",
      "email",
      "appareil",
      "mainOeuvre",
      "diagnostic",
      "solution",
    ].forEach((id) => data.append(id, document.getElementById(id).value));
    data.append("main_oeuvre", document.getElementById("mainOeuvre").value);
    data.append("pieces", JSON.stringify(reparationState.pieces));
    const response = await fetch(
      `${reparationBaseUrl}Controller/reparation/addReparation.php`,
      { method: "POST", body: data }
    );
    const result = await response.json();
    notifyReparation(result.message, result.success ? "success" : "error");
    if (result.success) {
      event.target.reset();
      reparationState.pieces = [];
      renderPieces();
      closeModal("reparationModal");
      await loadReparationData();
    }
  });
function printReparationInvoice() {
  const invoiceModal = document.getElementById("factureReparationModal");
  const invoiceContent = document.getElementById("factureReparationBody");
  if (!invoiceContent || !invoiceContent.innerHTML.trim()) {
    notifyReparation(
      "Aucune réparation sélectionnée pour l'impression",
      "error"
    );
    return;
  }

  const printWindow = window.open("", "_blank", "width=900,height=700");
  if (!printWindow) {
    notifyReparation(
      "Autorisez les fenêtres popup pour imprimer la facture",
      "error"
    );
    return;
  }

  printWindow.document.write(`<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Facture de réparation</title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; padding: 30px; color: #1f2937; font-family: Arial, sans-serif; }
    h2, h3 { margin: 0 0 8px; text-align: center; }
    .invoice-head { margin-bottom: 24px; text-align: center; }
    .invoice-info { margin-bottom: 20px; line-height: 1.7; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 9px 8px; border-bottom: 1px solid #d1d5db; text-align: left; }
    th { background: #1e3a5f; color: #fff; }
    td:nth-child(2), td:nth-child(3), td:nth-child(4), th:nth-child(2), th:nth-child(3), th:nth-child(4) { text-align: right; }
    .totals { margin-top: 18px; margin-left: auto; width: 280px; line-height: 1.9; text-align: right; }
    .total-final { border-top: 2px solid #1e3a5f; font-size: 1.1rem; font-weight: bold; }
    @media print { body { padding: 10mm; } }
  </style>
</head>
<body>${invoiceContent.innerHTML}</body>
</html>`);
  printWindow.document.close();
  printWindow.focus();
  printWindow.addEventListener("load", () => {
    printWindow.print();
  });
}

document
  .getElementById("printReparationInvoice")
  .addEventListener("click", printReparationInvoice);
loadReparationData().catch(() =>
  notifyReparation("Impossible de charger les réparations", "error")
);
