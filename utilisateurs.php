<div class="main-header">
    <h1 class="main-title">Gestion des utilisateurs</h1>
    <header class="header-right">
        <button class="toggle-menu-btn" id="openSidebar">
            <span class="material-icons-sharp">menu</span>
        </button>
    </header>
</div>

<div class="inside">
    <div class="card">
        <div class="card-container">
            <div class="card-header">
                <span class="material-icons-sharp">person_add_alt</span>
            </div>
            <div class="card-body">
                <div class="card-info">
                    <h3>Liste des utilisateurs</h3>
                    <p>Ajout, modification et suppression des utilisateurs ici.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="user-management">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Exemple d'utilisateur -->
            <tr>
                <td>1</td>
                <td>Jean Dupont</td>
                <td>jean.dupont@example.com</td>
                <td>Administrateur</td>
                <td>
                    <button class="edit-btn">Modifier</button>
                    <button class="delete-btn">Supprimer</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
