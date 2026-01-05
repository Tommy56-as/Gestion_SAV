// Variables globales
let editingUserId = null;
const BASE_URL = window.location.origin + '/GESTION_SAV/';

// Fonction pour afficher les notifications
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (!notification) {
        console.error('Notification element not found');
        alert(`${type}: ${message}`);
        return;
    }
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.style.display = 'none';
            notification.style.opacity = '1';
        }, 300);
    }, 3000);
}
// Fonction pour construire l'URL correcte
function getApiUrl(endpoint) {
    return `${window.location.origin}/GESTION_SAV/Controller/fournisseur/${endpoint}`;
}
// Fonction pour charger les fournisseurs
function loadFournisseurs() {
    fetch(getApiUrl('getAllFournisseur.php'))
        .then(response => response.json())
        .then(data => {
            if (data) {
            const container = document.getElementById('FournisseurContainer');
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

            container.innerHTML = '';

            data.fournisseurs.forEach(fournisseur => {
                const status = fournisseur.statut ?? 0;
                const fournisseurId = fournisseur.idfour ?? 0;

                container.innerHTML += `
                    <div class="user-card ${status == 1 ? 'archiver' : 'active'}" id="user-card-${fournisseurId}">
                        
                        <div class="user-status ${status == 1 ? 'status-blocked' : 'status-active'}">
                            ${status == 1 
                                ? `<span class="material-icons-sharp">lock</span> Archivé`
                                : `<span class="material-icons-sharp">check_circle</span> Actif`
                            }
                        </div>

                        <div class="user-info">
                            <h3 class="user-name">
                                <span class="material-icons-sharp">person</span>
                                ${fournisseur.nom ?? 'Non défini'} <span>${fournisseur.prenom ?? 'Non défini'}</span>
                            </h3>

                            <div class="user-detail">
                                <span class="material-icons-sharp">badge</span>
                                <span>${fournisseur.adresse ?? 'Non défini'}</span>
                            </div>

                            <div class="user-detail">
                                <span class="material-icons-sharp">phone</span>
                                <span>${fournisseur.telephone ?? 'Non défini'}</span>
                            </div>
                            <div class="user-detail">
                                <span class="material-icons-sharp">inventory_2</span>
                                <span>${fournisseur.produit_livres ?? 'Non défini'}</span>
                            </div>
                        </div>

                        <div class="user-actions">
                            <button class="btn btn-secondary btn-sm" onclick="editFournisseur(${fournisseurId})">
                                <span class="material-icons-sharp">edit</span>
                            </button>

                            ${
                                status == 1
                                ? `<button class="btn btn-success btn-sm" onclick="debloquerUser(${fournisseurId})">
                                        <span class="material-icons-sharp">lock_open</span>
                                   </button>`
                                : `<button class="btn btn-danger btn-sm" onclick="bloquerUser(${fournisseurId})">
                                        <span class="material-icons-sharp">lock</span>
                                   </button>`
                            }
                            <button class="btn btn-delete btn-sm" onclick="deleteUser(${fournisseurId})">
                                <span class="material-icons-sharp">delete</span>
                            </button>
                        </div>
                    </div>
                `;
            });
            } else {
                showNotification('Erreur lors du chargement des fournisseurs', 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur réseau ou serveur', 'error');
            console.error('Erreur:', error);
        });
}
// Fonction pour ajouter un fournsseur
function saveFournisseur() {
    const form = document.getElementById('fournisseurForm');
    const formData = new FormData(form);
      const endpoint = 'addFournisseur.php';
    fetch(getApiUrl(endpoint), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Succès');
            resetForm();
            loadFournisseurs();
            
        } else {
            showNotification(data.message || 'Erreur', 'error');
        }
    })
    .catch(error => {
        console.error(error);
        showNotification('Erreur serveur', 'error');
    });
    
}
document.getElementById('saveFournisseur').addEventListener('click', function () {
    saveFournisseur(); 
    close();  
});
//ouvrir le modal d'ajout fournisseur
const modalFournisseur = document.getElementById('fournisseurModal');
function openAddFournisseurModal() {
    const addFournisseurBtn = document.getElementById('addFournisseurBtn');
    addFournisseurBtn.addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = " Ajouter un fournisseur";
        modalFournisseur.style.display = 'flex';
    
    });
}  
 // Fonction pour close le modal
function close(){
document.getElementById('closeModal').addEventListener('click', function () {
 modalFournisseur.style.display = 'none';    
});
}


document.getElementById('designation').addEventListener('change', function () {
    const designation = this.value;
    const caracteristiqueSelect = document.getElementById('produitLivre');

    caracteristiqueSelect.innerHTML =
        '<option value="">Sélectionner une caractéristique</option>';
    caracteristiqueSelect.disabled = true;

    if (!designation) return;

    const url = getApiUrl(
        `caracteristique.php?designation=${encodeURIComponent(designation)}`
    );

    console.log('URL caractéristiques:', url); 

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Caractéristiques reçues:', data); 

            if (!Array.isArray(data)) {
                throw new Error('Réponse invalide');
            }

            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.idproduit;
                option.textContent = item.caracteristique;
                caracteristiqueSelect.appendChild(option);
            });

            caracteristiqueSelect.disabled = false;
        })
        .catch(error => {
            console.error('Erreur chargement caractéristiques:', error);
            showNotification('Erreur lors du chargement des caractéristiques');
        });
});
//foncton pour etiter un fournisseur
function editFournisseur(fournisseurId) {
    fetch(getApiUrl(`get_Fournisseur.php?idfour=${fournisseurId}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fournisseur = data.data[0];
                const produit = data.data[0];
                document.getElementById('idfour').value = fournisseur.idfour || '';
                document.getElementById('nom').value = fournisseur.nom || '';
                document.getElementById('prenom').value = fournisseur.prenom || '';
                document.getElementById('telephone').value = fournisseur.telephone || '';
                document.getElementById('adresse').value = fournisseur.adresse || '';
                // le champ designation ne remplie pas pareil pour produitLivre se qui est embetant
                document.getElementById('designation').value = produit.designation || '';
                document.getElementById('produitLivre').value = produit.caracteristique || '';
                document.getElementById('modalTitle').textContent = " Modifier le fournisseur";
                modalFournisseur.style.display = 'flex';
            } else {
                showNotification(data.message || 'Erreur lors du chargement du fournisseur', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur réseau ou serveur', 'error');
      }); 
}
// Fonction pour réinitialiser le formulaire
function resetForm() {
    document.getElementById('idfour').value = '';
    document.getElementById('nom').value = '';
    document.getElementById('prenom').value = '';
    document.getElementById('telephone').value = '';
    document.getElementById('adresse').value = '';
    document.getElementById('designation').value = '';
    document.getElementById('produitLivre').value = '';

}


// Charger les fournisseurs au chargement de la page
document.addEventListener("DOMContentLoaded", function() {
    loadFournisseurs();
    openAddFournisseurModal();
    close();
});