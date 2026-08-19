document.addEventListener("DOMContentLoaded", function () {
  const body = document.body;
  const openSidebar = document.querySelector("#openSidebar");
  const closeSidebar = document.querySelector("#close");
  const toggleTheme = document.querySelector(".main-sidebar .toggle-theme");
  const sidebar = document.querySelector(".main-sidebar");

  if (toggleTheme && sidebar) {
    const light = toggleTheme.children[0];
    const dark = toggleTheme.children[1];

    if (localStorage.getItem("theme") === "dark") {
      body.classList.add("dark-mode");
      light.classList.remove("active");
      dark.classList.add("active");
    } else {
      light.classList.add("active");
      dark.classList.remove("active");
    }

    toggleTheme.addEventListener("click", function () {
      const darkMode = body.classList.toggle("dark-mode");
      light.classList.toggle("active", !darkMode);
      dark.classList.toggle("active", darkMode);
      localStorage.setItem("theme", darkMode ? "dark" : "light");
    });
  }

  if (openSidebar && sidebar) {
    openSidebar.addEventListener("click", function () {
      sidebar.style.left = "0%";
    });
  }

  if (closeSidebar && sidebar) {
    closeSidebar.addEventListener("click", function () {
      sidebar.style.left = "-100%";
    });
  }

  const links = document.querySelectorAll("#sidebarMenu .item a");
  const currentPage =
    new URLSearchParams(window.location.search).get("page") || "dashboard";

  links.forEach(function (link) {
    const href = link.getAttribute("href") || "";
    const match = href.match(/[?&]page=([^&]+)/);

    if (match && match[1] === currentPage) {
      link.classList.add("active");
    }

    link.addEventListener("click", function () {
      links.forEach(function (item) {
        item.classList.remove("active");
      });
      link.classList.add("active");
    });
  });
});

function showNotification(message, type = "success") {
  const notification = document.getElementById("notification");

  if (!notification) {
    console.warn(`Notification element not found: ${message}`);
    return;
  }

  notification.textContent = message;
  notification.className = `notification ${type}`;
  notification.offsetHeight;

  setTimeout(function () {
    notification.style.opacity = "0";
    setTimeout(function () {
      notification.classList.remove("success", "error", "warning");
      notification.style.opacity = "1";
    }, 300);
  }, 5000);
}
