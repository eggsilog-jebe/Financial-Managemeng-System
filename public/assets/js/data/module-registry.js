/*
 * Main HIMS module registry.
 * Fleet is an external frontend awaiting formal HIMS integration. A future
 * deployment may set window.HIMS_FLEET_URL to a validated HTTP(S) URL before
 * this file loads; no route is configured by default.
 */
(() => {
  const configuredFleetUrl = typeof window.HIMS_FLEET_URL === "string"
    ? window.HIMS_FLEET_URL.trim()
    : "";
  const fleetUrl = /^https?:\/\//i.test(configuredFleetUrl)
    ? configuredFleetUrl
    : "/module/fleet/fleet-dashboard";
  const modules = Object.freeze([
    Object.freeze({
      id: "fleet",
      name: "Fleet & Transportation Management",
      shortName: "Fleet & Transportation",
      description: "Existing frontend module awaiting formal HIMS integration.",
      icon: "ph-truck",
      status: "ready-for-integration",
      statusLabel: "Ready for Integration",
      version: "1.0.0",
      state: "Integration ready",
      enabled: true,
      external: false,
      integrationState: "configured",
      url: fleetUrl,
    }),
  ]);
  const getModule = (id) => modules.find((module) => module.id === id) || null;
  const announceUnavailable = (module) => {
    document.dispatchEvent(new CustomEvent("hims:module-feedback", {
      detail: {
        moduleId: module?.id || null,
        message: module?.id === "fleet"
          ? "Fleet integration has not been configured yet."
          : "This module is not available yet.",
      },
    }));
  };
  const navigate = (id) => {
    const module = getModule(id);
    if (!module || !module.enabled || !module.url) {
      announceUnavailable(module);
      return false;
    }
    window.location.assign(module.url);
    return true;
  };
  const wireLinks = (root = document) => {
    root.querySelectorAll("[data-module-link]").forEach((element) => {
      const module = getModule(element.dataset.moduleLink);
      if (!module || element.dataset.moduleBound === module.id) return;
      if (element.tagName === "A") {
        if (module.enabled && module.url) element.href = module.url;
        else element.removeAttribute("href");
      }
      if (module.enabled && module.url) {
        element.disabled = false;
        element.removeAttribute("aria-disabled");
        element.setAttribute("aria-label", `Open ${module.name}`);
      } else {
        element.setAttribute("aria-disabled", "true");
        element.setAttribute("aria-label", `${module.name}: ${module.statusLabel}`);
      }
      element.addEventListener("click", (event) => {
        if (!module.enabled || !module.url || element.tagName !== "A") {
          event.preventDefault();
          navigate(module.id);
        }
      });
      element.dataset.moduleBound = module.id;
    });
  };
  window.HimsModuleRegistry = Object.freeze({ modules, getModule, navigate, wireLinks });
})();
