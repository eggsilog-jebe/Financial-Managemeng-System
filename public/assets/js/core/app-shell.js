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

    const animateAccordion = (toggle, submenu, expand) => {
      if (!submenu) return;

      if (expand) {
        submenu.hidden = false;
        submenu.style.overflow = "hidden";
        toggle.setAttribute("aria-expanded", "true");
        toggle.closest(".nav-accordion")?.classList.add("is-expanded");

        const targetHeight = submenu.scrollHeight;

        if (typeof submenu.animate === "function") {
          const openAnim = submenu.animate([
            { maxHeight: "0px", opacity: 0, transform: "translateY(-4px)" },
            { maxHeight: `${targetHeight}px`, opacity: 1, transform: "translateY(0)" }
          ], {
            duration: 260,
            easing: "cubic-bezier(0.25, 1, 0.5, 1)",
            fill: "forwards"
          });

          // Stagger item reveal
          const items = submenu.querySelectorAll("li");
          items.forEach((item, index) => {
            item.animate([
              { opacity: 0, transform: "translateX(-8px)" },
              { opacity: 1, transform: "translateX(0)" }
            ], {
              duration: 220,
              delay: Math.min(index * 25, 120),
              easing: "cubic-bezier(0.25, 1, 0.5, 1)",
              fill: "both"
            });
          });

          openAnim.finished.then(() => {
            openAnim.cancel();
            submenu.style.removeProperty("max-height");
            submenu.style.removeProperty("overflow");
            submenu.style.removeProperty("opacity");
            submenu.style.removeProperty("transform");
          }).catch(() => {});
        }
      } else {
        toggle.setAttribute("aria-expanded", "false");
        toggle.closest(".nav-accordion")?.classList.remove("is-expanded");

        const currentHeight = submenu.scrollHeight;
        submenu.style.overflow = "hidden";

        if (typeof submenu.animate === "function") {
          const closeAnim = submenu.animate([
            { maxHeight: `${currentHeight}px`, opacity: 1, transform: "translateY(0)" },
            { maxHeight: "0px", opacity: 0, transform: "translateY(-4px)" }
          ], {
            duration: 200,
            easing: "cubic-bezier(0.4, 0, 1, 1)",
            fill: "forwards"
          });

          closeAnim.finished.then(() => {
            closeAnim.cancel();
            submenu.hidden = true;
            submenu.style.removeProperty("max-height");
            submenu.style.removeProperty("overflow");
            submenu.style.removeProperty("opacity");
            submenu.style.removeProperty("transform");
          }).catch(() => {
            submenu.hidden = true;
          });
        } else {
          submenu.hidden = true;
        }
      }
    };

    const setAccordion = (toggle, expanded) => {
      const submenu = document.getElementById(toggle.getAttribute("aria-controls"));
      animateAccordion(toggle, submenu, expanded);
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

    accordionToggles.forEach((toggle) => {
      toggle.addEventListener("click", (event) => {
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
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const sidebarNav = document.querySelector(".sidebar-nav");

    const syncSidebarToggle = () => {
      if (!sidebarToggle) return;
      const isDrawer = usesDrawer();
      const expanded = isDrawer
        ? (body.classList.contains("sidebar-open") || sidebar?.classList.contains("is-open"))
        : (!body.classList.contains("sidebar-collapsed") && !sidebar?.classList.contains("is-collapsed"));
      sidebarToggle.setAttribute("aria-expanded", String(expanded));
      sidebarToggle.setAttribute(
        "aria-label",
        isDrawer
          ? (expanded ? "Close navigation menu" : "Open navigation menu")
          : (expanded ? "Collapse sidebar" : "Expand sidebar"),
      );
    };

    let isSidebarAnimating = false;

    // JavaScript Web Animations API for 60fps buttery smooth desktop transition
    const animateDesktopSidebar = (willCollapse) => {
      if (isSidebarAnimating) return;
      isSidebarAnimating = true;
      body.classList.add("sidebar-animating");

      const startWidth = willCollapse ? 315 : 88;
      const endWidth = willCollapse ? 88 : 315;
      const animDuration = 300;
      const animEasing = "cubic-bezier(0.25, 1, 0.5, 1)";

      if (willCollapse) {
        body.classList.add("sidebar-collapsing");
        sidebar?.classList.add("is-collapsing");
        // Close open accordion submenus when collapsing into icon-only mode
        accordionToggles.forEach((toggle) => setAccordion(toggle, false));
      } else {
        body.classList.remove("sidebar-collapsed");
        sidebar?.classList.remove("is-collapsed");
        sidebar?.removeAttribute("data-collapsed");
        body.classList.add("sidebar-expanding");
        sidebar?.classList.add("is-expanding");
      }

      if (sidebar && mainContent && typeof sidebar.animate === "function") {
        const sidebarAnim = sidebar.animate([
          { width: `${startWidth}px` },
          { width: `${endWidth}px` }
        ], { duration: animDuration, easing: animEasing, fill: "forwards" });

        const mainAnim = mainContent.animate([
          { marginLeft: `${startWidth}px` },
          { marginLeft: `${endWidth}px` }
        ], { duration: animDuration, easing: animEasing, fill: "forwards" });

        Promise.all([sidebarAnim.finished, mainAnim.finished]).then(() => {
          if (willCollapse) {
            body.classList.add("sidebar-collapsed");
            body.classList.remove("sidebar-collapsing");
            sidebar?.classList.add("is-collapsed");
            sidebar?.classList.remove("is-collapsing");
            sidebar?.setAttribute("data-collapsed", "true");
          } else {
            body.classList.remove("sidebar-expanding");
            sidebar?.classList.remove("is-expanding");
          }
          sidebarAnim.cancel();
          mainAnim.cancel();
          body.classList.remove("sidebar-animating");
          isSidebarAnimating = false;
          try { localStorage.setItem("fms_sidebar_collapsed", String(willCollapse)); } catch (_) {}
          syncSidebarToggle();
          hideNavTooltip();
        }).catch(() => {
          if (willCollapse) {
            body.classList.add("sidebar-collapsed");
            body.classList.remove("sidebar-collapsing");
            sidebar?.classList.add("is-collapsed");
            sidebar?.classList.remove("is-collapsing");
            sidebar?.setAttribute("data-collapsed", "true");
          } else {
            body.classList.remove("sidebar-expanding");
            sidebar?.classList.remove("is-expanding");
          }
          body.classList.remove("sidebar-animating");
          isSidebarAnimating = false;
          syncSidebarToggle();
          hideNavTooltip();
        });
      } else {
        setTimeout(() => {
          if (willCollapse) {
            body.classList.add("sidebar-collapsed");
            body.classList.remove("sidebar-collapsing");
            sidebar?.classList.add("is-collapsed");
            sidebar?.classList.remove("is-collapsing");
            sidebar?.setAttribute("data-collapsed", "true");
          } else {
            body.classList.remove("sidebar-expanding");
            sidebar?.classList.remove("is-expanding");
          }
          body.classList.remove("sidebar-animating");
          isSidebarAnimating = false;
          try { localStorage.setItem("fms_sidebar_collapsed", String(willCollapse)); } catch (_) {}
          syncSidebarToggle();
          hideNavTooltip();
        }, animDuration);
      }
    };

    // Restore desktop sidebar collapsed preference on boot
    const isSavedCollapsed = localStorage.getItem("fms_sidebar_collapsed") === "true";
    if (!usesDrawer() && isSavedCollapsed) {
      body.classList.add("sidebar-collapsed");
      sidebar?.classList.add("is-collapsed");
      sidebar?.setAttribute("data-collapsed", "true");
      accordionToggles.forEach((toggle) => setAccordion(toggle, false));
      syncSidebarToggle();
    } else {
      syncSidebarToggle();
    }

    // JavaScript Web Animations API for mobile drawer slide-in / slide-out
    const openMobileDrawer = () => {
      if (isSidebarAnimating) return;
      isSidebarAnimating = true;
      body.classList.add("sidebar-open");
      sidebar?.classList.add("is-open");
      sidebar?.classList.remove("is-collapsed");
      syncSidebarToggle();

      if (sidebar && typeof sidebar.animate === "function") {
        const drawerAnim = sidebar.animate([
          { transform: "translateX(-100%)" },
          { transform: "translateX(0)" }
        ], { duration: 260, easing: "cubic-bezier(0.25, 1, 0.5, 1)", fill: "both" });

        drawerAnim.finished.then(() => {
          drawerAnim.cancel();
          isSidebarAnimating = false;
        }).catch(() => { isSidebarAnimating = false; });
      } else {
        setTimeout(() => { isSidebarAnimating = false; }, 260);
      }
    };

    const closeMobileNav = () => {
      if (!body.classList.contains("sidebar-open") || isSidebarAnimating) return;
      isSidebarAnimating = true;

      if (sidebar && typeof sidebar.animate === "function") {
        const closeAnim = sidebar.animate([
          { transform: "translateX(0)" },
          { transform: "translateX(-100%)" }
        ], { duration: 220, easing: "cubic-bezier(0.4, 0, 1, 1)", fill: "both" });

        closeAnim.finished.then(() => {
          closeAnim.cancel();
          body.classList.remove("sidebar-open");
          sidebar?.classList.remove("is-open");
          syncSidebarToggle();
          hideNavTooltip();
          isSidebarAnimating = false;
        }).catch(() => {
          body.classList.remove("sidebar-open");
          sidebar?.classList.remove("is-open");
          syncSidebarToggle();
          hideNavTooltip();
          isSidebarAnimating = false;
        });
      } else {
        body.classList.remove("sidebar-open");
        sidebar?.classList.remove("is-open");
        syncSidebarToggle();
        hideNavTooltip();
        setTimeout(() => { isSidebarAnimating = false; }, 220);
      }
    };

    sidebarToggle?.addEventListener("click", () => {
      if (usesDrawer()) {
        if (body.classList.contains("sidebar-open") || sidebar?.classList.contains("is-open")) {
          closeMobileNav();
        } else {
          openMobileDrawer();
        }
      } else {
        const willCollapse = !body.classList.contains("sidebar-collapsed") && !sidebar?.classList.contains("is-collapsed");
        animateDesktopSidebar(willCollapse);
      }
    });

    backdrop?.addEventListener("click", closeMobileNav);
    
    // Sidebar Scroll Position Persistence & Auto-Scroll Active Link into View
    if (sidebarNav) {
      const savedScroll = sessionStorage.getItem("fms_sidebar_scroll");
      if (savedScroll !== null) {
        sidebarNav.scrollTop = parseInt(savedScroll, 10);
      } else {
        const activeLink = sidebarNav.querySelector(".nav-link.active, .nav-submenu a.active, [aria-current='page']");
        if (activeLink) {
          activeLink.scrollIntoView({ block: "nearest", behavior: "instant" });
        }
      }

      const saveSidebarScroll = () => {
        sessionStorage.setItem("fms_sidebar_scroll", sidebarNav.scrollTop);
      };

      sidebarNav.addEventListener("scroll", saveSidebarScroll, { passive: true });
      sidebarNav.addEventListener("click", (event) => {
        if (event.target.closest("a[href]") || event.target.closest(".nav-accordion__toggle")) {
          saveSidebarScroll();
        }
        if (window.innerWidth <= 991 && event.target.closest('a[href]:not([aria-disabled="true"])')) closeMobileNav();
      });
      window.addEventListener("beforeunload", saveSidebarScroll);
    } else {
      document.querySelector(".sidebar-nav")?.addEventListener("click", (event) => {
        if (window.innerWidth <= 991 && event.target.closest('a[href]:not([aria-disabled="true"])')) closeMobileNav();
      });
    }

    // Responsive resize handler with debounced class synchronization
    let resizeTimer;
    window.addEventListener("resize", () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        if (!usesDrawer()) {
          body.classList.remove("sidebar-open");
        }
        syncSidebarToggle();
        hideNavTooltip();
      }, 100);
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

    const fmsNavigationIndex = [
      // 1. General Ledger
      { name: "General Ledger", category: "Core Module", url: "/general-ledger/chart-of-accounts", icon: "ph-book-open", desc: "Central accounting repository and chart of accounts." },
      { name: "Chart of Accounts", category: "General Ledger", url: "/general-ledger/chart-of-accounts", icon: "ph-list-bullets", desc: "Master index of assets, liabilities, equity, revenue, and expenses." },
      { name: "Journal Entries", category: "General Ledger", url: "/general-ledger/journal-entries", icon: "ph-notebook", desc: "Record and review day-to-day double-entry accounting transactions." },
      { name: "Ledger Books", category: "General Ledger", url: "/general-ledger/ledger-books", icon: "ph-book", desc: "Departmental financial logs and running balance statements." },
      { name: "Trial Balance", category: "General Ledger", url: "/general-ledger/trial-balance", icon: "ph-scales", desc: "Periodic verification that total debits equal total credits." },
      { name: "Period End Closing", category: "General Ledger", url: "/general-ledger/period-end-closing", icon: "ph-lock-key", desc: "Month-end and year-end closing, depreciation, and balance rollover." },

      // 2. Accounts Payable
      { name: "Accounts Payable (AP)", category: "Core Module", url: "/accounts-payable/vendor-management", icon: "ph-receipt", desc: "Vendor invoices, 3-way matching, and supplier payments." },
      { name: "Vendor Management", category: "Accounts Payable", url: "/accounts-payable/vendor-management", icon: "ph-buildings", desc: "Master directory of pharmaceutical and medical device suppliers." },
      { name: "Invoices & Vouchers", category: "Accounts Payable", url: "/accounts-payable/invoices-vouchers", icon: "ph-check-square-offset", desc: "3-Way matching (PO, GRN, Invoice) and AP voucher processing." },
      { name: "Purchase Bills", category: "Accounts Payable", url: "/accounts-payable/purchase-bills", icon: "ph-file-text", desc: "Log recurring hospital utility, oxygen, and bio-hazard bills." },
      { name: "Payable Aging", category: "Accounts Payable", url: "/accounts-payable/payable-aging", icon: "ph-clock-afternoon", desc: "Age analysis of unpaid vendor bills (0-30, 31-60, 61-90+ days)." },
      { name: "AP Payment Approvals", category: "Accounts Payable", url: "/accounts-payable/ap-payment-approvals", icon: "ph-shield-check", desc: "Multi-tier approval workflow for releasing vendor payments." },

      // 3. Accounts Receivable
      { name: "Accounts Receivable (AR)", category: "Core Module", url: "/accounts-receivable/patient-accounts", icon: "ph-currency-circle-dollar", desc: "Patient billings, HMO claims, and collection tracking." },
      { name: "Patient Accounts", category: "Accounts Receivable", url: "/accounts-receivable/patient-accounts", icon: "ph-user-list", desc: "Master accounts for admitted inpatients, outpatients, and HMOs." },
      { name: "Invoicing & Billing", category: "Accounts Receivable", url: "/accounts-receivable/invoicing-billing", icon: "ph-receipt", desc: "Itemized patient billing for room, doctor fees, and meds." },
      { name: "Receivable Aging", category: "Accounts Receivable", url: "/accounts-receivable/receivable-aging", icon: "ph-chart-bar", desc: "DSO tracking and age analysis of uncollected patient and HMO bills." },
      { name: "Credit Notes", category: "Accounts Receivable", url: "/accounts-receivable/credit-notes", icon: "ph-percent", desc: "Senior Citizen (20%), PWD discounts, and procedure credits." },
      { name: "Customer Statements", category: "Accounts Receivable", url: "/accounts-receivable/customer-statements", icon: "ph-files", desc: "Periodic statement summaries for PhilHealth and commercial HMOs." },

      // 4. Disbursement Management
      { name: "Disbursement Management", category: "Core Module", url: "/disbursement-management/payment-requests", icon: "ph-arrows-out", desc: "Fund request requisitions, check payments, and EFT transfers." },
      { name: "Payment Requests", category: "Disbursement", url: "/disbursement-management/payment-requests", icon: "ph-file-arrow-up", desc: "Departmental requisitions for operational hospital expenses." },
      { name: "Check Register", category: "Disbursement", url: "/disbursement-management/check-register", icon: "ph-pencil-line", desc: "Official log of physical checks written, signed, and issued." },
      { name: "EFT Transfers", category: "Disbursement", url: "/disbursement-management/eft-transfers", icon: "ph-bank", desc: "Automated electronic bank transfers for payroll and suppliers." },
      { name: "Disbursement Approvals", category: "Disbursement", url: "/disbursement-management/disbursement-approvals", icon: "ph-seal-check", desc: "Treasury and CFO authorization prior to releasing funds." },
      { name: "Petty Cash", category: "Disbursement", url: "/disbursement-management/petty-cash", icon: "ph-wallet", desc: "On-site cash drawer for urgent minor daily operational expenses." },

      // 5. Collection Management
      { name: "Collection Management", category: "Core Module", url: "/collection-management/payment-receipts", icon: "ph-vault", desc: "Point-of-Sale cashiers, official receipts, and bank deposits." },
      { name: "Payment Receipts", category: "Collection Management", url: "/collection-management/payment-receipts", icon: "ph-receipt", desc: "Official Receipts (OR) issued to patients upon payment." },
      { name: "Cashier Desk", category: "Collection Management", url: "/collection-management/cashier-desk", icon: "ph-device-tablet", desc: "POS station drawer management across ER, Inpatient, and Pharmacy." },
      { name: "Deposit Slips", category: "Collection Management", url: "/collection-management/deposit-slips", icon: "ph-path", desc: "Batch deposit slips for armored pickup and bank deposits." },
      { name: "Bank Deposits", category: "Collection Management", url: "/collection-management/bank-deposits", icon: "ph-bank", desc: "Verification logs matching cashier drawers with bank statements." },
      { name: "Payment Gateway Logs", category: "Collection Management", url: "/collection-management/payment-gateway-logs", icon: "ph-globe-hemisphere-west", desc: "Digital payment logs for online patient portal, GCash, and PayMaya." },

      // 6. Budget Management
      { name: "Budget Management", category: "Core Module", url: "/budget-management/fiscal-planning", icon: "ph-calculator", desc: "Fiscal planning, department caps, and variance analysis." },
      { name: "Fiscal Planning", category: "Budget Management", url: "/budget-management/fiscal-planning", icon: "ph-calendar-check", desc: "Annual hospital revenue targets and operational expense caps." },
      { name: "Budget Allocation", category: "Budget Management", url: "/budget-management/budget-allocation", icon: "ph-pie-chart", desc: "Distribution of approved funds to cost centers and units." },
      { name: "Departmental Budgets", category: "Budget Management", url: "/budget-management/departmental-budgets", icon: "ph-chart-donut", desc: "Live departmental spending monitors and burn rate tracking." },
      { name: "Variance Analysis", category: "Budget Management", url: "/budget-management/variance-analysis", icon: "ph-trend-up", desc: "Budget vs. Actual spending and revenue variance reports." },
      { name: "Budget Reallocations", category: "Budget Management", url: "/budget-management/budget-reallocations", icon: "ph-swap", desc: "Inter-departmental budget transfer requests and approvals." },

      // 7. Cash Management
      { name: "Cash Management", category: "Core Module", url: "/cash-management/bank-accounts", icon: "ph-coins", desc: "Hospital liquidity, bank reconciliation, and cash flow forecasting." },
      { name: "Bank Accounts", category: "Cash Management", url: "/cash-management/bank-accounts", icon: "ph-bank", desc: "Master register of hospital commercial bank accounts and balances." },
      { name: "Cash Flow Forecasting", category: "Cash Management", url: "/cash-management/cash-flow-forecasting", icon: "ph-chart-line-up", desc: "30-day liquidity prediction model based on inflows/outflows." },
      { name: "Bank Reconciliation", category: "Cash Management", url: "/cash-management/bank-reconciliation", icon: "ph-arrows-clockwise", desc: "Automated matching of bank statement feeds with GL accounts." },
      { name: "Fund Transfers", category: "Cash Management", url: "/cash-management/fund-transfers", icon: "ph-arrows-left-right", desc: "Inter-bank fund transfers between operational and payroll accounts." },
      { name: "Liquidity Management", category: "Cash Management", url: "/cash-management/liquidity-management", icon: "ph-safe", desc: "Short-term treasury investments and working capital reserves." },

      // 8. Financial Reporting & Analytics
      { name: "Financial Reporting & Analytics", category: "Core Module", url: "/financial-reporting/balance-sheet", icon: "ph-chart-line-up", desc: "Financial statements, P&L, balance sheets, and executive KPIs." },
      { name: "Balance Sheet", category: "Financial Reporting", url: "/financial-reporting/balance-sheet", icon: "ph-scales", desc: "Statement of Financial Position: Assets, Liabilities, and Equity." },
      { name: "Profit & Loss (P&L)", category: "Financial Reporting", url: "/financial-reporting/profit-loss", icon: "ph-trend-up", desc: "Hospital operating revenue, medical costs, and EBITDA margins." },
      { name: "Cash Flow Statement", category: "Financial Reporting", url: "/financial-reporting/cash-flow-statement", icon: "ph-currency-dollar", desc: "Operating, Investing, and Financing cash movement analysis." },
      { name: "Financial KPI Dashboard", category: "Financial Reporting", url: "/financial-reporting/financial-kpi-dashboard", icon: "ph-gauge", desc: "Healthcare indicators: DSO, Occupancy Revenue, ARPOB, Working Ratio." },
      { name: "Executive Reports", category: "Financial Reporting", url: "/financial-reporting/executive-reports", icon: "ph-file-pdf", desc: "Compiled quarterly executive packs for Board of Directors." },

      // 9. Tax Management
      { name: "Tax Management", category: "Core Module", url: "/tax-management/tax-configuration", icon: "ph-percent", desc: "Tax rules, withholding compliance, Form 2307, and statutory filings." },
      { name: "Tax Configuration", category: "Tax Management", url: "/tax-management/tax-configuration", icon: "ph-gear", desc: "Tax codes setup for VAT (12%), EWT, and hospital service exemptions." },
      { name: "Withholding Tax (EWT/VAT)", category: "Tax Management", url: "/tax-management/withholding-tax", icon: "ph-file-text", desc: "BIR Form 2307 / 2306 certificates for doctors and suppliers." },
      { name: "Tax Returns & Filings", category: "Tax Management", url: "/tax-management/tax-returns", icon: "ph-paperclip", desc: "Statutory tax returns (BIR Form 2550Q, 1601EQ, Corporate Tax)." },
      { name: "Tax Exemptions", category: "Tax Management", url: "/tax-management/tax-exemptions", icon: "ph-shield-check", desc: "VAT-exempt prescription medicines and Senior Citizen/PWD logs." },
      { name: "Tax Audit Trail", category: "Tax Management", url: "/tax-management/tax-audit-trail", icon: "ph-magnifying-glass-plus", desc: "Immutable audit trail logs for internal and external tax audits." }
    ];

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

      const matches = fmsNavigationIndex.filter((item) =>
        [item.name, item.category, item.desc].join(" ").toLowerCase().includes(query)
      );

      searchResults.innerHTML = "";
      matches.forEach((item) => {
        const result = document.createElement("button");
        result.type = "button";
        result.className = "search-result d-flex align-items-center justify-content-between p-2";
        result.setAttribute("role", "option");
        result.innerHTML = `
          <div class="d-flex align-items-center gap-2 min-w-0">
            <i class="ph ${item.icon} fs-5 text-primary flex-shrink-0" aria-hidden="true"></i>
            <div class="d-flex flex-column text-start min-w-0">
              <strong class="text-dark fs-xs text-truncate">${item.name}</strong>
              <small class="text-muted fs-xs text-truncate" style="font-size: 10.5px;">${item.desc}</small>
            </div>
          </div>
          <span class="badge bg-secondary-subtle text-secondary fs-xs flex-shrink-0 ms-2">${item.category}</span>
        `;
        result.addEventListener("click", () => {
          window.location.href = item.url;
          closeSearch();
        });
        searchResults.appendChild(result);
      });

      if (!matches.length) {
        searchResults.innerHTML = '<p class="search-empty text-muted p-3 mb-0 fs-xs">No module or sub-module matched your search.</p>';
      }
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
