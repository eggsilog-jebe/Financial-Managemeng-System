(() => {
  const key = "himsMainTheme";
  const sidebarKey = "fms_sidebar_collapsed";
  try {
    const preference = localStorage.getItem(key) || "light";
    const theme = preference === "system"
      ? (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light")
      : preference === "dark" ? "dark" : "light";
    document.documentElement.dataset.theme = theme;
    document.documentElement.setAttribute("data-bs-theme", theme);

    if (window.innerWidth > 991 && localStorage.getItem(sidebarKey) === "true") {
      document.documentElement.classList.add("sidebar-collapsed");
    }
  } catch {
    document.documentElement.dataset.theme = "light";
  }
})();

