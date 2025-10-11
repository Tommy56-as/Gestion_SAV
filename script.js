  document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const openSidebar = document.querySelector('#openSidebar');
    const closeSidebar = document.querySelector('#close');
    const toggleTheme = document.querySelector('.main-sidebar .toggle-theme');
    const sidebar = document.querySelector('.main-sidebar');
    const light = toggleTheme.children[0];
    const dark = toggleTheme.children[1];

    // Applique le thème sauvegardé au chargement
    if (localStorage.getItem('theme') === 'dark') {
      body.classList.add('dark-mode');
      light.classList.remove('active');
      dark.classList.add('active');
    } else {
      body.classList.remove('dark-mode');
      light.classList.add('active');
      dark.classList.remove('active');
    }

    openSidebar.addEventListener('click', () => {
      sidebar.style.left = '0%';
    });
    closeSidebar.addEventListener('click', () => {
      sidebar.style.left = '-100%';
    });

    toggleTheme.addEventListener('click', changeTheme);

    function changeTheme() {
      if (body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        light.classList.add('active');
        dark.classList.remove('active');
        localStorage.setItem('theme', 'light');
      } else {
        body.classList.add('dark-mode');
        light.classList.remove('active');
        dark.classList.add('active');
        localStorage.setItem('theme', 'dark');
      }
    }
  });
 document.addEventListener("DOMContentLoaded", function() {
    const links = document.querySelectorAll("#sidebarMenu .item a");
    let currentUrl = window.location.href;

    // Vérifie l'URL courante et applique active
    links.forEach(link => {
        if (currentUrl.includes(link.getAttribute("href"))) {
            link.classList.add("active");
        }
    });

    // Gestion au clic
    links.forEach(link => {
        link.addEventListener("click", function() {
            links.forEach(l => l.classList.remove("active")); // retire des autres
            this.classList.add("active"); // applique sur celui cliqué
        });
    });
});
        // Fonctionnalité d'affichage/masquage du mot de passe
        function setupPasswordToggle(toggleId, passwordId) {
            const toggle = document.getElementById(toggleId);
            const passwordInput = document.getElementById(passwordId);
            
            toggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Changer l'icône
                const icon = this.querySelector('i');
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        }
        
        // Configurer les boutons d'affichage/masquage
        setupPasswordToggle('login-toggle', 'login-password');
        
        // Validation du formulaire
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires!');
            }
        });


