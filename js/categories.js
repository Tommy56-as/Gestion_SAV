const categoryForm = document.getElementById("categoryForm");
const categoryId = document.getElementById("categoryId");
const categoryLabel = document.getElementById("categoryLabel");
const categorySubmit = document.getElementById("categorySubmit");
const categoryCancel = document.getElementById("categoryCancel");
const categoriesList = document.getElementById("categoriesList");
const categoryMessage = document.getElementById("categoryMessage");

function showCategoryMessage(message, type = "success") {
  categoryMessage.textContent = message;
  categoryMessage.className = `category-message ${type}`;
}

async function categoryRequest(data = null) {
  const options = data ? { method: "POST", body: data } : {};
  const response = await fetch("Controller/categorie/categorie.php", options);
  const responseText = await response.text();
  let result;
  try {
    result = JSON.parse(responseText);
  } catch (error) {
    throw new Error(
      "Le serveur a renvoyé une réponse invalide. Vérifiez votre session."
    );
  }
  if (!response.ok || !result.success)
    throw new Error(result.message || "Opération impossible.");
  return result;
}

async function loadCategories() {
  try {
    const result = await categoryRequest();
    if (!result.categories.length) {
      categoriesList.innerHTML =
        '<p class="category-empty">Aucune catégorie créée.</p>';
      return;
    }
    categoriesList.innerHTML = result.categories
      .map(
        (category) => `
      <article class="category-row">
        <div><strong>${escapeHtml(category.libelle)}</strong><small>${
          category.produits
        } produit(s)</small></div>
        <div class="category-actions">
          <button type="button" class="category-icon" data-edit="${
            category.idCategorie
          }" aria-label="Modifier"><span class="material-icons-sharp">edit</span></button>
          <button type="button" class="category-icon danger" data-delete="${
            category.idCategorie
          }" aria-label="Supprimer"><span class="material-icons-sharp">delete</span></button>
        </div>
      </article>`
      )
      .join("");
  } catch (error) {
    categoriesList.innerHTML = `<p class="category-message error">${escapeHtml(
      error.message
    )}</p>`;
  }
}

categoryForm.addEventListener("submit", async (event) => {
  event.preventDefault();
  const data = new FormData(categoryForm);
  data.append("action", categoryId.value ? "update" : "create");
  try {
    const result = await categoryRequest(data);
    showCategoryMessage(result.message);
    categoryForm.reset();
    categoryId.value = "";
    categorySubmit.innerHTML =
      '<span class="material-icons-sharp">add</span> Ajouter';
    categoryCancel.hidden = true;
    await loadCategories();
  } catch (error) {
    showCategoryMessage(error.message, "error");
  }
});

categoriesList.addEventListener("click", async (event) => {
  const editButton = event.target.closest("[data-edit]");
  const deleteButton = event.target.closest("[data-delete]");
  if (editButton) {
    const row = editButton.closest(".category-row");
    categoryId.value = editButton.dataset.edit;
    categoryLabel.value = row.querySelector("strong").textContent;
    categorySubmit.innerHTML =
      '<span class="material-icons-sharp">save</span> Modifier';
    categoryCancel.hidden = false;
    categoryLabel.focus();
  }
  if (deleteButton && confirm("Supprimer cette catégorie ?")) {
    const data = new FormData();
    data.append("action", "delete");
    data.append("idCategorie", deleteButton.dataset.delete);
    try {
      const result = await categoryRequest(data);
      showCategoryMessage(result.message);
      await loadCategories();
    } catch (error) {
      showCategoryMessage(error.message, "error");
    }
  }
});

categoryCancel.addEventListener("click", () => {
  categoryForm.reset();
  categoryId.value = "";
  categorySubmit.innerHTML =
    '<span class="material-icons-sharp">add</span> Ajouter';
  categoryCancel.hidden = true;
});

loadCategories();
