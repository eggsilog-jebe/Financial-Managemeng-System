(() => {
  const key = "himsMainTheme";
  try {
    const preference = localStorage.getItem(key) || "light";
    const theme = preference === "system"
      ? (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light")
      : preference === "dark" ? "dark" : "light";
    document.documentElement.dataset.theme = theme;
    document.documentElement.setAttribute("data-bs-theme", theme);
  } catch {
    document.documentElement.dataset.theme = "light";
  }
})();
