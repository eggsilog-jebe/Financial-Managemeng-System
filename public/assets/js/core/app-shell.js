(() => {
  const themeKey = "himsMainTheme";
  const applyTheme = (preference) => {
    const theme = preference === "system"
      ? (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light")
      : preference;
    document.documentElement.dataset.theme = theme;
    document.documentElement.setAttribute("data-bs-theme", theme);
    localStorage.setItem(themeKey, preference);
    document.querySelectorAll("[data-theme-option]").forEach((button) => {
      button.setAttribute("aria-checked", String(button.dataset.themeOption === preference));
    });
  };

  document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const profileToggle = document.getElementById("profile-toggle");
    const profileMenu = document.getElementById("profile-menu");
    const sidebarToggle = document.querySelector(".menu-toggle");
    const backdrop = document.querySelector("[data-sidebar-backdrop]");
    const searchInput = document.getElementById("global-search");
    const searchResults = document.getElementById("search-results");
    const toastContainer = document.getElementById("toast-container");
    const accordionToggles = [...document.querySelectorAll(".nav-accordion__toggle")];
    const tooltipTargets = [...document.querySelectorAll("[data-nav-tooltip]")];
    let activeModal = null;
    let modalTrigger = null;
    let navTooltip = null;

    const focusableSelector = [
      "a[href]",
      "button:not([disabled])",
      "input:not([disabled])",
      "select:not([disabled])",
      "textarea:not([disabled])",
      "[tabindex]:not([tabindex='-1'])",
    ].join(",");
    const closeModal = (modal, restoreFocus = true) => {
      if (!modal) return;
      modal.hidden = true;
      document.body.style.removeProperty("overflow");
      activeModal = null;
      if (restoreFocus) modalTrigger?.focus();
      modalTrigger = null;
    };
    const openModal = (id, trigger = null) => {
      const modal = document.getElementById(id);
      if (!modal?.classList.contains("hims-modal")) return false;
      if (activeModal) closeModal(activeModal, false);
      activeModal = modal;
      modalTrigger = trigger;
      modal.hidden = false;
      document.body.style.overflow = "hidden";
      window.requestAnimationFrame(() => modal.querySelector(focusableSelector)?.focus());
      return true;
    };
    const notify = ({ tone = "info", title = null, message = "", duration = 4200 } = {}) => {
      if (!toastContainer || !message) return null;
      if (tone === "error") tone = "danger";
      const allowedTones = new Set(["success", "warning", "danger", "info"]);
      const resolvedTone = allowedTones.has(tone) ? tone : "info";
      const toneIcons = { success: "ph-check-circle", warning: "ph-warning", danger: "ph-warning-circle", info: "ph-info" };
      const toast = document.createElement("div");
      toast.className = `hims-notification hims-notification--${resolvedTone} hims-notification--toast`;
      toast.setAttribute("role", resolvedTone === "danger" ? "alert" : "status");
      const icon = document.createElement("span");
      icon.className = "hims-notification__icon";
      icon.setAttribute("aria-hidden", "true");
      icon.innerHTML = `<i class="ph ${toneIcons[resolvedTone]}"></i>`;
      const content = document.createElement("div");
      content.className = "hims-notification__content";
      if (title) {
        const heading = document.createElement("strong");
        heading.textContent = title;
        content.appendChild(heading);
      }
      const copy = document.createElement("div");
      copy.textContent = message;
      content.appendChild(copy);
      const dismiss = document.createElement("button");
      dismiss.className = "hims-notification__dismiss";
      dismiss.type = "button";
      dismiss.dataset.notificationDismiss = "";
      dismiss.setAttribute("aria-label", "Dismiss notification");
      dismiss.innerHTML = '<i class="ph ph-x" aria-hidden="true"></i>';
      toast.append(icon, content, dismiss);
      toastContainer.replaceChildren(toast);
      if (duration > 0) window.setTimeout(() => toast.remove(), duration);
      return toast;
    };

    window.HimsComponents = Object.freeze({ openModal, closeModal, notify });

    const setAccordion = (toggle, expanded) => {
      const submenu = document.getElementById(toggle.getAttribute("aria-controls"));
      toggle.setAttribute("aria-expanded", String(expanded));
      toggle.closest(".nav-accordion")?.classList.toggle("is-expanded", expanded);
      if (submenu) submenu.hidden = !expanded;
    };
    const closeOtherAccordions = (current) => {
      accordionToggles.forEach((toggle) => {
        if (toggle !== current) setAccordion(toggle, false);
      });
    };
    const hideNavTooltip = () => {
      navTooltip?.remove();
      navTooltip = null;
    };
    const showNavTooltip = (target) => {
      if (!body.classList.contains("sidebar-collapsed") || window.innerWidth <= 991) return;
      hideNavTooltip();
      const rect = target.getBoundingClientRect();
      navTooltip = document.createElement("div");
      navTooltip.className = "nav-tooltip";
      navTooltip.setAttribute("role", "tooltip");
      navTooltip.textContent = target.dataset.navTooltip;
      document.body.appendChild(navTooltip);
      const tooltipRect = navTooltip.getBoundingClientRect();
      navTooltip.style.left = `${Math.round(rect.right + 10)}px`;
      navTooltip.style.top = `${Math.round(rect.top + (rect.height - tooltipRect.height) / 2)}px`;
    };

    const initiallyExpanded = accordionToggles.filter((toggle) => toggle.getAttribute("aria-expanded") === "true");
    initiallyExpanded.slice(1).forEach((toggle) => setAccordion(toggle, false));
    accordionToggles.forEach((toggle) => {
      toggle.addEventListener("click", (event) => {
        const isChevronClick = event.target.classList.contains("nav-chevron") || Boolean(event.target.closest(".nav-chevron"));
        const targetUrl = toggle.dataset.href;
        const isCurrentModulePage = targetUrl && window.location.href.split('#')[0].split('?')[0] === targetUrl.split('#')[0].split('?')[0];

        if (!isChevronClick && targetUrl && !isCurrentModulePage) {
          window.location.href = targetUrl;
          return;
        }

        const wasExpanded = toggle.getAttribute("aria-expanded") === "true";
        if (body.classList.contains("sidebar-collapsed") && window.innerWidth > 991) {
          body.classList.remove("sidebar-collapsed");
          sidebarToggle?.setAttribute("aria-expanded", "true");
          sidebarToggle?.setAttribute("aria-label", "Collapse sidebar");
          hideNavTooltip();
        }
        closeOtherAccordions(toggle);
        setAccordion(toggle, !wasExpanded);
      });
    });
    tooltipTargets.forEach((target) => {
      target.addEventListener("mouseenter", () => showNavTooltip(target));
      target.addEventListener("mouseleave", hideNavTooltip);
      target.addEventListener("focus", () => showNavTooltip(target));
      target.addEventListener("blur", hideNavTooltip);
    });

    document.addEventListener("hims:module-feedback", (event) => {
      notify({ tone: "info", message: event.detail?.message });
    });
    document.addEventListener("hims:notify", (event) => notify(event.detail));
    document.addEventListener("click", (event) => {
      const openButton = event.target.closest("[data-modal-open]");
      if (openButton) {
        event.preventDefault();
        openModal(openButton.dataset.modalOpen, openButton);
        return;
      }
      const closeButton = event.target.closest("[data-modal-close]");
      if (closeButton) {
        closeModal(closeButton.closest(".hims-modal"));
        return;
      }
      const dismissButton = event.target.closest("[data-notification-dismiss]");
      if (dismissButton) {
        dismissButton.closest(".hims-notification")?.remove();
        return;
      }
      const clearButton = event.target.closest("[data-search-clear]");
      if (clearButton) {
        const input = clearButton.closest(".hims-search")?.querySelector('input[type="search"]');
        if (!input) return;
        input.value = "";
        input.dispatchEvent(new Event("input", { bubbles: true }));
        input.focus();
        return;
      }
      if (activeModal && event.target === activeModal) closeModal(activeModal);
    });
    document.addEventListener("input", (event) => {
      if (!event.target.matches('.hims-search input[type="search"]')) return;
      const clearButton = event.target.closest(".hims-search")?.querySelector("[data-search-clear]");
      if (clearButton) clearButton.hidden = !event.target.value;
    });
    document.addEventListener("change", (event) => {
      if (!event.target.matches("[data-table-select-all]")) return;
      const table = event.target.closest("table");
      table?.querySelectorAll("tbody [data-row-select]").forEach((checkbox) => {
        checkbox.checked = event.target.checked;
        checkbox.closest("tr")?.classList.toggle("is-selected", checkbox.checked);
      });
    });
    document.addEventListener("change", (event) => {
      if (!event.target.matches("[data-row-select]")) return;
      event.target.closest("tr")?.classList.toggle("is-selected", event.target.checked);
      const table = event.target.closest("table");
      const selection = [...(table?.querySelectorAll("tbody [data-row-select]") || [])];
      const selectAll = table?.querySelector("[data-table-select-all]");
      if (selectAll) {
        selectAll.checked = selection.length > 0 && selection.every((checkbox) => checkbox.checked);
        selectAll.indeterminate = selection.some((checkbox) => checkbox.checked) && !selectAll.checked;
      }
    });

    const usesDrawer = () => window.innerWidth <= 991;
    const syncSidebarToggle = () => {
      if (!sidebarToggle) return;
      const expanded = usesDrawer()
        ? body.classList.contains("sidebar-open")
        : !body.classList.contains("sidebar-collapsed");
      sidebarToggle.setAttribute("aria-expanded", String(expanded));
      sidebarToggle.setAttribute(
        "aria-label",
        usesDrawer()
          ? (expanded ? "Close navigation menu" : "Open navigation menu")
          : (expanded ? "Collapse sidebar" : "Expand sidebar"),
      );
    };
    const closeMobileNav = () => {
      body.classList.remove("sidebar-open");
      syncSidebarToggle();
      hideNavTooltip();
    };

    sidebarToggle?.addEventListener("click", () => {
      if (usesDrawer()) {
        body.classList.toggle("sidebar-open");
      } else {
        body.classList.toggle("sidebar-collapsed");
      }
      syncSidebarToggle();
      hideNavTooltip();
    });
    backdrop?.addEventListener("click", closeMobileNav);
    document.querySelector(".sidebar-nav")?.addEventListener("click", (event) => {
      if (window.innerWidth <= 991 && event.target.closest('a[href]:not([aria-disabled="true"])')) closeMobileNav();
    });
    window.addEventListener("resize", () => {
      if (!usesDrawer()) body.classList.remove("sidebar-open");
      syncSidebarToggle();
      hideNavTooltip();
    });
    syncSidebarToggle();

    const getProfileMenuItems = () => [...(profileMenu?.querySelectorAll('[role="menuitem"], [role="menuitemradio"]') || [])];
    const closeProfileMenu = ({ restoreFocus = false } = {}) => {
      if (!profileMenu || !profileToggle) return;
      profileMenu.hidden = true;
      profileToggle.setAttribute("aria-expanded", "false");
      if (restoreFocus) profileToggle.focus();
    };
    const openProfileMenu = ({ focus = true } = {}) => {
      if (!profileMenu || !profileToggle) return;
      profileMenu.hidden = false;
      profileToggle.setAttribute("aria-expanded", "true");
      if (focus) window.requestAnimationFrame(() => getProfileMenuItems()[0]?.focus());
    };
    profileToggle?.addEventListener("click", () => {
      if (profileMenu.hidden) openProfileMenu();
      else closeProfileMenu();
    });
    profileToggle?.addEventListener("keydown", (event) => {
      if (event.key !== "ArrowDown") return;
      event.preventDefault();
      openProfileMenu();
    });
    profileMenu?.addEventListener("click", (event) => {
      if (event.target.closest('[role="menuitem"], [role="menuitemradio"]')) closeProfileMenu();
    });
    profileMenu?.addEventListener("keydown", (event) => {
      const items = getProfileMenuItems();
      const currentIndex = items.indexOf(document.activeElement);
      if (event.key === "Escape") {
        event.preventDefault();
        closeProfileMenu({ restoreFocus: true });
        return;
      }
      if (event.key === "Tab") {
        closeProfileMenu();
        return;
      }
      if (!["ArrowDown", "ArrowUp", "Home", "End"].includes(event.key) || !items.length) return;
      event.preventDefault();
      let nextIndex;
      if (event.key === "Home") nextIndex = 0;
      else if (event.key === "End") nextIndex = items.length - 1;
      else if (event.key === "ArrowDown") nextIndex = (currentIndex + 1 + items.length) % items.length;
      else nextIndex = (currentIndex - 1 + items.length) % items.length;
      items[nextIndex].focus();
    });
    document.addEventListener("click", (event) => {
      if (profileMenu && profileToggle && !event.target.closest(".sidebar-profile-wrap")) {
        closeProfileMenu();
      }
    });
    document.addEventListener("keydown", (event) => {
      const typingTarget = event.target.closest?.("input, textarea, select, [contenteditable='true']");
      if (event.key === "/" && !typingTarget && !event.ctrlKey && !event.metaKey && !event.altKey) {
        event.preventDefault();
        searchInput?.focus();
        return;
      }
      if (event.key === "Escape") {
        if (activeModal) {
          closeModal(activeModal);
          return;
        }
        closeMobileNav();
        if (profileMenu && !profileMenu.hidden) closeProfileMenu({ restoreFocus: true });
      }
      if (event.key === "Tab" && activeModal) {
        const focusable = [...activeModal.querySelectorAll(focusableSelector)];
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    });

    const preference = localStorage.getItem(themeKey) || "light";
    applyTheme(preference);
    const user = window.HimsSession?.getUser();
    if (user) {
      const initials = user.name.split(/\s+/).filter(Boolean).map((word) => word[0]).join("").slice(0, 2).toUpperCase();
      document.querySelector(".profile-avatar").textContent = initials || "HU";
      document.querySelector(".profile-name").textContent = user.name;
      document.querySelector(".profile-role").textContent = user.role;
      profileToggle?.setAttribute("aria-label", `Open account menu for ${user.name}`);
    }
    document.querySelectorAll("[data-theme-option]").forEach((button) => {
      button.addEventListener("click", () => applyTheme(button.dataset.themeOption));
    });
    document.querySelectorAll('a[aria-disabled="true"]').forEach((link) => {
      link.addEventListener("click", (event) => {
        if (link.getAttribute("aria-disabled") === "true") event.preventDefault();
      });
    });
    window.HimsModuleRegistry?.wireLinks(document);

    const closeSearch = () => {
      if (!searchResults || !searchInput) return;
      searchResults.hidden = true;
      searchResults.innerHTML = "";
      searchInput.setAttribute("aria-expanded", "false");
    };
    const renderSearch = () => {
      if (!searchResults || !searchInput) return;
      const query = searchInput.value.trim().toLowerCase();
      if (!query) return closeSearch();
      const registryModules = (window.HimsModuleRegistry?.modules || []).map((module) => ({ ...module, registryId: module.id }));
      const discoveryModules = window.HimsModuleDiscovery?.modules || [];
      const searchableModules = [...registryModules, ...discoveryModules].filter((module, index, modules) =>
        modules.findIndex((candidate) => candidate.id === module.id) === index,
      );
      const matches = searchableModules.filter((module) =>
        [module.name, module.shortName, module.description, module.statusLabel, module.state].join(" ").toLowerCase().includes(query),
      );
      searchResults.innerHTML = "";
      matches.forEach((module) => {
        const result = document.createElement("button");
        result.type = "button";
        result.className = "search-result";
        result.setAttribute("role", "option");
        if (!module.enabled) {
          result.classList.add("is-unavailable");
          result.setAttribute("aria-disabled", "true");
        }
        result.innerHTML = `<i class="ph-fill ${module.icon}" aria-hidden="true"></i><span><strong></strong><small></small></span>`;
        result.querySelector("strong").textContent = module.name;
        result.querySelector("small").textContent = [module.statusLabel, module.state].filter(Boolean).join(" · ");
        result.addEventListener("click", () => {
          if (module.enabled && module.registryId) {
            window.HimsModuleRegistry?.navigate(module.registryId);
            return;
          }
          document.dispatchEvent(new CustomEvent("hims:module-locate", { detail: { moduleId: module.id } }));
          if (module.registryId) {
            window.HimsModuleRegistry?.navigate(module.registryId);
          } else {
            document.dispatchEvent(new CustomEvent("hims:module-feedback", {
              detail: { moduleId: module.id, message: `${module.name}: ${module.statusLabel || module.state}. No approved route is available.` },
            }));
          }
          closeSearch();
        });
        searchResults.appendChild(result);
      });
      if (!matches.length) searchResults.innerHTML = '<p class="search-empty">No frontend module matched your search.</p>';
      searchResults.hidden = false;
      searchInput.setAttribute("aria-expanded", "true");
    };
    searchInput?.addEventListener("input", renderSearch);
    searchInput?.addEventListener("keydown", (event) => {
      if (event.key === "Escape") { searchInput.value = ""; closeSearch(); searchInput.blur(); }
    });
    document.addEventListener("click", (event) => {
      if (!event.target.closest(".search-wrap")) closeSearch();
    });
    document.querySelector("[data-logout]")?.addEventListener("click", () => {
      window.HimsSession?.clear();
      window.location.replace("/login");
    });
  });
})();
