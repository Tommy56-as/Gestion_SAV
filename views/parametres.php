<section class="settings-page">
    <div class="settings-heading">
        <div>
            <p class="settings-eyebrow">Personnalisation</p>
            <h1>Paramètres d'affichage</h1>
            <p>Adaptez l'apparence de votre espace de travail.</p>
        </div>
        <span class="material-icons-sharp settings-heading-icon">tune</span>
    </div>
    <header class="header-right">
        <button class="toggle-menu-btn" id="openSidebar">
            <span class="material-icons-sharp">menu</span>
        </button>
    </header>
    <form id="settingsForm" class="settings-panel">
        <div class="settings-section">
            <div class="settings-section-title">
                <span class="material-icons-sharp">palette</span>
                <div>
                    <h2>Couleurs principales</h2>
                    <p>Choisissez les couleurs utilisées par les actions et les accents.</p>
                </div>
            </div>
            <div class="settings-color-grid">
                <label class="settings-control">
                    <span>Couleur principale</span>
                    <input type="color" name="primary" aria-label="Couleur principale">
                </label>
                <label class="settings-control">
                    <span>Couleur secondaire</span>
                    <input type="color" name="secondary" aria-label="Couleur secondaire">
                </label>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-section-title">
                <span class="material-icons-sharp">format_size</span>
                <div>
                    <h2>Texte</h2>
                    <p>Ajustez la taille de base des textes de l'application.</p>
                </div>
            </div>
            <label class="settings-control settings-range-control">
                <span>Taille des textes <output id="fontSizeValue">14 px</output></span>
                <input type="range" name="fontSize" min="12" max="18" step="1" value="14"
                    oninput="fontSizeValue.value = `${this.value} px`">
            </label>
        </div>

        <div class="settings-section">
            <div class="settings-section-title">
                <span class="material-icons-sharp">text_fields</span>
                <div>
                    <h2>Style de police</h2>
                    <p>Sélectionnez une famille lisible pour toute l'interface.</p>
                </div>
            </div>
            <label class="settings-control">
                <span>Police</span>
                <select name="font">
                    <option value="'Nunito', sans-serif">Nunito</option>
                    <option value="'Montserrat', sans-serif">Montserrat</option>
                    <option value="'Roboto Slab', serif">Roboto Slab</option>
                    <option value="Georgia, serif">Georgia</option>
                    <option value="'Times New Roman', serif">Times New Roman</option>
                </select>
            </label>
        </div>

        <button type="button" id="resetSettings" class="settings-reset">
            <span class="material-icons-sharp">restart_alt</span>
            Réinitialiser l'affichage
        </button>
    </form>

    <section class="categories-panel">
        <div class="settings-section-title">
            <span class="material-icons-sharp">business</span>
            <div>
                <h2>Informations de l’entreprise</h2>
                <p>Ces informations apparaissent sur vos factures.</p>
            </div>
        </div>
        <form id="companyForm" class="category-form">
            <div class="company-fields-grid">
                <label class="company-field" for="companyName">
                    <span>Nom de l’entreprise</span>
                    <input id="companyName" name="nom" maxlength="150" required
                        value="<?= htmlspecialchars($currentEntreprise['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="company-field" for="companyAddress">
                    <span>Adresse</span>
                    <input id="companyAddress" name="adresse" maxlength="255"
                        value="<?= htmlspecialchars($currentEntreprise['adresse'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="company-field" for="companyPhone">
                    <span>Téléphone</span>
                    <input id="companyPhone" name="telephone" maxlength="40"
                        value="<?= htmlspecialchars($currentEntreprise['telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="company-field" for="companyPostalBox">
                    <span>Boîte postale</span>
                    <input id="companyPostalBox" name="boite_postale" maxlength="80"
                        value="<?= htmlspecialchars($currentEntreprise['boite_postale'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
            </div>
            <div class="company-form-actions">
                <button type="submit" class="settings-action"><span class="material-icons-sharp">save</span>
                    Enregistrer</button>
            </div>
            <p id="companyMessage" class="category-message" role="status"></p>
        </form>
    </section>

    <section class="categories-panel">
        <div class="settings-section-title">
            <span class="material-icons-sharp">category</span>
            <div>
                <h2>Catégories de produits</h2>
                <p>Créez et organisez les catégories propres à votre entreprise.</p>
            </div>
        </div>
        <form id="categoryForm" class="category-form">
            <input type="hidden" id="categoryId" name="idCategorie">
            <label for="categoryLabel">Libellé</label>
            <div class="category-form-row">
                <input id="categoryLabel" name="libelle" maxlength="100" required placeholder="Ex. Électronique">
                <button type="submit" class="settings-action" id="categorySubmit">
                    <span class="material-icons-sharp">add</span> Ajouter
                </button>
                <button type="button" class="category-cancel" id="categoryCancel" hidden>Annuler</button>
            </div>
        </form>
        <p id="categoryMessage" class="category-message" role="status"></p>
        <div id="categoriesList" class="categories-list">Chargement...</div>
    </section>
</section>
<script src="js/categories.js"></script>