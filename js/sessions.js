function loadSessions() {
  const body = document.getElementById("sessionsBody");
  fetch("Controller/users/get_sessions.php")
    .then((response) => response.json())
    .then((result) => {
      if (!result.success) throw new Error(result.message);
      if (!result.sessions.length) {
        body.innerHTML =
          '<tr><td colspan="7">Aucune session ouverte.</td></tr>';
        return;
      }
      body.innerHTML = result.sessions
        .map((session) => {
          const sales = Number(session.revenu_vente || 0);
          const repairs = Number(session.revenu_reparation || 0);
          return `<tr><td><strong>${escapeHtml(
            session.NomComplet || session.Nom_Utilisateur
          )}</strong><small>${escapeHtml(
            session.Nom_Utilisateur
          )}</small></td><td><span class="session-role">${escapeHtml(
            session.TypeDeCompte
          )}</span></td><td>${escapeHtml(
            new Date(session.last_activity.replace(" ", "T")).toLocaleString(
              "fr-FR"
            )
          )}</td><td>${escapeHtml(
            session.ip_address || "-"
          )}</td><td>${sales.toLocaleString(
            "fr-FR"
          )} FCFA</td><td>${repairs.toLocaleString(
            "fr-FR"
          )} FCFA</td><td><strong>${(sales + repairs).toLocaleString(
            "fr-FR"
          )} FCFA</strong></td></tr>`;
        })
        .join("");
    })
    .catch((error) => {
      body.innerHTML = `<tr><td colspan="7">${error.message}</td></tr>`;
    });
}

document.addEventListener("DOMContentLoaded", () => {
  if (!document.getElementById("sessionsBody")) return;
  loadSessions();
  document
    .getElementById("refreshSessions")
    .addEventListener("click", loadSessions);
});
