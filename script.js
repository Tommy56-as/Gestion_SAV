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


