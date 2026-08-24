const permissionGroups = {
  Vente: ["vente.read", "vente.create", "vente.update", "vente.delete"],
  Réparation: [
    "reparation.read",
    "reparation.create",
    "reparation.update",
    "reparation.delete",
  ],
  Produits: ["produit.read", "produit.create", "produit.update"],
  Utilisateurs: ["user.read", "user.create", "user.update"],
};
const permissionLabels = {
  read: "Lire",
  create: "Créer",
  update: "Modifier",
  delete: "Supprimer",
};

function permissionApi(endpoint, options) {
  return fetch(`Controller/users/${endpoint}`, options).then((response) =>
    response.json()
  );
}

function renderPermissions(users) {
  const container = document.getElementById("permissionsList");
  if (!users.length) {
    container.innerHTML = "<p>Aucun caissier ou technicien trouvé.</p>";
    return;
  }
  container.innerHTML = users
    .map(
      (user) => `
    <article class="permission-user" data-user-id="${user.idUser}">
      <div class="permission-user-heading">
        <div>
          <h2>${escapeHtml(user.NomComplet || user.Nom_Utilisateur)}</h2>
          <p>${escapeHtml(user.TypeDeCompte)} · ${escapeHtml(
        user.Email || ""
      )}</p>
        </div>
        <button type="button" class="permission-save" data-save-user="${
          user.idUser
        }">
          <span class="material-icons-sharp">save</span> Enregistrer
        </button>
      </div>
      <div class="permission-groups">
        ${Object.entries(permissionGroups)
          .map(
            ([group, keys]) => `
          <fieldset>
            <legend>${group}</legend>
            ${keys
              .map((key) => {
                const action = key.split(".")[1];
                const checked = user.permissions[key] === 1 ? "checked" : "";
                return `<label><input type="checkbox" value="${key}" ${checked}> ${permissionLabels[action]}</label>`;
              })
              .join("")}
          </fieldset>
        `
          )
          .join("")}
      </div>
    </article>
  `
    )
    .join("");

  container.querySelectorAll("[data-save-user]").forEach((button) => {
    button.addEventListener("click", () =>
      saveUserPermissions(button.closest(".permission-user"))
    );
  });
}

function saveUserPermissions(card) {
  const formData = new FormData();
  formData.append("idUser", card.dataset.userId);
  formData.append(
    "permissions",
    JSON.stringify(
      [...card.querySelectorAll("input:checked")].map((input) => input.value)
    )
  );
  permissionApi("save_permissions.php", {
    method: "POST",
    body: formData,
  }).then((result) => {
    const message = document.getElementById("permissionsMessage");
    message.textContent = result.message || "Autorisation mise à jour";
    message.className = `permissions-message ${
      result.success ? "success" : "error"
    }`;
  });
}

document.addEventListener("DOMContentLoaded", () => {
  if (!document.getElementById("permissionsList")) return;
  permissionApi("get_permissions.php").then((result) => {
    if (result.success) renderPermissions(result.users);
    else
      document.getElementById("permissionsList").textContent = result.message;
  });
  permissionApi("get_revenue.php").then((result) => {
    const container = document.getElementById("revenueList");
    if (!result.success) {
      container.textContent = result.message;
      return;
    }
    const table = (title, rows) =>
      `<section class="revenue-card"><h3>${title}</h3>${
        rows.length
          ? `<table>
                    <thead>
                        <tr>
                        <th>
                        Utilisateur
                        </th>
                        <th>
                        Revenu
                        </th>
                        </tr>
                        </thead>
                        <tbody>${rows
                          .map(
                            (row) =>
                              `<tr><td>${escapeHtml(
                                row.NomComplet || row.Nom_Utilisateur
                              )}</td><td>${Number(row.revenu).toLocaleString(
                                "fr-FR"
                              )} FCFA</td></tr>`
                          )
                          .join("")}</tbody></table>`
          : "<p>Aucune donnée.</p>"
      }</section>`;
    container.innerHTML =
      table("Ventes · Caissiers", result.sales) +
      table("Réparations · Techniciens", result.repairs);
  });
});
