(function () {
  const fallbackIcons = {
    add: "+",
    add_business: "+",
    add_circle: "+",
    add_shopping_cart: "+",
    admin_panel_settings: "◎",
    arrow_forward: "→",
    arrow_upward: "↑",
    bar_chart: "▥",
    block: "×",
    build: "◆",
    build_circle: "◆",
    business: "■",
    business_center: "■",
    calendar_month: "□",
    calendar_today: "□",
    check: "✓",
    check_circle: "✓",
    close: "×",
    cloud_off: "−",
    currency_franc: "¤",
    delete: "×",
    domain: "■",
    edit: "✎",
    event: "□",
    event_available: "✓",
    group: "◎",
    groups: "◎",
    hub: "◇",
    info: "i",
    inventory_2: "▣",
    light_mode: "☼",
    local_offer: "◆",
    local_shipping: "□",
    lock: "■",
    lock_open: "□",
    logout: "↪",
    menu: "☰",
    monitor_heart: "♥",
    payments: "¤",
    person: "●",
    person_add: "+",
    person_add_alt: "+",
    person_add_disabled: "−",
    person_off: "−",
    palette: "●",
    print: "▣",
    query_stats: "▥",
    refresh: "↻",
    restart_alt: "↻",
    save: "▣",
    schedule: "◷",
    sell: "◆",
    settings: "⚙",
    shopping_cart: "□",
    shopping_cart_checkout: "□",
    storefront: "■",
    text_fields: "T",
    today: "□",
    trending_down: "↓",
    trending_up: "↑",
    tune: "☷",
    verified: "✓",
    verified_user: "✓",
    visibility: "◉",
    warning: "!",
    workspace_premium: "★",
  };

  function applyFallback() {
    const icons = document.querySelectorAll(".material-icons-sharp");
    icons.forEach((icon) => {
      const name = icon.textContent.trim();
      const fallback = fallbackIcons[name];
      if (!fallback) return;
      icon.dataset.iconName = name;
      icon.textContent = fallback;
      icon.classList.add("icon-fallback");
    });
  }

  function waitForIconFont() {
    if (document.fonts?.ready) {
      document.fonts.ready.then(() => {
        if (!document.fonts.check('24px "Material Icons Sharp"'))
          applyFallback();
      });
    } else {
      window.setTimeout(applyFallback, 800);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", waitForIconFont, {
      once: true,
    });
  } else {
    waitForIconFont();
  }
})();
