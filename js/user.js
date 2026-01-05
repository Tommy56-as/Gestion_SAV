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
    return `${window.location.origin}/GESTION_SAV/Controller/users/${endpoint}`;
}
function loadUsers() {
    const container = document.getElementById('usersContainer');
    container.innerHTML = '<p>Chargement...</p>';

    fetch(getApiUrl('getAllUser.php'))
        .then(response => response.json())
        .then(data => {
            if (!data.success || data.users.length === 0) {
                container.innerHTML = `
                    <div class="no-users">
                        <span class="material-icons-sharp">person_off</span>
                        <h3>Aucun utilisateur trouvé</h3>
                        <p>Ajoutez votre premier utilisateur.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = '';

            data.users.forEach(user => {
                const status = user.Statut ?? 0;
                const userId = user.idUser ?? 0;

                container.innerHTML += `
                    <div class="user-card ${status == 1 ? 'blocked' : 'active'}" id="user-card-${userId}">
                        
                        <div class="user-status ${status == 1 ? 'status-blocked' : 'status-active'}">
                            ${status == 1 
                                ? `<span class="material-icons-sharp">lock</span> Bloqué`
                                : `<span class="material-icons-sharp">check_circle</span> Actif`
                            }
                        </div>

                        <div class="user-info">
                            <h3 class="user-name">
                                <span class="material-icons-sharp">person</span>
                                ${user.Nom_Utilisateur ?? 'Non défini'}
                            </h3>

                            <div class="user-detail">
                                <span class="material-icons-sharp">mail</span>
                                <span>${user.Email ?? 'Non défini'}</span>
                            </div>

                            <div class="user-detail">
                                <span class="material-icons-sharp">badge</span>
                                <span>${user.TypeDeCompte ?? 'Non défini'}</span>
                            </div>

                            <div class="user-detail">
                                <span class="material-icons-sharp">phone</span>
                                <span>${user.Telephone ?? 'Non défini'}</span>
                            </div>
                        </div>

                        <div class="user-actions">
                            <button class="btn btn-secondary btn-sm" onclick="editUser(${userId})">
                                <span class="material-icons-sharp">edit</span>
                            </button>

                            ${
                                status == 1
                                ? `<button class="btn btn-success btn-sm" onclick="debloquerUser(${userId})">
                                        <span class="material-icons-sharp">lock_open</span>
                                   </button>`
                                : `<button class="btn btn-danger btn-sm" onclick="bloquerUser(${userId})">
                                        <span class="material-icons-sharp">lock</span>
                                   </button>`
                            }
                            <button class="btn btn-delete btn-sm" onclick="deleteUser(${userId})">
                                <span class="material-icons-sharp">delete</span>
                            </button>
                        </div>
                    </div>
                `;
            });
        })
        .catch(error => {
            console.error(error);
            container.innerHTML = '<p>Erreur de chargement</p>';
        });
}

// Fonction pour ajouter un utilisateur
function saveUser() {
    const form = document.getElementById('userForm');
    const formData = new FormData(form);

    const id = document.getElementById('userId').value;
    const endpoint = id ? 'update_user.php' : 'addUser.php';

    fetch(getApiUrl(endpoint), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Succès');
            resetForm();
            loadUsers();
            fermer();
        } else {
            showNotification(data.message || 'Erreur', 'error');
        }
    })
    .catch(error => {
        console.error(error);
        showNotification('Erreur serveur', 'error');
    });
    
}

document.getElementById('saveUser').addEventListener('click', function () {
    saveUser(); 
   
});
document.getElementById('updateUser').addEventListener('click', function () {
    updateUser();    
});

// Fonction pour éditer un utilisateur
function editUser(userId) {
    const url = getApiUrl(`getUserById.php?id=${userId}`);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showNotification(data.message, 'error');
                return;
            }

            const u = data.user;

            editingUserId = u.idUser;
            document.getElementById('nomUtilisateur').value = u.Nom_Utilisateur;
            document.getElementById('email').value = u.Email;
            document.getElementById('typeCompte').value = u.TypeDeCompte;
            document.getElementById('nomComplet').value = u.NomComplet;
            document.getElementById('telephone').value = u.Telephone;
            document.getElementById('adresse').value = u.Adresse;
            document.getElementById('motDePasse').value = '';
            document.getElementById('passwordHint').style.display = 'block';
            document.getElementById('modalTitle').textContent = "Modifier l'utilisateur";
            document.getElementById('saveUser').style.display = 'none';
            document.getElementById('updateUser').style.display = 'block';
            document.getElementById('userModal').style.display = 'flex';
        })
        .catch(err => {
            console.error(err);
            showNotification('Erreur chargement utilisateur', 'error');
        });
}

// Fonction pour mettre à jour un utilisateur
function updateUser() {
    if (!editingUserId) {
        showNotification('ID utilisateur manquant', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('idUser', editingUserId);
    formData.append('Nom_Utilisateur', document.getElementById('nomUtilisateur').value);
    formData.append('Email', document.getElementById('email').value);
    formData.append('TypeDeCompte', document.getElementById('typeCompte').value);
    formData.append('NomComplet', document.getElementById('nomComplet').value);
    formData.append('Telephone', document.getElementById('telephone').value);
    formData.append('Adresse', document.getElementById('adresse').value);

    const password = document.getElementById('motDePasse').value;
    if (password && password.trim() !== '') {
        formData.append('MotDePasse', password);
    }

    const url = getApiUrl('update_user.php');
    console.log('Mise à jour vers:', url);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Utilisateur modifié avec succès');
            resetForm();
            loadUsers();
            fermer();
        } else {
            showNotification(data.message || 'Erreur lors de la modification', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur updateUser:', error);
        showNotification('Erreur serveur: ' + error.message, 'error');
    });
}

// Fonction pour bloquer un utilisateur
function bloquerUser(userId) {
    const userCard = document.getElementById(`user-card-${userId}`);
    if (!userCard) {
        showNotification('Utilisateur non trouvé', 'error');
        return;
    }
    
    const userName = userCard.querySelector('.user-name').textContent.replace('person', '').trim();
    
    if (confirm(`Voulez-vous vraiment bloquer l'utilisateur "${userName}" ?`)) {
        changeUserStatus(userId, 1);
    }
}

// Fonction pour débloquer un utilisateur
function debloquerUser(userId) {
    const userCard = document.getElementById(`user-card-${userId}`);
    if (!userCard) {
        showNotification('Utilisateur non trouvé', 'error');
        return;
    }
    
    const userName = userCard.querySelector('.user-name').textContent.replace('person', '').trim();
    
    if (confirm(`Voulez-vous vraiment débloquer l'utilisateur "${userName}" ?`)) {
        changeUserStatus(userId, 0);
    }
}

// Fonction pour changer le statut d'un utilisateur
function changeUserStatus(userId, status) {
    const formData = new FormData();
    formData.append('idUser', userId);
    formData.append('Statut', status);

    const url = getApiUrl('get_statut_user.php');
    console.log('Changement statut vers:', url);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Réponse non-JSON:', text.substring(0, 200));
                throw new Error('Réponse non-JSON du serveur');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const action = status == 1 ? 'bloqué' : 'débloqué';
            showNotification(`Utilisateur ${action} avec succès`);
            updateUserCardUI(userId, status);
            loadUsers();
        } else {
            showNotification(data.message || 'Erreur lors du changement de statut', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors du changement de statut: ' + error.message, 'error');
    });
}

// Fonction pour mettre à jour l'affichage de la carte utilisateur
function updateUserCardUI(userId, status) {
    const userCard = document.getElementById(`user-card-${userId}`);
    if (!userCard) {
        
        console.warn(`Carte utilisateur ${userId} non trouvée`);
        return;
    }
    
    // Mettre à jour la classe
    userCard.className = `user-card ${status == 1 ? 'blocked' : 'active'}`;
    
    // Mettre à jour le statut
    const statusDiv = userCard.querySelector('.user-status');
    if (statusDiv) {
        statusDiv.className = `user-status ${status == 1 ? 'status-blocked' : 'status-active'}`;
        statusDiv.innerHTML = status == 1 
            ? '<span class="material-icons-sharp" style="font-size: 14px; margin-right: 4px;">lock</span> Bloqué'
            : '<span class="material-icons-sharp" style="font-size: 14px; margin-right: 4px;">check_circle</span> Actif';
    }
    
    // Mettre à jour le bouton
    const actionBtn = userCard.querySelector('.user-actions button:last-child');
    if (actionBtn) {
        if (status == 1) {
            actionBtn.innerHTML = '<span class="material-icons-sharp ">lock_open</span> ';
            actionBtn.onclick = () => debloquerUser(userId);
            
        } else {
            actionBtn.innerHTML = '<span class="material-icons-sharp">lock</span> ';
            actionBtn.onclick = () => bloquerUser(userId);
        }
    }
}

// Fonction pour réinitialiser le formulaire
function resetForm() {
    document.getElementById('userId').value = '';
    document.getElementById('nomUtilisateur').value = '';
    document.getElementById('email').value = '';
    document.getElementById('typeCompte').value = '';
    document.getElementById('motDePasse').value = '';
    document.getElementById('nomComplet').value = '';
    document.getElementById('telephone').value = '';
    document.getElementById('adresse').value = '';

}

// Fonction de recherche
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.user-card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? 'flex' : 'none';
    });
}

//Fonction pour initialiser l'application
function initializeApp() {
    loadUsers();
    // Gestionnaire pour le bouton de réinitialisation
    const resetBtn = document.getElementById('resetForm');
    if (resetBtn) {
        resetBtn.addEventListener('click', resetForm);
    }
    // Gestionnaire pour le mode admin
    const adduserbtn = document.getElementById('addUserBtn');
    if (adduserbtn) {
        adduserbtn.addEventListener('click', function() {
            const adminPanel = document.getElementById('userModal');
            if (adminPanel) {
            resetForm();
            document.getElementById('modalTitle').textContent = " Ajouter un Utilisateur";
            adminPanel.style.display = 'flex'; 
            }
        });
    }
    // bouton annuler du modal
    const closeModal = document.getElementById('closeModal');
      if(closeModal){
        closeModal.addEventListener('click', function() {
            fermer();
        });
      }
    // Gestionnaire pour la recherche
    const searchBox = document.querySelector('.search-box');
    if (searchBox) {
        searchBox.addEventListener('input', handleSearch);
    }
    loadUsers();
    fermer();
}
function fermer() {
    // Fonction pour fermer le modal
    const adminPanel = document.getElementById('userModal');
    if (adminPanel) {
        adminPanel.style.display = 'none'; 
    }
}
// Attendre que le DOM soit chargé
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
    
}