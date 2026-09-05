const csrfToken =
  document.querySelector('meta[name="csrf-token"]')?.content || "";
let plans = [];

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

async function superRequest(endpoint, data = null) {
  const options = data
    ? { method: "POST", body: data, headers: { "X-CSRF-Token": csrfToken } }
    : { headers: { "X-CSRF-Token": csrfToken } };
  const response = await fetch(`Controller/super/${endpoint}`, options);
  const result = await response.json();
  if (!response.ok || !result.success)
    throw new Error(result.message || "Opération impossible.");
  return result;
}

function showMessage(message, type = "success") {
  const element = document.getElementById("saasMessage");
  element.textContent = message;
  element.className = `saas-message ${type}`;
}

function renderCompanies(companies) {
  const container = document.getElementById("companiesList");
  if (!container) return;
  if (!companies.length) {
    container.innerHTML = '<p class="saas-empty">Aucune entreprise.</p>';
    return;
  }
  container.innerHTML = companies
    .map(
      (company) => `
    <article class="company-card">
      <div class="company-card-heading"><div class="company-identity"><span class="material-icons-sharp">domain</span><div><strong>${escapeHtml(
        company.nom
      )}</strong><small>${escapeHtml(
        company.slug
      )}</small></div></div><span class="company-card-status ${
        company.statut === "active" || company.statut === "trialing"
          ? "is-active"
          : "is-muted"
      }">${
        company.statut
          ? escapeHtml(
              company.statut === "trialing"
                ? "Période d’essai"
                : company.statut === "active"
                ? "Actif"
                : company.statut
            )
          : "Sans abonnement"
      }</span></div>
      <div class="company-card-summary"><div><span>Plan actuel</span><strong>${escapeHtml(
        company.plan_nom || "Aucun"
      )}</strong></div><div><span>Expiration</span><strong>${escapeHtml(
        company.date_fin || "À définir"
      )}</strong></div></div>
      <form class="subscription-form" data-company-id="${company.idEntreprise}">
        <select name="idPlan" aria-label="Plan de ${escapeHtml(
          company.nom
        )}">${plans
        .filter((plan) => Number(plan.actif) === 1)
        .map(
          (plan) =>
            `<option value="${plan.idPlan}" ${
              String(plan.idPlan) === String(company.idPlan) ? "selected" : ""
            }>${escapeHtml(plan.nom)}</option>`
        )
        .join("")}</select>
        <select name="statut" aria-label="Statut de ${escapeHtml(company.nom)}">
          ${[
            ["trialing", "Essai"],
            ["active", "Actif"],
            ["past_due", "Paiement en retard"],
            ["cancelled", "Annulé"],
            ["expired", "Expiré"],
            ["suspended", "Suspendu"],
          ]
            .map(
              ([status, label]) =>
                `<option value="${status}" ${
                  status === company.statut ? "selected" : ""
                }>${label}</option>`
            )
            .join("")}
        </select>
        <select name="periode" aria-label="Période de ${escapeHtml(
          company.nom
        )}"><option value="mensuelle" ${
        company.periode === "mensuelle" ? "selected" : ""
      }>Mensuel</option><option value="annuelle" ${
        company.periode === "annuelle" ? "selected" : ""
      }>Annuel</option></select>
        <input type="date" name="date_fin" value="${escapeHtml(
          company.date_fin || ""
        )}" required>
        <button type="submit" title="Prolonger ou mettre à jour"><span class="material-icons-sharp">save</span></button>
      </form>
      <form class="payment-form" data-company-id="${company.idEntreprise}">
        <input name="montant" type="number" min="1" step="1" placeholder="Montant reçu (FCFA)" required>
        <input name="reference" placeholder="Référence (facultatif)">
        <button type="submit" title="Enregistrer le paiement"><span class="material-icons-sharp">payments</span> Paiement reçu</button>
      </form>
    </article>`
    )
    .join("");
  container
    .querySelectorAll(".subscription-form")
    .forEach((form) => form.addEventListener("submit", updateSubscription));
  container
    .querySelectorAll(".payment-form")
    .forEach((form) => form.addEventListener("submit", recordPayment));
}

function renderMetrics(result) {
  const container = document.getElementById("saasMetrics");
  if (!container) return;
  container.innerHTML = `
    <article><span class="material-icons-sharp">payments</span><div><small>Recettes encaissées ce mois</small><strong>${Number(
      result.monthlyRevenue || 0
    ).toLocaleString("fr-FR")} FCFA</strong></div></article>
    <article><span class="material-icons-sharp">business</span><div><small>Entreprises actives</small><strong>${Number(
      result.activeCompanies || 0
    ).toLocaleString("fr-FR")}</strong></div></article>
    <article><span class="material-icons-sharp">event</span><div><small>Suivi des abonnements</small><strong>Plans configurables</strong></div></article>`;
}

function renderPlans() {
  const container = document.getElementById("plansList");
  if (!container) return;
  container.innerHTML = plans
    .map(
      (plan) => `
    <article class="plan-card"><form class="plan-row" data-plan-id="${
      plan.idPlan
    }">
      <div class="plan-card-heading"><span class="material-icons-sharp">workspace_premium</span><div><strong>${escapeHtml(
        plan.nom
      )}</strong><small>${escapeHtml(
        plan.description || "Plan configurable"
      )}</small></div></div>
      <div class="plan-edit-fields">
        <label>Nom du plan<input name="nom" value="${escapeHtml(
          plan.nom
        )}" aria-label="Nom du plan"></label>
        <label>Description<input name="description" value="${escapeHtml(
          plan.description || ""
        )}" aria-label="Description du plan"></label>
        <label>Prix mensuel<input name="prix_mensuel" type="number" min="0" step="1" value="${
          plan.prix_mensuel
        }" aria-label="Prix mensuel"></label>
        <label>Prix annuel<input name="prix_annuel" type="number" min="0" step="1" value="${
          plan.prix_annuel
        }" aria-label="Prix annuel"></label>
      </div>
      <button class="plan-save-button" type="submit" title="Enregistrer"><span class="material-icons-sharp">save</span> Enregistrer les modifications</button>
    </form></article>`
    )
    .join("");
  document
    .querySelectorAll(".plan-row")
    .forEach((form) => form.addEventListener("submit", updatePlan));
}

async function updateSubscription(event) {
  event.preventDefault();
  const data = new FormData(event.currentTarget);
  data.append("idEntreprise", event.currentTarget.dataset.companyId);
  try {
    await superRequest("update_subscription.php", data);
    showMessage("Abonnement mis à jour.");
    await loadData();
  } catch (error) {
    showMessage(error.message, "error");
  }
}

async function updatePlan(event) {
  event.preventDefault();
  const data = new FormData(event.currentTarget);
  data.append("idPlan", event.currentTarget.dataset.planId);
  try {
    await superRequest("update_plan.php", data);
    showMessage("Plan mis à jour.");
    await loadData();
  } catch (error) {
    showMessage(error.message, "error");
  }
}

async function createPlan(event) {
  event.preventDefault();
  const form = event.currentTarget;
  try {
    await superRequest("create_plan.php", new FormData(form));
    form.reset();
    showMessage("Nouveau plan créé.");
    await loadData();
  } catch (error) {
    showMessage(error.message, "error");
  }
}

async function recordPayment(event) {
  event.preventDefault();
  const data = new FormData(event.currentTarget);
  data.append("idEntreprise", event.currentTarget.dataset.companyId);
  try {
    await superRequest("record_payment.php", data);
    showMessage("Paiement enregistré.");
    event.currentTarget.reset();
    await loadData();
  } catch (error) {
    showMessage(error.message, "error");
  }
}

async function loadData() {
  try {
    const result = await superRequest("get_data.php");
    plans = result.plans || [];
    renderMetrics(result);
    renderCompanies(result.companies || []);
    renderPlans();
  } catch (error) {
    showMessage(error.message, "error");
  }
}

loadData();
document
  .getElementById("createPlanForm")
  ?.addEventListener("submit", createPlan);
