document.addEventListener("DOMContentLoaded", function () {
  const body = document.body;
  const openSidebar = document.querySelector("#openSidebar");
  const closeSidebar = document.querySelector("#close");
  const toggleTheme = document.querySelector(".main-sidebar .toggle-theme");
  const sidebar = document.querySelector(".main-sidebar");
  applySavedPreferences();

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

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta) {
  const originalFetch = window.fetch;
  window.fetch = function (input, init = {}) {
    const requestUrl = typeof input === "string" ? input : input.url;
    const headers = new Headers(init.headers || {});
    if (
      new URL(requestUrl, window.location.href).origin ===
      window.location.origin
    ) {
      headers.set("X-CSRF-Token", csrfMeta.content);
    }
    return originalFetch(input, { ...init, headers });
  };
}

function applySavedPreferences() {
  const preferences = JSON.parse(
    localStorage.getItem("appPreferences") || "{}"
  );
  const root = document.documentElement;

  if (preferences.primary)
    root.style.setProperty("--fuscha", preferences.primary);
  if (preferences.secondary)
    root.style.setProperty("--cyan", preferences.secondary);
  if (preferences.fontSize)
    root.style.setProperty("--base-font-size", `${preferences.fontSize}px`);
  if (preferences.font) root.style.setProperty("--app-font", preferences.font);
}

function savePreferences(preferences) {
  localStorage.setItem("appPreferences", JSON.stringify(preferences));
  applySavedPreferences();
}

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("#settingsForm");
  if (!form) return;

  const saved = JSON.parse(localStorage.getItem("appPreferences") || "{}");
  form.primary.value = saved.primary || "#ed2f87";
  form.secondary.value = saved.secondary || "#63c5ce";
  form.fontSize.value = saved.fontSize || "14";
  form.font.value = saved.font || "'Nunito', sans-serif";

  form.addEventListener("input", function () {
    savePreferences({
      primary: form.primary.value,
      secondary: form.secondary.value,
      fontSize: form.fontSize.value,
      font: form.font.value,
    });
  });

  document
    .querySelector("#resetSettings")
    ?.addEventListener("click", function () {
      localStorage.removeItem("appPreferences");
      window.location.reload();
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
