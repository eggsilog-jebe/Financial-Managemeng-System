(() => {
  const key = "himsMainTheme";
  const sidebarKey = "fms_sidebar_collapsed";
  try {
    const preference = localStorage.getItem("fms_theme") || localStorage.getItem(key) || "light";
    const theme = preference === "dark" ? "dark" : "light";
    document.documentElement.dataset.theme = theme;
    document.documentElement.setAttribute("data-bs-theme", theme);
    if (theme === "dark") {
      document.documentElement.classList.add("dark");
    } else {
      document.documentElement.classList.remove("dark");
    }

    if (window.innerWidth > 991 && localStorage.getItem(sidebarKey) === "true") {
      document.documentElement.classList.add("sidebar-collapsed");
    }
  } catch {
    document.documentElement.dataset.theme = "light";
    document.documentElement.classList.remove("dark");
  }
})();

