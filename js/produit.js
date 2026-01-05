let selectedImage = null;
let editingidProduit = null;

const BASE_URL = window.location.origin + '/GESTION_SAV/';

// Fonction pour construire l'URL correcte
function getApiUrl(endpoint) {
    return `${window.location.origin}/GESTION_SAV/Controller/produit/${endpoint}`;
}
// Fonction pour charger les produits depuis le serveur
function loadProduits() {
    const container = document.getElementById('produitContainer');
    const url = getApiUrl('get_produits.php');

    container.innerHTML = '<p>Chargement des produits...</p>';

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP : ${response.status}`);
            }
            return response.json();
        })
        .then(({
            success,
            data
        }) => {
            if (!success || !Array.isArray(data)) {
                throw new Error('Format de données invalide');
            }

            container.innerHTML = '';

            if (data.length === 0) {
                container.innerHTML = '<p>Aucun produit disponible</p>';
                return;
            }

            data.forEach(renderProduit);
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<p style="color:red">Erreur de chargement</p>';
        });
}

// Fonction pour afficher un produit
function renderProduit(produit) {
    const container = document.getElementById('produitContainer');

    const card = document.createElement('div');
    card.className = 'produit-card';

    const image = produit.image ?
        `<img src="img/${produit.image}" alt="${produit.designation}">` :
        `<div class="placeholder">📦</div>`;
    card.innerHTML = `
        <div class="produit-image">${image}
         <div class="detail-price">${produit.prixUnitaire} FCFA</div>
        </div>
                    <div class="produit-content">
                        <h3 class="produit-title">${produit.designation}</h3>
                        <div>
                            <div class="detail-item">
                                <div class="detail-label">Quantite en Stock</div>
                                <div class="detail-value">${produit.quantite}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Caractéristique</div>
                                <div class="detail-value">${produit.caracteristique}</div>
                            </div>
                        </div>
                        <div class="plan-actions">
                            <button class="action-btn edit-btn"
                                onclick="editProduit(${produit.idproduit})">
                                Modifier
                            </button>
                        </div>
                    </div>
    `;

    container.appendChild(card);
}
// Fonction pour mettre à jour un produit 
function updateProduit() {
   
    const formData = new FormData();
    
    formData.append('idproduit', editingidProduit);
    formData.append('designation', document.getElementById('designation').value);
    formData.append('caracteristique', document.getElementById('caracteristique').value);
    formData.append('quantite', document.getElementById('quantite').value);
    formData.append('quantite_min', document.getElementById('quantite_min').value);
    formData.append('categorie', document.getElementById('categorie').value);
    formData.append('prixUnitaire', document.getElementById('prixUnitaire').value);

    // Ajoute la nouvelle image si une est sélectionnée
    if (window.selectedImage) {
        formData.append('image', window.selectedImage);
    }
    const url = getApiUrl('update_produit.php');
    console.log('Mise à jour du produit:', url);
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Produit modifié avec succès', 'success');
            loadProduits();
            resetForm();
            // Revenir au mode ajout
            document.getElementById('Title').textContent = 'Ajouter un nouveau produit';
            document.getElementById('addProduit').style.display = 'block';
            document.getElementById('updateProduit').style.display = 'none';
        } else {
            console.error('Erreur de mise à jour:', data.message);
            showNotification(data.message || 'Erreur lors de la modification du produit', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la modification ', 'error');
    });
}
// Fonction pour ajouter un produit
function addProduit() {

    const formData = new FormData();
    formData.append('designation', document.getElementById('designation').value);
    formData.append('caracteristique', document.getElementById('caracteristique').value);
    formData.append('quantite', document.getElementById('quantite').value);
    formData.append('quantite_min', document.getElementById('quantite_min').value);
    formData.append('categorie', document.getElementById('categorie').value);
    formData.append('prixUnitaire', document.getElementById('prixUnitaire').value);

    if (window.selectedImage) {
        formData.append('image', window.selectedImage);
    }
    const url = getApiUrl('addProduit.php');

    fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Produit ajouté avec succès', 'success');
                loadProduits();
                resetForm();
            } else {
                showNotification(data.message || 'Erreur lors de l\'ajout', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de l\'ajout du produit', 'error');
        });
}
/*
 FRA1DL20257042172 France métropolitaine - Long séjour (> 90 jours)
TSAGUE KOUGANG Raoul Loic 27/10/2002 
*/

// Fonction pour éditer un plan
function editProduit(idproduit) {
    if (!idproduit) {
        showNotification('ID produit manquant', 'error');
        return;
    }
 const url = getApiUrl(`get_produit.php?idproduit=${idproduit}`);
 console.log('chargement du produit:', url);
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP : ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if(!data.success) {
                throw new Error(data.message || 'Erreur lors de la récupération du produit');
            }
            const produit = data.data;
            
            // Remplir le formulaire avec les données du produit
            document.getElementById('idproduit').value = produit.idproduit || '';
            document.getElementById('designation').value = produit.designation || '';
            document.getElementById('caracteristique').value = produit.caracteristique || '';
            document.getElementById('quantite').value = produit.quantite || '';
            document.getElementById('quantite_min').value = produit.quantite_min || '';
            document.getElementById('categorie').value = produit.categorie || 'ordinateur';
            document.getElementById('prixUnitaire').value = produit.prixUnitaire || '';

            // Afficher l'image existante
            const previews = document.getElementById('imagePreview');
            if (produit.image) {
                previews.innerHTML = 
                `<img src="img/${produit.image}" alt="${produit.designation}">`;
            }

            // Changer le mode édition
            editingidProduit = produit.idproduit;
            document.getElementById('Title').textContent = 'Modification du produit';
            document.getElementById('addProduit').style.display = 'none';
            document.getElementById('updateProduit').style.display = 'block';
            document.getElementById('adminPanel').style.display = 'block';
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors du chargement du produit', 'error');
        });
}
// Fonction pour afficher l'aperçu de l'image
function showImagePreview(file) {
    const preview = document.getElementById('imagePreview');
    const reader = new FileReader();

    reader.onload = function(e) {
        // Corrige le src : utilise directement e.target.result (data URL)
        preview.innerHTML =
            `<img src="${e.target.result}" alt="Aperçu" style="max-width: 100%; max-height: 200px; border-radius: 8px;">`;
    };

    reader.readAsDataURL(file);
    // Définit selectedImage si tu l'utilises ailleurs (par exemple pour l'upload)
    window.selectedImage = file; // Utilise window pour la rendre globale
}
// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.style.display = 'block';

    // Cache après 3 secondes
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Initialisation
document.addEventListener("DOMContentLoaded", function() {
    loadProduits();

    // Gestionnaire pour le bouton d'upload d'image
    document.getElementById('uploadTrigger').addEventListener('click', function() {
            document.getElementById('imageUpload').click();
        });
    // Gestionnaire pour la sélection de fichier
    document.getElementById("imageUpload").addEventListener("change", function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];

            // Vérifier le type de fichier
            if (!file.type.match("image.*")) {
                showNotification("Veuillez sélectionner un fichier image valide", "error");
                return;
            }

            // Vérifier la taille du fichier (max 3MB)
            if (file.size > 3 * 1024 * 1024) {
                showNotification("L'image ne doit pas dépasser 3MB", "error");
                return;
            }

            showImagePreview(file);
        }
    });
    //  const container = document.getElementById("produitContainer");
    // Gestionnaire pour le bouton d'ajout de plan
    document.getElementById("addProduit").addEventListener("click", addProduit);

    // Gestionnaire pour le bouton de modification de plan
    document.getElementById("updateProduit").addEventListener("click", updateProduit);

    // Gestionnaire pour le bouton de réinitialisation
    document.getElementById("resetForm").addEventListener("click", resetForm);
    
    // Gestionnaire pour le mode admin
    document.getElementById("addProductBtn").addEventListener("click", function() {
        document.getElementById('Title').textContent = 'Ajouter un nouveau produit';
        const adminPanel = document.getElementById("adminPanel");
        adminPanel.style.display =
            adminPanel.style.display === "block" ? "none" : "block";
        this.textContent =
            adminPanel.style.display === "block" ? "fermer " : "Ajouter un produit";
    });
      // Gestionnaire pour la recherche
    const searchBox = document.querySelector('.search-box');
    if (searchBox) {
        searchBox.addEventListener('input', handleSearch);
    }
});
 // Fonction pour réinitialiser le formulaire
        function resetForm() {
            document.getElementById('idproduit').value = '';
            document.getElementById('designation').value = '';
            document.getElementById('caracteristique').value = '';
            document.getElementById('quantite').value = '';
            document.getElementById('quantite_min').value = '';
            document.getElementById('categorie').value = 'ordinateur';
            document.getElementById('prixUnitaire').value = '';
            document.getElementById('imagePreview').innerHTML = `
                <div class="placeholder">
                    <i class="fas fa-image" style="font-size: 
                     3rem; margin-bottom: 10px; background: bleu;"></i>
                    <div>Aucune image sélectionnée</div>
                </div>
            `;
            selectedImage = null;
            document.getElementById('addProduit').style.display = 'block';
            document.getElementById('updateProduit').style.display = 'none';
        }
// Fonction de recherche
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.produit-card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
}